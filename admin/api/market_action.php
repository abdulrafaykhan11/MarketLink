<?php
/**
 * MarketLink Admin API - Markets Management (Create / Update / Delete)
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

$pdo = getDBConnection();
$action = trim($_POST['action'] ?? '');

try {
    if ($action === 'delete') {
        $marketId = (int)($_POST['market_id'] ?? 0);
        if ($marketId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid market ID']);
            exit;
        }

        // Check if stalls are active
        $stallCount = (int)$pdo->query("SELECT COUNT(*) FROM farmer_market_stalls WHERE market_id = {$marketId}")->fetchColumn();
        if ($stallCount > 0) {
            // Soft delete / set to inactive rather than breaking constraints
            $pdo->prepare("UPDATE markets SET status = 'inactive' WHERE market_id = :id")->execute([':id' => $marketId]);
            echo json_encode(['success' => true, 'message' => 'Market has active stalls. Its status was marked as Inactive.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM markets WHERE market_id = :id");
        $stmt->execute([':id' => $marketId]);
        echo json_encode(['success' => true, 'message' => 'Market successfully removed.']);
        exit;
    }

    if ($action === 'save') {
        $marketId      = (int)($_POST['market_id'] ?? 0);
        $marketName    = trim($_POST['market_name'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $city          = trim($_POST['city'] ?? 'Metropolis');
        $state         = trim($_POST['state'] ?? 'State');
        $postalCode    = trim($_POST['postal_code'] ?? '');
        $lat           = (float)($_POST['latitude'] ?? 0.0);
        $lng           = (float)($_POST['longitude'] ?? 0.0);
        $days          = trim($_POST['operating_days'] ?? '');
        $hours         = trim($_POST['operating_hours'] ?? '');
        $status        = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (empty($marketName) || empty($address) || empty($days) || empty($hours)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Please provide market name, address, operating days, and hours.']);
            exit;
        }

        if ($marketId > 0) {
            // Update existing market
            $sql = "UPDATE markets SET 
                        market_name = :name, 
                        address = :addr, 
                        city = :city, 
                        state = :state, 
                        postal_code = :postal, 
                        latitude = :lat, 
                        longitude = :lng, 
                        operating_days = :days, 
                        operating_hours = :hours, 
                        status = :status 
                    WHERE market_id = :mid";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'   => $marketName,
                ':addr'   => $address,
                ':city'   => $city,
                ':state'  => $state,
                ':postal' => $postalCode,
                ':lat'    => $lat,
                ':lng'    => $lng,
                ':days'   => $days,
                ':hours'  => $hours,
                ':status' => $status,
                ':mid'    => $marketId
            ]);
            echo json_encode(['success' => true, 'message' => 'Market updated successfully!']);
            exit;
        } else {
            // Create new market
            $sql = "INSERT INTO markets (market_name, address, city, state, postal_code, latitude, longitude, operating_days, operating_hours, status, created_by, created_at)
                    VALUES (:name, :addr, :city, :state, :postal, :lat, :lng, :days, :hours, :status, :uid, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'   => $marketName,
                ':addr'   => $address,
                ':city'   => $city,
                ':state'  => $state,
                ':postal' => $postalCode,
                ':lat'    => $lat,
                ':lng'    => $lng,
                ':days'   => $days,
                ':hours'  => $hours,
                ':status' => $status,
                ':uid'    => (int)$_SESSION['user_id']
            ]);
            echo json_encode(['success' => true, 'message' => 'New farmers market established successfully!']);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action requested']);
} catch (Exception $e) {
    error_log("Market action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
