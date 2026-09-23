<?php
/**
 * MarketLink - Review & Rating Submission API
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

try {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $farmerRating = max(1, min(5, (int)($_POST['farmer_rating'] ?? 5)));
    $farmerComment = trim($_POST['farmer_comment'] ?? '');

    if (!$orderId) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
        exit;
    }

    // Check order belongs to this user and is completed
    $orderStmt = $pdo->prepare("SELECT order_id, farmer_id, order_status FROM orders WHERE order_id = :oid AND customer_id = :uid LIMIT 1");
    $orderStmt->execute([':oid' => $orderId, ':uid' => $userId]);
    $order = $orderStmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        exit;
    }

    if ($order['order_status'] !== 'completed') {
        echo json_encode(['status' => 'error', 'message' => 'Reviews can only be submitted for completed orders.']);
        exit;
    }

    $farmerId = $order['farmer_id'];

    // Insert farmer review
    $fRevStmt = $pdo->prepare("INSERT INTO farmer_reviews (order_id, customer_id, farmer_id, rating, review_comment, is_moderated, created_at)
                               VALUES (:oid, :cid, :fid, :rating, :comment, 1, NOW())
                               ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_comment = VALUES(review_comment)");
    $fRevStmt->execute([
        ':oid' => $orderId,
        ':cid' => $userId,
        ':fid' => $farmerId,
        ':rating' => $farmerRating,
        ':comment' => $farmerComment
    ]);

    // Product reviews if provided
    $productRatings = $_POST['product_ratings'] ?? [];
    $productComments = $_POST['product_comments'] ?? [];

    if (is_array($productRatings)) {
        $pRevStmt = $pdo->prepare("INSERT INTO product_reviews (order_id, product_id, customer_id, rating, comment, is_moderated, created_at)
                                   VALUES (:oid, :pid, :cid, :rating, :comment, 1, NOW())
                                   ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
        foreach ($productRatings as $pid => $rating) {
            $pRating = max(1, min(5, (int)$rating));
            $pComment = trim($productComments[$pid] ?? '');
            $pRevStmt->execute([
                ':oid' => $orderId,
                ':pid' => (int)$pid,
                ':cid' => $userId,
                ':rating' => $pRating,
                ':comment' => $pComment
            ]);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you! Your feedback and rating have been submitted successfully. ⭐'
    ]);

} catch (Exception $e) {
    error_log("Review API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
