<?php
/**
 * MarketLink - Farmer API: Manage Market Stalls
 * Allows farmers to join and maintain stalls across multiple farmers markets
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
$action = trim($_POST['action'] ?? 'add_stall');

try {
    if ($action === 'add_stall') {
        $marketId = (int)($_POST['market_id'] ?? 0);
        $stallNumber = trim($_POST['stall_number_location'] ?? '');
        $operatingDays = trim($_POST['operating_days'] ?? 'Saturday, Sunday');

        if ($marketId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a valid farmers market.']);
            exit;
        }

        if (empty($stallNumber)) {
            $stallNumber = 'Stall #' . rand(1, 30);
        }

        // Verify market exists
        $mStmt = $pdo->prepare("SELECT market_name FROM markets WHERE market_id = :mid AND status = 'active' LIMIT 1");
        $mStmt->execute([':mid' => $marketId]);
        $marketName = $mStmt->fetchColumn();

        if (!$marketName) {
            echo json_encode(['status' => 'error', 'message' => 'Selected market is not currently active.']);
            exit;
        }

        // Check if stall record already exists for this farmer in this market
        $checkStmt = $pdo->prepare("SELECT stall_id, status FROM farmer_market_stalls WHERE farmer_id = :fid AND market_id = :mid LIMIT 1");
        $checkStmt->execute([':fid' => $userId, ':mid' => $marketId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            if ($existing['status'] === 'active') {
                // Update stall info
                $upd = $pdo->prepare("UPDATE farmer_market_stalls SET stall_number_location = :num, operating_days = :days WHERE stall_id = :sid");
                $upd->execute([':num' => $stallNumber, ':days' => $operatingDays, ':sid' => $existing['stall_id']]);
                echo json_encode([
                    'status' => 'success',
                    'message' => "Stall details updated for {$marketName}!"
                ]);
                exit;
            } else {
                // Reactivate
                $upd = $pdo->prepare("UPDATE farmer_market_stalls SET status = 'active', stall_number_location = :num, operating_days = :days WHERE stall_id = :sid");
                $upd->execute([':num' => $stallNumber, ':days' => $operatingDays, ':sid' => $existing['stall_id']]);
                echo json_encode([
                    'status' => 'success',
                    'message' => "Stall in {$marketName} reactivated successfully!"
                ]);
                exit;
            }
        } else {
            // Insert brand new stall in this market
            $ins = $pdo->prepare("INSERT INTO farmer_market_stalls (farmer_id, market_id, stall_number_location, operating_days, status, assigned_at)
                                  VALUES (:fid, :mid, :num, :days, 'active', NOW())");
            $ins->execute([
                ':fid' => $userId,
                ':mid' => $marketId,
                ':num' => $stallNumber,
                ':days' => $operatingDays
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => "Congratulations! New stall registered at {$marketName}."
            ]);
            exit;
        }
    }

    if ($action === 'toggle_status') {
        $stallId = (int)($_POST['stall_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? 'active');

        if (!in_array($newStatus, ['active', 'inactive'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid stall status']);
            exit;
        }

        $upd = $pdo->prepare("UPDATE farmer_market_stalls SET status = :st WHERE stall_id = :sid AND (farmer_id = :fid OR :is_admin = 'admin')");
        $upd->execute([
            ':st' => $newStatus,
            ':sid' => $stallId,
            ':fid' => $userId,
            ':is_admin' => $_SESSION['role'] ?? ''
        ]);

        echo json_encode(['status' => 'success', 'message' => "Stall marked as {$newStatus}."]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action request']);
    exit;

} catch (Exception $e) {
    error_log("Stall Action Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
