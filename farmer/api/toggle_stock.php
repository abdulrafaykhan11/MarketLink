<?php
/**
 * MarketLink - Farmer API: Toggle Produce Stock Availability
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
$productId = (int)($_POST['product_id'] ?? 0);
$isAvailable = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 1;

if (!$productId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit;
}

try {
    // Verify product belongs to current farmer
    $checkStmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = :pid AND (farmer_id = :fid OR :is_admin = 'admin') LIMIT 1");
    $checkStmt->execute([
        ':pid' => $productId,
        ':fid' => $userId,
        ':is_admin' => $_SESSION['role'] ?? ''
    ]);

    if (!$checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found or unauthorized.']);
        exit;
    }

    // Update availability across weekly inventory
    $updateStmt = $pdo->prepare("UPDATE weekly_inventory SET is_available = :avail WHERE product_id = :pid");
    $updateStmt->execute([
        ':avail' => $isAvailable ? 1 : 0,
        ':pid' => $productId
    ]);

    echo json_encode([
        'status' => 'success',
        'is_available' => $isAvailable,
        'message' => $isAvailable ? 'Product marked available for pre-orders.' : 'Product marked temporarily unavailable.'
    ]);

} catch (Exception $e) {
    error_log("Toggle Stock Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating availability.']);
}
