<?php
/**
 * MarketLink Admin API - Review Moderation
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

$pdo = getDBConnection();
$reviewType = trim($_POST['review_type'] ?? 'farmer'); // 'farmer' or 'product'
$action     = trim($_POST['action'] ?? '');
$reviewId   = (int)($_POST['review_id'] ?? 0);

if ($reviewId <= 0 || !in_array($action, ['toggle_visibility', 'delete'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$table = ($reviewType === 'product') ? 'product_reviews' : 'farmer_reviews';

try {
    if ($action === 'toggle_visibility') {
        $status = (int)($_POST['is_moderated'] ?? 1);
        $stmt = $pdo->prepare("UPDATE {$table} SET is_moderated = :st WHERE review_id = :id");
        $stmt->execute([':st' => $status, ':id' => $reviewId]);
        echo json_encode([
            'success' => true, 
            'message' => 'Review visibility updated (' . ($status ? 'Visible' : 'Hidden') . ').',
            'new_status' => $status
        ]);
        exit;
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE review_id = :id");
        $stmt->execute([':id' => $reviewId]);
        echo json_encode(['success' => true, 'message' => 'Review deleted successfully.']);
        exit;
    }
} catch (Exception $e) {
    error_log("Review moderation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
