<?php
/**
 * MarketLink - Public Contact Inquiry / Ticket Status Checker API
 * Powered by MySQL View: view_admin_contact_inquiries
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_REQUEST;
}

$ticketInput = trim($data['ticket'] ?? '');
$emailInput  = trim($data['email'] ?? '');

if (empty($ticketInput)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Please enter your Inquiry Ticket Number (e.g. INQ-00012).'
    ]);
    exit;
}

// Normalize ticket number: Extract ID if given as INQ-00012, #INQ-00012, or just 12
$cleanTicket = strtoupper(trim(ltrim($ticketInput, '#')));
$ticketId = 0;
if (preg_match('/^INQ-(\d+)$/', $cleanTicket, $matches)) {
    $ticketId = (int)$matches[1];
} elseif (ctype_digit($cleanTicket)) {
    $ticketId = (int)$cleanTicket;
}

try {
    $pdo = getDBConnection();

    // Query from MySQL View
    if ($ticketId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM view_admin_contact_inquiries WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $ticketId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM view_admin_contact_inquiries WHERE ticket_number = :t LIMIT 1");
        $stmt->execute([':t' => $cleanTicket]);
    }

    $inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inquiry) {
        echo json_encode([
            'success' => false,
            'error'   => "No inquiry found matching \"{$ticketInput}\". Please ensure your ticket number matches the code sent in your confirmation email."
        ]);
        exit;
    }

    // Optional email verification if email was entered
    if (!empty($emailInput)) {
        if (strcasecmp($emailInput, $inquiry['email']) !== 0) {
            echo json_encode([
                'success' => false,
                'error'   => "The provided email address does not match this inquiry ticket. Please check and try again."
            ]);
            exit;
        }
    }

    // Status step & badge computation
    $status = $inquiry['status']; // 'unread', 'read', 'replied'
    $step = 1;
    $statusTitle = 'Received & In Queue';
    $statusDesc = 'Your message has been received by our support team and is waiting for review.';

    if ($status === 'read') {
        $step = 2;
        $statusTitle = 'Seen & Under Review';
        $statusDesc = 'An administrator has reviewed your inquiry and is preparing a response.';
    } elseif ($status === 'replied') {
        $step = 3;
        $statusTitle = 'Replied & Resolved';
        $statusDesc = 'Our administration has provided an official reply. A copy was also emailed to your address.';
    }

    // Mask email for display privacy (e.g. j***n@example.com)
    $emailParts = explode('@', $inquiry['email']);
    $maskedEmail = $inquiry['email'];
    if (count($emailParts) === 2 && strlen($emailParts[0]) > 2) {
        $maskedEmail = substr($emailParts[0], 0, 2) . '***@' . $emailParts[1];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id'                  => (int)$inquiry['id'],
            'ticket_number'       => $inquiry['ticket_number'],
            'name'                => $inquiry['name'],
            'email'               => $maskedEmail,
            'subject'             => $inquiry['subject'],
            'message'             => $inquiry['message'],
            'status'              => $status,
            'status_step'         => $step,
            'status_title'        => $statusTitle,
            'status_desc'         => $statusDesc,
            'created_at_human'    => date('d M Y, h:i A', strtotime($inquiry['created_at'])) . ' (PKT)',
            'admin_reply'         => $inquiry['admin_reply'] ?? null,
            'replied_at_human'    => !empty($inquiry['replied_at']) ? (date('d M Y, h:i A', strtotime($inquiry['replied_at'])) . ' (PKT)') : null,
            'replied_by'          => !empty($inquiry['replied_by_username']) ? (ucfirst($inquiry['replied_by_username']) . ' (MarketLink Admin)') : 'MarketLink Support Team'
        ]
    ]);

} catch (Exception $e) {
    error_log("Contact Status API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Could not retrieve ticket status at this moment. Please try again shortly.'
    ]);
}

