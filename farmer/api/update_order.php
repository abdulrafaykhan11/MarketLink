<?php
/**
 * MarketLink - Farmer API: Update Order Status
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
$orderId = (int)($_POST['order_id'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');
$reason = trim($_POST['reason'] ?? '');

$allowedStatuses = ['placed', 'accepted', 'ready_for_pickup', 'completed', 'cancelled', 'declined'];

if (!$orderId || !in_array($newStatus, $allowedStatuses, true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID or status transition.']);
    exit;
}

try {
    // 1. Fetch order details and verify ownership
    $stmt = $pdo->prepare("SELECT o.*, fp.stall_name, u.user_id as customer_user_id 
                           FROM orders o 
                           JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id 
                           JOIN customer_profiles cp ON o.customer_id = cp.customer_id 
                           JOIN users u ON cp.customer_id = u.user_id 
                           WHERE o.order_id = :oid AND (o.farmer_id = :uid OR :is_admin = 'admin') LIMIT 1");
    $stmt->execute([
        ':oid' => $orderId,
        ':uid' => $userId,
        ':is_admin' => $_SESSION['role'] ?? ''
    ]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or unauthorized access.']);
        exit;
    }

    // 2. Update status and optional cancellation reason
    $updateStmt = $pdo->prepare("UPDATE orders SET order_status = :status, cancellation_reason = :reason, updated_at = NOW() WHERE order_id = :oid");
    $updateStmt->execute([
        ':status' => $newStatus,
        ':reason' => ($newStatus === 'declined' || $newStatus === 'cancelled') ? ($reason ?: 'Cancelled by producer') : null,
        ':oid' => $orderId
    ]);

    // 3. Dispatch in-app customer notification
    $customerUserId = (int)$order['customer_user_id'];
    $orderNum = $order['order_number'];
    $stall = $order['stall_name'];

    $title = '';
    $message = '';

    switch ($newStatus) {
        case 'accepted':
            $title = 'Order Accepted! 🥕';
            $message = "Your pre-order #{$orderNum} has been accepted by {$stall}. It will be packed fresh for market pickup.";
            break;
        case 'ready_for_pickup':
            $title = 'Ready for Pickup! 🥬';
            $message = "Your pre-order #{$orderNum} is sorted and ready for collection at {$stall}. See you soon!";
            break;
        case 'completed':
            $title = 'Order Completed! 🎉';
            $message = "Your pre-order #{$orderNum} with {$stall} has been fulfilled. Thank you for supporting local growers!";
            break;
        case 'declined':
            $title = 'Order Declined';
            $message = "Your pre-order #{$orderNum} was declined by {$stall}. " . ($reason ? "Reason: {$reason}" : "Item was out of stock.");
            break;
        case 'cancelled':
            $title = 'Order Cancelled';
            $message = "Pre-order #{$orderNum} has been cancelled.";
            break;
    }

    if ($title && $message) {
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                    VALUES (:uid, :title, :msg, 'order_status', 0, NOW())");
        $notifStmt->execute([
            ':uid' => $customerUserId,
            ':title' => $title,
            ':msg' => $message
        ]);
    }

    // Dispatch status update email to customer via PHPMailer
    require_once __DIR__ . '/../../includes/mailer.php';
    sendOrderStatusEmail($orderId, $newStatus, $reason);

    echo json_encode([
        'status' => 'success',
        'message' => "Order #{$orderNum} status changed to " . ucfirst(str_replace('_', ' ', $newStatus))
    ]);

} catch (Exception $e) {
    error_log("Update Order Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating order.']);
}
