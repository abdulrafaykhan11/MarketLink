<?php
/**
 * MarketLink Admin API - Farmer Approval / Rejection / Suspension
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$farmerId = (int)($_POST['farmer_id'] ?? 0);
$action   = trim($_POST['action'] ?? '');
$reason   = trim($_POST['reason'] ?? '');

if ($farmerId <= 0 || !in_array($action, ['approve', 'reject', 'suspend', 'activate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$pdo = getDBConnection();

try {
    $pdo->beginTransaction();

    $newStatus = match($action) {
        'approve' => 'approved',
        'reject'  => 'rejected',
        'suspend' => 'suspended',
        'activate'=> 'approved',
    };

    $stmt = $pdo->prepare("UPDATE farmer_profiles SET approval_status = :status, updated_at = NOW() WHERE farmer_id = :fid");
    $stmt->execute([':status' => $newStatus, ':fid' => $farmerId]);

    // Send corresponding notification to farmer user
    $notifTitle = match($newStatus) {
        'approved' => '🎉 Farmer Stall Approved!',
        'rejected' => 'Farmer Profile Update',
        'suspended'=> 'Account Notice: Stall Suspended'
    };

    $notifMsg = match($newStatus) {
        'approved' => 'Congratulations! Your farm profile has been officially verified and approved. You can now post weekly inventories, assign stalls, and start receiving pre-orders.',
        'rejected' => 'Your farmer stall registration could not be approved at this time.' . (!empty($reason) ? ' Reason: ' . $reason : ' Please review your documentation and contact support.'),
        'suspended'=> 'Your farmer stall has been temporarily suspended by platform administration.' . (!empty($reason) ? ' Reason: ' . $reason : '')
    };

    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                               VALUES (:uid, :title, :msg, 'system', 0, NOW())");
    $notifStmt->execute([
        ':uid'   => $farmerId,
        ':title' => $notifTitle,
        ':msg'   => $notifMsg
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Farmer status updated to " . ucfirst($newStatus) . " successfully.",
        'new_status' => $newStatus
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Farmer action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
