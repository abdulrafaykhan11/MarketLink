<?php
/**
 * MarketLink - Favorite Farmers & Products API
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
$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if (!$type || !$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    exit;
}

try {
    // Check/create customer profile if missing
    $chkProfile = $pdo->prepare("SELECT customer_id FROM customer_profiles WHERE customer_id = :uid");
    $chkProfile->execute([':uid' => $userId]);
    if (!$chkProfile->fetch()) {
        $pdo->prepare("INSERT INTO customer_profiles (customer_id, full_name, created_at) VALUES (:uid, :uname, NOW())")
            ->execute([':uid' => $userId, ':uname' => $_SESSION['username'] ?? 'Customer']);
    }

    if ($type === 'product') {
        $chk = $pdo->prepare("SELECT favorite_id FROM favorite_products WHERE customer_id = :uid AND product_id = :pid");
        $chk->execute([':uid' => $userId, ':pid' => $id]);
        $fav = $chk->fetch();

        if ($fav) {
            $del = $pdo->prepare("DELETE FROM favorite_products WHERE favorite_id = :fid");
            $del->execute([':fid' => $fav['favorite_id']]);
            echo json_encode(['status' => 'success', 'is_favorite' => false, 'message' => 'Removed from saved products.']);
        } else {
            $ins = $pdo->prepare("INSERT INTO favorite_products (customer_id, product_id, created_at) VALUES (:uid, :pid, NOW())");
            $ins->execute([':uid' => $userId, ':pid' => $id]);
            echo json_encode(['status' => 'success', 'is_favorite' => true, 'message' => 'Saved to favorite products! ❤️']);
        }
        exit;
    }

    if ($type === 'farmer') {
        $chk = $pdo->prepare("SELECT favorite_id FROM favorite_farmers WHERE customer_id = :uid AND farmer_id = :fid");
        $chk->execute([':uid' => $userId, ':fid' => $id]);
        $fav = $chk->fetch();

        if ($fav) {
            $del = $pdo->prepare("DELETE FROM favorite_farmers WHERE favorite_id = :fid");
            $del->execute([':fid' => $fav['favorite_id']]);
            echo json_encode(['status' => 'success', 'is_favorite' => false, 'message' => 'Removed from favorite farmers.']);
        } else {
            $ins = $pdo->prepare("INSERT INTO favorite_farmers (customer_id, farmer_id, created_at) VALUES (:uid, :fid, NOW())");
            $ins->execute([':uid' => $userId, ':fid' => $id]);
            echo json_encode(['status' => 'success', 'is_favorite' => true, 'message' => 'Farmer saved to favorites! ❤️']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown favorite type.']);

} catch (Exception $e) {
    error_log("Favorite API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
