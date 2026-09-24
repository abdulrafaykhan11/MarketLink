<?php
/**
 * MarketLink - Farmer API: Delete Produce Listing
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

if (!$productId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit;
}

try {
    // Delete product (cascades to weekly_inventory and cart_items per foreign keys)
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :pid AND (farmer_id = :fid OR :is_admin = 'admin')");
    $stmt->execute([
        ':pid' => $productId,
        ':fid' => $userId,
        ':is_admin' => $_SESSION['role'] ?? ''
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Produce listing deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found or unauthorized to delete.']);
    }
} catch (Exception $e) {
    error_log("Delete Product Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete product if active orders are attached.']);
}
