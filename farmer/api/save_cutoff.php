<?php
/**
 * MarketLink - Farmer API: Update Order Cut-Off Time
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['farmer', 'admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDBConnection();
$userId = (int)$_SESSION['user_id'];
$cutoffTime = trim($_POST['order_cutoff_time'] ?? '18:00:00');

// Validate time format HH:MM or HH:MM:SS
if (!preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/', $cutoffTime)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid time format. Please use HH:MM.']);
    exit;
}

// Append seconds if omitted
if (strlen($cutoffTime) === 5) {
    $cutoffTime .= ':00';
}

try {
    $stmt = $pdo->prepare("UPDATE farmer_profiles SET order_cutoff_time = :cutoff WHERE farmer_id = :fid");
    $stmt->execute([
        ':cutoff' => $cutoffTime,
        ':fid' => $userId
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Daily order cutoff time updated to ' . date('g:i A', strtotime($cutoffTime)) . '!'
    ]);
} catch (Exception $e) {
    error_log("Save Cutoff Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating cut-off time.']);
}
