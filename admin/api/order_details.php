<?php
/**
 * MarketLink - Admin API: Fetch Order Details & Items
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['admin', 'farmer', 'customer']);

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Fetch order
    $stmt = $pdo->prepare("SELECT o.*, 
                                  u.username as customer_name, u.email as customer_email, u.phone_number as customer_phone,
                                  fp.stall_name, fp.contact_person as farmer_name,
                                  m.market_name, m.address as market_address,
                                  ps.start_time, ps.end_time
                           FROM orders o
                           JOIN users u ON o.customer_id = u.user_id
                           JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                           JOIN markets m ON o.market_id = m.market_id
                           LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                           WHERE o.order_id = :oid LIMIT 1");
    $stmt->execute([':oid' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch line items
    $iStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.unit, p.image_url
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.product_id
                            WHERE oi.order_id = :oid");
    $iStmt->execute([':oid' => $orderId]);
    $items = $iStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
