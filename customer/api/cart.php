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

        $stockStmt = $pdo->prepare("SELECT MAX(stock_quantity) AS available_stock
                                    FROM weekly_inventory
                                    WHERE product_id = :pid AND stall_id = :sid AND is_available = 1");
        $stockStmt->execute([':pid' => $productId, ':sid' => $stallId]);
        $availableStock = (float)($stockStmt->fetchColumn() ?? 0);
        if ($availableStock <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'This item is no longer available for pre-order.']);
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

        $newQty = $existing ? ((float)$existing['quantity'] + $qty) : $qty;
        if ($newQty > $availableStock) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Only ' . rtrim(rtrim(number_format($availableStock, 2, '.', ''), '0'), '.') . ' unit(s) are currently available.'
            ]);
            exit;
        }

        if ($existing) {
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

        $cartStockStmt = $pdo->prepare("SELECT ci.product_id, ci.stall_id, MAX(wi.stock_quantity) AS available_stock
                                        FROM cart_items ci
                                        LEFT JOIN weekly_inventory wi ON wi.product_id = ci.product_id
                                                                     AND wi.stall_id = ci.stall_id
                                                                     AND wi.is_available = 1
                                        WHERE ci.cart_item_id = :cid AND ci.customer_id = :uid
                                        GROUP BY ci.cart_item_id, ci.product_id, ci.stall_id");
        $cartStockStmt->execute([':cid' => $cartItemId, ':uid' => $userId]);
        $cartStock = $cartStockStmt->fetch();
        if (!$cartStock) {
            echo json_encode(['status' => 'error', 'message' => 'Cart item was not found.']);
            exit;
        }
        $availableStock = (float)($cartStock['available_stock'] ?? 0);
        if ($qty > $availableStock) {
            echo json_encode([
                'status' => 'error',
                'message' => $availableStock > 0
                    ? 'Only ' . rtrim(rtrim(number_format($availableStock, 2, '.', ''), '0'), '.') . ' unit(s) are currently available.'
                    : 'This item is no longer available for pre-order.'
            ]);
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :cid AND customer_id = :uid");
        $updateStmt->execute([':qty' => $qty, ':cid' => $cartItemId, ':uid' => $userId]);

        // Get single item subtotal
        $subStmt = $pdo->prepare("SELECT ci.quantity, wi.price
                                  FROM cart_items ci
                                  JOIN (
                                      SELECT product_id, stall_id, MAX(price) AS price
                                      FROM weekly_inventory
                                      GROUP BY product_id, stall_id
                                  ) wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                  WHERE ci.cart_item_id = :cid AND ci.customer_id = :uid LIMIT 1");
        $subStmt->execute([':cid' => $cartItemId, ':uid' => $userId]);
        $row = $subStmt->fetch();
        $itemSubtotal = $row ? number_format($row['quantity'] * $row['price'], 2) : '0.00';

        // Get grand total
        $grandStmt = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity * wi.price), 0)
                                    FROM cart_items ci
                                    JOIN (
                                        SELECT product_id, stall_id, MAX(price) AS price
                                        FROM weekly_inventory
                                        GROUP BY product_id, stall_id
                                    ) wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                    WHERE ci.customer_id = :uid");
        $grandStmt->execute([':uid' => $userId]);
        $grandTotal = number_format((float)$grandStmt->fetchColumn(), 2);

        $totStmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = :uid");
        $totStmt->execute([':uid' => $userId]);
        $totalItems = (int)$totStmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'quantity' => $row ? (int)$row['quantity'] : $qty,
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
                                    JOIN (
                                        SELECT product_id, stall_id, MAX(price) AS price
                                        FROM weekly_inventory
                                        GROUP BY product_id, stall_id
                                    ) wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
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
        $pickupDates = $_POST['pickup_date'] ?? [];
        $pickupSlotIds = $_POST['pickup_slot_id'] ?? [];
        if (!is_array($pickupDates) || !is_array($pickupSlotIds)) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a pickup date and pickup time window for every stall.']);
            exit;
        }

        // Fetch cart items
        $cartItemsStmt = $pdo->prepare("SELECT ci.cart_item_id, ci.product_id, ci.stall_id, ci.quantity, 
                                               wi.price, fms.farmer_id, fms.market_id, p.product_name
                                        FROM cart_items ci
                                        JOIN (
                                            SELECT product_id, stall_id, MAX(price) AS price
                                            FROM weekly_inventory
                                            WHERE is_available = 1
                                            GROUP BY product_id, stall_id
                                        ) wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                                        JOIN farmer_market_stalls fms ON ci.stall_id = fms.stall_id
                                        JOIN products p ON ci.product_id = p.product_id
                                        WHERE ci.customer_id = :uid");
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
        ksort($byStall, SORT_NUMERIC);

        foreach ($byStall as $stallId => $items) {
            $pickupDate = trim((string)($pickupDates[$stallId] ?? ''));
            $pickupSlotId = (int)($pickupSlotIds[$stallId] ?? 0);
            $pickupDateObject = DateTime::createFromFormat('!Y-m-d', $pickupDate);
            if (!$pickupDateObject || $pickupDateObject->format('Y-m-d') !== $pickupDate || !$pickupSlotId) {
                throw new RuntimeException('Select a valid pickup date and window for every stall.');
            }

            // Lock the slot while checking its capacity, so concurrent checkout
            // requests cannot reserve more orders than the farmer allows.
            $slotStmt = $pdo->prepare("SELECT pickup_slot_id, max_orders
                                       FROM pickup_slots
                                       WHERE pickup_slot_id = :slot_id
                                         AND stall_id = :stall_id
                                         AND slot_date = :pickup_date
                                         AND slot_date >= CURDATE()
                                         AND status = 'available'
                                       LIMIT 1 FOR UPDATE");
            $slotStmt->execute([
                ':slot_id' => $pickupSlotId,
                ':stall_id' => $stallId,
                ':pickup_date' => $pickupDate
            ]);
            $slot = $slotStmt->fetch();
            if (!$slot) {
                throw new RuntimeException('A selected pickup window is no longer available. Please choose another window.');
            }

            $slotCountStmt = $pdo->prepare("SELECT COUNT(*)
                                            FROM orders
                                            WHERE pickup_slot_id = :slot_id
                                              AND order_status NOT IN ('cancelled', 'declined')");
            $slotCountStmt->execute([':slot_id' => $pickupSlotId]);
            $bookedOrders = (int)$slotCountStmt->fetchColumn();
            if ($bookedOrders >= (int)$slot['max_orders']) {
                throw new RuntimeException('A selected pickup window is full. Please choose another window.');
            }

            // Lock the live inventory rows and verify that previously reserved
            // active orders plus this request do not exceed available stock.
            $inventoryStmt = $pdo->prepare("SELECT stock_quantity
                                            FROM weekly_inventory
                                            WHERE product_id = :product_id
                                              AND stall_id = :stall_id
                                              AND is_available = 1
                                            FOR UPDATE");
            $reservedStockStmt = $pdo->prepare("SELECT COALESCE(SUM(oi.quantity), 0)
                                                FROM order_items oi
                                                JOIN orders o ON o.order_id = oi.order_id
                                                WHERE oi.product_id = :product_id
                                                  AND o.stall_id = :stall_id
                                                  AND o.order_status IN ('placed', 'accepted', 'ready_for_pickup')");

            foreach ($items as $item) {
                $inventoryStmt->execute([
                    ':product_id' => $item['product_id'],
                    ':stall_id' => $stallId
                ]);
                $inventoryRows = $inventoryStmt->fetchAll(PDO::FETCH_COLUMN);
                $availableStock = $inventoryRows ? max(array_map('floatval', $inventoryRows)) : 0;
                if ($availableStock <= 0) {
                    throw new RuntimeException(htmlspecialchars($item['product_name']) . ' is no longer available for pre-order.');
                }

                $reservedStockStmt->execute([
                    ':product_id' => $item['product_id'],
                    ':stall_id' => $stallId
                ]);
                $reservedStock = (float)$reservedStockStmt->fetchColumn();
                if (($reservedStock + (float)$item['quantity']) > $availableStock) {
                    throw new RuntimeException('Insufficient stock remains for ' . htmlspecialchars($item['product_name']) . '. Please adjust your basket.');
                }
            }

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

        // Dispatch order confirmation emails to customer and farmer via PHPMailer
        require_once __DIR__ . '/../../includes/mailer.php';
        foreach ($createdOrderIds as $newOid) {
            sendOrderConfirmationEmails((int)$newOid);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Your harvest pre-order has been placed successfully! 🌿 Confirmation emails have been sent.',
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
