<?php
/**
 * MarketLink Admin API - User Status Management (Active / Suspended)
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

$userId = (int)($_POST['user_id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($userId <= 0 || !in_array($action, ['activate', 'suspend', 'deactivate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Prevent admin from suspending themselves
if ($userId === (int)$_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot change your own admin account status']);
    exit;
}

$pdo = getDBConnection();

try {
    $newStatus = match($action) {
        'activate'   => 'active',
        'suspend'    => 'suspended',
        'deactivate' => 'inactive',
    };

    $stmt = $pdo->prepare("UPDATE users SET status = :status, updated_at = NOW() WHERE user_id = :uid");
    $stmt->execute([':status' => $newStatus, ':uid' => $userId]);

    echo json_encode([
        'success' => true,
        'message' => "User account set to " . ucfirst($newStatus) . " successfully.",
        'new_status' => $newStatus
    ]);
} catch (Exception $e) {
    error_log("User status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
