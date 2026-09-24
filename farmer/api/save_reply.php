<?php
/**
 * MarketLink - Farmer API: Respond to Customer Review
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

$reviewId = (int)($_POST['review_id'] ?? 0);
$replyText = trim($_POST['farmer_response'] ?? '');

if (!$reviewId || empty($replyText)) {
    echo json_encode(['status' => 'error', 'message' => 'Response text cannot be empty.']);
    exit;
}

try {
    // Verify review belongs to this farmer
    $checkReview = $pdo->prepare("SELECT fr.*, fp.stall_name, u.user_id as customer_user_id 
                                  FROM farmer_reviews fr 
                                  JOIN farmer_profiles fp ON fr.farmer_id = fp.farmer_id 
                                  JOIN customer_profiles cp ON fr.customer_id = cp.customer_id 
                                  JOIN users u ON cp.customer_id = u.user_id 
                                  WHERE fr.review_id = :rid AND (fr.farmer_id = :fid OR :is_admin = 'admin') LIMIT 1");
    $checkReview->execute([
        ':rid' => $reviewId,
        ':fid' => $userId,
        ':is_admin' => $_SESSION['role'] ?? ''
    ]);
    $review = $checkReview->fetch();

    if (!$review) {
        echo json_encode(['status' => 'error', 'message' => 'Review not found or unauthorized.']);
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE farmer_reviews SET farmer_response = :resp, response_date = NOW() WHERE review_id = :rid");
    $updateStmt->execute([
        ':resp' => $replyText,
        ':rid' => $reviewId
    ]);

    // Send customer in-app notification
    $custUid = (int)$review['customer_user_id'];
    $stall = $review['stall_name'];

    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                VALUES (:uid, :title, :msg, 'system', 0, NOW())");
    $notifStmt->execute([
        ':uid' => $custUid,
        ':title' => "Response to your review from {$stall} 💬",
        ':msg' => "Farmer {$stall} has responded to your feedback: \"{$replyText}\""
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Your response has been published to the review!'
    ]);

} catch (Exception $e) {
    error_log("Save Review Reply Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while saving response.']);
}
