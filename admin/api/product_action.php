<?php
/**
 * MarketLink Admin API - Product Listing Moderation
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

$productId = (int)($_POST['product_id'] ?? 0);
$action    = trim($_POST['action'] ?? '');
$reason    = trim($_POST['reason'] ?? '');

if ($productId <= 0 || !in_array($action, ['delete', 'moderate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$pdo = getDBConnection();

try {
    // Fetch product details for notification to farmer
    $stmt = $pdo->prepare("SELECT product_name, farmer_id FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $productId]);
    $prod = $stmt->fetch();

    if (!$prod) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $pdo->beginTransaction();

    // Check if product is in order_items
    $inOrders = (int)$pdo->query("SELECT COUNT(*) FROM order_items WHERE product_id = {$productId}")->fetchColumn();
    if ($inOrders > 0) {
        // If part of existing historical orders, we can archive or remove from weekly inventory
        $pdo->prepare("DELETE FROM weekly_inventory WHERE product_id = :pid")->execute([':pid' => $productId]);
        $pdo->prepare("DELETE FROM cart_items WHERE product_id = :pid")->execute([':pid' => $productId]);
        // Also prefix description with [Removed by Admin]
        $pdo->prepare("UPDATE products SET description = CONCAT('[REMOVED BY PLATFORM MODERATION] ', COALESCE(description, '')) WHERE product_id = :pid")->execute([':pid' => $productId]);
    } else {
        // Safe to completely delete
        $pdo->prepare("DELETE FROM weekly_inventory WHERE product_id = :pid")->execute([':pid' => $productId]);
        $pdo->prepare("DELETE FROM cart_items WHERE product_id = :pid")->execute([':pid' => $productId]);
        $pdo->prepare("DELETE FROM favorite_products WHERE product_id = :pid")->execute([':pid' => $productId]);
        $pdo->prepare("DELETE FROM products WHERE product_id = :pid")->execute([':pid' => $productId]);
    }

    // Send moderation notification to farmer
    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                               VALUES (:uid, :title, :msg, 'system', 0, NOW())");
    $notifStmt->execute([
        ':uid'   => $prod['farmer_id'],
        ':title' => 'Product Listing Removed',
        ':msg'   => "Your listing '{$prod['product_name']}' was removed by platform administration" . (!empty($reason) ? " for: {$reason}" : " following content guideline review.")
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => "Product '{$prod['product_name']}' was successfully moderated and removed from catalog."]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Product moderation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
