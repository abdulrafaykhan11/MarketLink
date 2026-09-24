<?php
/**
 * MarketLink - Farmer API: Update Stall Profile & GPS Map Location
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

$stallName = trim($_POST['stall_name'] ?? '');
$contactPerson = trim($_POST['contact_person'] ?? '');
$businessPhone = trim($_POST['business_phone'] ?? '');
$businessEmail = trim($_POST['business_email'] ?? '');
$description = trim($_POST['description'] ?? '');
$address = trim($_POST['address'] ?? '');
$latitude = (float)($_POST['latitude'] ?? 24.8607);
$longitude = (float)($_POST['longitude'] ?? 67.0011);

if (empty($stallName) || empty($contactPerson) || empty($businessPhone)) {
    echo json_encode(['status' => 'error', 'message' => 'Stall name, contact person, and phone number are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE farmer_profiles SET 
                            stall_name = :sname,
                            contact_person = :cperson,
                            business_phone = :bphone,
                            business_email = :bemail,
                            description = :desc,
                            address = :addr,
                            latitude = :lat,
                            longitude = :lng,
                            updated_at = NOW()
                            WHERE farmer_id = :fid");
    $stmt->execute([
        ':sname' => $stallName,
        ':cperson' => $contactPerson,
        ':bphone' => $businessPhone,
        ':bemail' => $businessEmail,
        ':desc' => $description,
        ':addr' => $address,
        ':lat' => $latitude,
        ':lng' => $longitude,
        ':fid' => $userId
    ]);

    // Also update users table phone number if matching
    $pdo->prepare("UPDATE users SET phone_number = :p WHERE user_id = :uid")->execute([':p' => $businessPhone, ':uid' => $userId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Farm profile and GPS coordinates saved successfully!'
    ]);

} catch (Exception $e) {
    error_log("Save Profile Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating profile.']);
}
