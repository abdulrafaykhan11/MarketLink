<?php
/**
 * MarketLink - Contact Form Submission API
 * Handles database persistence, user email confirmation & admin alerts
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Receive payload from JSON or standard FormData
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$name    = trim($data['name'] ?? '');
$email   = trim($data['email'] ?? '');
$phone   = trim($data['phone'] ?? '');
$subjectRaw = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

// Subject label translation
$subjectLabels = [
    'farmer_registration' => 'Farmer Registration Help',
    'order_issue'         => 'Order / Pickup Issue',
    'general'             => 'General Question',
    'partnership'         => 'Partnership / Media Inquiry',
    'feedback'            => 'Feedback & Suggestions'
];
$subject = $subjectLabels[$subjectRaw] ?? ($subjectRaw ?: 'General Inquiry');

// Validation
$errors = [];
if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
    $errors[] = 'Name must be between 3 and 100 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}
if (empty($subjectRaw)) {
    $errors[] = 'Please select an inquiry subject.';
}
if (mb_strlen($message) < 15) {
    $errors[] = 'Message is too short. Please provide at least 15 characters of detail.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getDBConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, ip_address, created_at) 
                           VALUES (:name, :email, :phone, :subject, :message, 'unread', :ip, NOW())");
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':phone'   => $phone,
        ':subject' => $subject,
        ':message' => $message,
        ':ip'      => $ip
    ]);

    $inquiryId = (int)$pdo->lastInsertId();

    // 1. Send confirmation email to the user
    $userEmailSent = sendContactConfirmationEmail($name, $email, $subject, $message, $inquiryId);

    // 2. Send instant alert notification email to admin
    $adminEmailSent = sendAdminContactAlertEmail($name, $email, $phone, $subject, $message, $inquiryId);

    echo json_encode([
        'success'         => true,
        'message'         => 'Thank you! Your inquiry has been sent to our administration. A confirmation email has been dispatched to ' . htmlspecialchars($email) . '.',
        'inquiry_id'      => $inquiryId,
        'ticket_number'   => 'INQ-' . str_pad((string)$inquiryId, 5, '0', STR_PAD_LEFT),
        'user_email_sent' => $userEmailSent,
        'admin_alert_sent'=> $adminEmailSent
    ]);

} catch (Exception $e) {
    error_log("Contact Submit DB/Mail Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred while submitting your message. Please try again.']);
}
