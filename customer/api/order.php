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

    if ($action === 'modify') {
        if ($order['order_status'] !== 'placed') {
            echo json_encode(['status' => 'error', 'message' => 'Only orders awaiting farmer confirmation can be modified.']);
            exit;
        }

        $pickupSlotId = (int)($_POST['pickup_slot_id'] ?? 0);
        $quantities = $_POST['quantities'] ?? [];

        if (!$pickupSlotId || !is_array($quantities) || empty($quantities)) {
            echo json_encode(['status' => 'error', 'message' => 'Choose a pickup time and valid item quantities.']);
            exit;
        }

        $slotStmt = $pdo->prepare("SELECT ps.pickup_slot_id, ps.slot_date, ps.max_orders, ps.status,
                                           COUNT(o.order_id) AS booked_orders
                                    FROM pickup_slots ps
                                    LEFT JOIN orders o ON o.pickup_slot_id = ps.pickup_slot_id
                                                     AND o.order_id != :oid
                                                     AND o.order_status NOT IN ('cancelled', 'declined')
                                    WHERE ps.pickup_slot_id = :sid
                                      AND ps.stall_id = :stall_id
                                      AND ps.slot_date >= CURDATE()
                                      AND (ps.status = 'available' OR ps.pickup_slot_id = :current_slot)
                                    GROUP BY ps.pickup_slot_id");
        $slotStmt->execute([
            ':oid' => $orderId,
            ':sid' => $pickupSlotId,
            ':stall_id' => $order['stall_id'],
            ':current_slot' => $order['pickup_slot_id']
        ]);
        $slot = $slotStmt->fetch();

        if (!$slot || ((int)$slot['booked_orders'] >= (int)$slot['max_orders'] && $pickupSlotId !== (int)$order['pickup_slot_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'That pickup time is no longer available. Please choose another slot.']);
            exit;
        }

        $itemsStmt = $pdo->prepare("SELECT oi.order_item_id, oi.product_id, oi.unit_price,
                                            COALESCE(MAX(wi.stock_quantity), 0) AS available_stock
                                     FROM order_items oi
                                     LEFT JOIN weekly_inventory wi ON wi.product_id = oi.product_id
                                                                   AND wi.stall_id = :stall_id
                                                                   AND wi.is_available = 1
                                     WHERE oi.order_id = :oid
                                     GROUP BY oi.order_item_id, oi.product_id, oi.unit_price");
        $itemsStmt->execute([':stall_id' => $order['stall_id'], ':oid' => $orderId]);
        $orderItems = $itemsStmt->fetchAll();

        if (empty($orderItems)) {
            echo json_encode(['status' => 'error', 'message' => 'No items were found in this order.']);
            exit;
        }

        $total = 0.0;
        $updates = [];
        foreach ($orderItems as $item) {
            $itemId = (string)$item['order_item_id'];
            $quantity = isset($quantities[$itemId]) ? (int)$quantities[$itemId] : 0;

            if ($quantity < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Every reserved item must have a quantity of at least one.']);
                exit;
            }

            if ($quantity > (int)$item['available_stock']) {
                echo json_encode(['status' => 'error', 'message' => 'One or more requested quantities exceed the currently available harvest stock.']);
                exit;
            }

            $subtotal = $quantity * (float)$item['unit_price'];
            $total += $subtotal;
            $updates[] = ['id' => (int)$item['order_item_id'], 'quantity' => $quantity, 'subtotal' => $subtotal];
        }

        $pdo->beginTransaction();
        $itemUpdateStmt = $pdo->prepare("UPDATE order_items SET quantity = :quantity, subtotal = :subtotal WHERE order_item_id = :id AND order_id = :oid");
        foreach ($updates as $update) {
            $itemUpdateStmt->execute([
                ':quantity' => $update['quantity'],
                ':subtotal' => $update['subtotal'],
                ':id' => $update['id'],
                ':oid' => $orderId
            ]);
        }

        $pdo->prepare("UPDATE orders
                       SET pickup_slot_id = :slot_id, pickup_date = :pickup_date, total_amount = :total, updated_at = NOW()
                       WHERE order_id = :oid AND customer_id = :uid AND order_status = 'placed'")
            ->execute([
                ':slot_id' => $pickupSlotId,
                ':pickup_date' => $slot['slot_date'],
                ':total' => $total,
                ':oid' => $orderId,
                ':uid' => $userId
            ]);

        $notificationStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                            VALUES (:uid, :title, :message, 'order_status', 0, NOW())");
        $notificationStmt->execute([
            ':uid' => $userId,
            ':title' => "Pre-Order #{$order['order_number']} Updated",
            ':message' => 'Your item quantities and pickup reservation have been updated successfully.'
        ]);
        $notificationStmt->execute([
            ':uid' => $order['farmer_id'],
            ':title' => "Pre-Order #{$order['order_number']} Updated",
            ':message' => 'The customer updated item quantities or their pickup reservation before confirmation.'
        ]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Pre-order updated successfully.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);

} catch (Exception $e) {
    error_log("Order Action API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
