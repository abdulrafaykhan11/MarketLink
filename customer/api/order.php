<?php
/**
 * MarketLink - Order Management API (Cancel & Reorder)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$orderId = (int)($_POST['order_id'] ?? 0);

if (!$action || !$orderId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    exit;
}

try {
    // Verify order belongs to this customer
    $stmt = $pdo->prepare("SELECT o.*, fp.order_cutoff_time 
                           FROM orders o
                           JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                           WHERE o.order_id = :oid AND o.customer_id = :uid LIMIT 1");
    $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or permission denied.']);
        exit;
    }

    if ($action === 'cancel') {
        // Check if order can be cancelled
        if (in_array($order['order_status'], ['completed', 'cancelled', 'declined'])) {
            echo json_encode(['status' => 'error', 'message' => "Order is already {$order['order_status']} and cannot be cancelled."]);
            exit;
        }

        $reason = trim($_POST['reason'] ?? 'Cancelled by customer');
        if (empty($reason)) {
            $reason = 'Customer requested cancellation';
        }

        $update = $pdo->prepare("UPDATE orders SET order_status = 'cancelled', cancellation_reason = :reason, updated_at = NOW() WHERE order_id = :oid");
        $update->execute([':reason' => $reason, ':oid' => $orderId]);

        // Add customer notification
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                       VALUES (:uid, :title, :msg, 'order_status', 0, NOW())")
            ->execute([
                ':uid' => $userId,
                ':title' => "Order #{$order['order_number']} Cancelled",
                ':msg' => "Your pre-order has been cancelled. Reason: " . htmlspecialchars($reason)
            ]);

        // Dispatch cancellation email via PHPMailer
        require_once __DIR__ . '/../../includes/mailer.php';
        sendOrderStatusEmail($orderId, 'cancelled', $reason);

        echo json_encode([
            'status' => 'success',
            'message' => 'Pre-order cancelled successfully.'
        ]);
        exit;
    }

    if ($action === 'reorder') {
        // Fetch items from the past order
        $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :oid");
        $itemsStmt->execute([':oid' => $orderId]);
        $items = $itemsStmt->fetchAll();

        if (empty($items)) {
            echo json_encode(['status' => 'error', 'message' => 'No items found in this order.']);
            exit;
        }

        $stallId = $order['stall_id'];

        foreach ($items as $it) {
            // Check if item already exists in cart
            $chk = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE customer_id = :uid AND product_id = :pid AND stall_id = :sid");
            $chk->execute([':uid' => $userId, ':pid' => $it['product_id'], ':sid' => $stallId]);
            $existing = $chk->fetch();

            if ($existing) {
                $newQty = $existing['quantity'] + $it['quantity'];
                $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :cid")
                    ->execute([':qty' => $newQty, ':cid' => $existing['cart_item_id']]);
            } else {
                $pdo->prepare("INSERT INTO cart_items (customer_id, product_id, stall_id, quantity, added_at) VALUES (:uid, :pid, :sid, :qty, NOW())")
                    ->execute([':uid' => $userId, ':pid' => $it['product_id'], ':sid' => $stallId, ':qty' => $it['quantity']]);
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'All items added to your pre-order basket! 🛒',
            'redirect_url' => BASE_URL . '/customer/cart.php'
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);

} catch (Exception $e) {
    error_log("Order Action API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
