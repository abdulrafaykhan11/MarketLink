<?php
/**
 * MarketLink - Farmer API: Manage Pickup Windows & Slots
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

$action = trim($_POST['action'] ?? 'create');

try {
    if ($action === 'toggle_status') {
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? 'available');

        if (!in_array($newStatus, ['available', 'full', 'closed'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid slot status.']);
            exit;
        }

        // Verify slot belongs to farmer's stall
        $checkStmt = $pdo->prepare("SELECT ps.pickup_slot_id 
                                   FROM pickup_slots ps 
                                   JOIN farmer_market_stalls fms ON ps.stall_id = fms.stall_id 
                                   WHERE ps.pickup_slot_id = :sid AND (fms.farmer_id = :fid OR :is_admin = 'admin')");
        $checkStmt->execute([
            ':sid' => $slotId,
            ':fid' => $userId,
            ':is_admin' => $_SESSION['role'] ?? ''
        ]);

        if (!$checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Slot not found or unauthorized.']);
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE pickup_slots SET status = :st WHERE pickup_slot_id = :sid");
        $updateStmt->execute([':st' => $newStatus, ':sid' => $slotId]);

        echo json_encode(['status' => 'success', 'message' => "Slot marked as {$newStatus}."]);
        exit;
    }

    // Otherwise create slot
    $stallId = (int)($_POST['stall_id'] ?? 0);
    $slotDate = trim($_POST['slot_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $maxOrders = (int)($_POST['max_orders'] ?? 10);

    if (!$stallId || empty($slotDate) || empty($startTime) || empty($endTime)) {
        echo json_encode(['status' => 'error', 'message' => 'All slot fields are required.']);
        exit;
    }

    // Validate ownership of stall
    $checkStall = $pdo->prepare("SELECT stall_id FROM farmer_market_stalls WHERE stall_id = :sid AND (farmer_id = :fid OR :is_admin = 'admin')");
    $checkStall->execute([
        ':sid' => $stallId,
        ':fid' => $userId,
        ':is_admin' => $_SESSION['role'] ?? ''
    ]);

    if (!$checkStall->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Stall not found or unauthorized.']);
        exit;
    }

    $insertStmt = $pdo->prepare("INSERT INTO pickup_slots (stall_id, slot_date, start_time, end_time, max_orders, status, created_at)
                                 VALUES (:sid, :sdate, :stime, :etime, :maxord, 'available', NOW())");
    $insertStmt->execute([
        ':sid' => $stallId,
        ':sdate' => $slotDate,
        ':stime' => $startTime,
        ':etime' => $endTime,
        ':maxord' => $maxOrders
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Pickup window added successfully for ' . date('M j, Y', strtotime($slotDate)) . '!'
    ]);

} catch (Exception $e) {
    error_log("Save Slot Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while saving pickup slot.']);
}
