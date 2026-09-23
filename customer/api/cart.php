<?php
/**
 * MarketLink - Customer Cart & Pre-Order Checkout API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please sign in.']);
    exit;
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

try {
    if ($action === 'count') {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = :uid");
        $stmt->execute([':uid' => $userId]);
        $count = (int)$stmt->fetchColumn();
        echo json_encode(['status' => 'success', 'count' => $count]);
        exit;
    }

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $stallId = (int)($_POST['stall_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$productId || !$stallId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product or stall selection.']);
            exit;
        }

        // Check if customer profile exists for foreign key constraint
        $chkProfile = $pdo->prepare("SELECT customer_id FROM customer_profiles WHERE customer_id = :uid");
        $chkProfile->execute([':uid' => $userId]);
        if (!$chkProfile->fetch()) {
            $pdo->prepare("INSERT INTO customer_profiles (customer_id, full_name, created_at) VALUES (:uid, :uname, NOW())")
                ->execute([':uid' => $userId, ':uname' => $_SESSION['username'] ?? 'Customer']);
        }

        // Check if item already exists in cart
        $checkStmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE customer_id = :uid AND product_id = :pid AND stall_id = :sid");
        $checkStmt->execute([':uid' => $userId, ':pid' => $productId, ':sid' => $stallId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :cid");
            $updateStmt->execute([':qty' => $newQty, ':cid' => $existing['cart_item_id']]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO cart_items (customer_id, product_id, stall_id, quantity, added_at) VALUES (:uid, :pid, :sid, :qty, NOW())");
            $insertStmt->execute([':uid' => $userId, ':pid' => $productId, ':sid' => $stallId, ':qty' => $qty]);
        }

        $totStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = :uid");
        $totStmt->execute([':uid' => $userId]);
        $totalItems = (int)$totStmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'message' => 'Harvest added to pre-order basket! 🥬',
            'total_items' => $totalItems
        ]);
        exit;
    }

    if ($action === 'update_qty') {
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :cid AND customer_id = :uid");
        $updateStmt->execute([':qty' => $qty, ':cid' => $cartItemId, ':uid' => $userId]);

        // Get single item subtotal
        $subStmt = $pdo->prepare("SELECT ci.quantity, wi.price 
                                  FROM cart_items ci
                                  JOIN weekly_inventory wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                  WHERE ci.cart_item_id = :cid LIMIT 1");
        $subStmt->execute([':cid' => $cartItemId]);
        $row = $subStmt->fetch();
        $itemSubtotal = $row ? number_format($row['quantity'] * $row['price'], 2) : '0.00';

        // Get grand total
        $grandStmt = $pdo->prepare("SELECT SUM(ci.quantity * wi.price) 
                                    FROM cart_items ci
                                    JOIN weekly_inventory wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                    WHERE ci.customer_id = :uid");
        $grandStmt->execute([':uid' => $userId]);
        $grandTotal = number_format((float)$grandStmt->fetchColumn(), 2);

        $totStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = :uid");
        $totStmt->execute([':uid' => $userId]);
        $totalItems = (int)$totStmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'item_subtotal' => $itemSubtotal,
            'grand_total' => $grandTotal,
            'total_items' => $totalItems
        ]);
        exit;
    }

    if ($action === 'remove') {
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);

        $delStmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = :cid AND customer_id = :uid");
        $delStmt->execute([':cid' => $cartItemId, ':uid' => $userId]);

        $grandStmt = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity * wi.price), 0) 
                                    FROM cart_items ci
                                    JOIN weekly_inventory wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                    WHERE ci.customer_id = :uid");
        $grandStmt->execute([':uid' => $userId]);
        $grandTotal = number_format((float)$grandStmt->fetchColumn(), 2);

        $totStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = :uid");
        $totStmt->execute([':uid' => $userId]);
        $totalItems = (int)$totStmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'grand_total' => $grandTotal,
            'total_items' => $totalItems
        ]);
        exit;
    }

    if ($action === 'checkout') {
        // Confirm Pre-Order
        $pickupDate = $_POST['pickup_date'] ?? '';
        $pickupSlotId = (int)($_POST['pickup_slot_id'] ?? 0);

        if (empty($pickupDate) || !$pickupSlotId) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a pickup date and pickup time window.']);
            exit;
        }

        // Fetch cart items
        $cartItemsStmt = $pdo->prepare("SELECT ci.cart_item_id, ci.product_id, ci.stall_id, ci.quantity, 
                                               wi.price, fms.farmer_id, fms.market_id, p.product_name
                                        FROM cart_items ci
                                        JOIN weekly_inventory wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                        JOIN farmer_market_stalls fms ON ci.stall_id = fms.stall_id
                                        JOIN products p ON ci.product_id = p.product_id
                                        WHERE ci.customer_id = :uid
                                        GROUP BY ci.cart_item_id");
        $cartItemsStmt->execute([':uid' => $userId]);
        $cartItems = $cartItemsStmt->fetchAll();

        if (empty($cartItems)) {
            echo json_encode(['status' => 'error', 'message' => 'Your pre-order basket is empty.']);
            exit;
        }

        // Group items by stall/farmer
        $byStall = [];
        foreach ($cartItems as $item) {
            $byStall[$item['stall_id']][] = $item;
        }

        $createdOrderIds = [];
        $orderNumbers = [];

        $pdo->beginTransaction();

        foreach ($byStall as $stallId => $items) {
            $farmerId = $items[0]['farmer_id'];
            $marketId = $items[0]['market_id'];

            // Calculate stall total
            $stallTotal = 0;
            foreach ($items as $it) {
                $stallTotal += ($it['quantity'] * $it['price']);
            }

            // Generate order number
            $orderNumber = 'ML-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

            $ordStmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, farmer_id, market_id, stall_id, pickup_slot_id, total_amount, order_status, pickup_date, created_at)
                                      VALUES (:num, :cid, :fid, :mid, :sid, :pslot, :tot, 'placed', :pdate, NOW())");
            $ordStmt->execute([
                ':num' => $orderNumber,
                ':cid' => $userId,
                ':fid' => $farmerId,
                ':mid' => $marketId,
                ':sid' => $stallId,
                ':pslot' => $pickupSlotId,
                ':tot' => $stallTotal,
                ':pdate' => $pickupDate
            ]);
            $orderId = $pdo->lastInsertId();
            $createdOrderIds[] = $orderId;
            $orderNumbers[] = $orderNumber;

            // Insert order items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
                                       VALUES (:oid, :pid, :qty, :pr, :sub)");
            foreach ($items as $it) {
                $sub = $it['quantity'] * $it['price'];
                $itemStmt->execute([
                    ':oid' => $orderId,
                    ':pid' => $it['product_id'],
                    ':qty' => $it['quantity'],
                    ':pr' => $it['price'],
                    ':sub' => $sub
                ]);
            }

            // In-app customer notification
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                        VALUES (:uid, :title, :msg, 'order_status', 0, NOW())");
            $notifStmt->execute([
                ':uid' => $userId,
                ':title' => "Pre-Order #{$orderNumber} Placed Successfully",
                ':msg' => "Your harvest pre-order of Rs. " . number_format($stallTotal, 2) . " has been submitted to the farmer for pickup on " . htmlspecialchars($pickupDate) . ". Payment is settled upon pickup."
            ]);
        }

        // Clear customer cart
        $pdo->prepare("DELETE FROM cart_items WHERE customer_id = :uid")->execute([':uid' => $userId]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Your harvest pre-order has been placed successfully! 🌿',
            'order_numbers' => $orderNumbers,
            'redirect_url' => BASE_URL . '/customer/orders.php'
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action request.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Cart API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
