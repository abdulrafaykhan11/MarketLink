<?php
/**
 * MarketLink - Farmer API: Upload / Update Profile Photo
 * Compulsory per SRS: every farmer must have a profile photo
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

if (empty($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No valid photo file received. Please select an image.']);
    exit;
}

$file = $_FILES['profile_photo'];
$allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedMimes)) {
    echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, WEBP, or GIF photos are accepted.']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
    echo json_encode(['status' => 'error', 'message' => 'Photo file size must be under 5MB.']);
    exit;
}

$ext = match($mimeType) {
    'image/jpeg', 'image/jpg' => 'jpg',
    'image/png'               => 'png',
    'image/webp'              => 'webp',
    'image/gif'               => 'gif',
    default                   => 'jpg'
};

$uploadDir = __DIR__ . '/../../uploads/profiles/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Remove old profile image if any
$oldStmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = :uid");
$oldStmt->execute([':uid' => $userId]);
$oldImage = $oldStmt->fetchColumn();
if ($oldImage && file_exists(__DIR__ . '/../../' . $oldImage)) {
    @unlink(__DIR__ . '/../../' . $oldImage);
}

$filename = 'farmer_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save photo. Please check server permissions.']);
    exit;
}

$dbPath = 'uploads/profiles/' . $filename;

try {
    $pdo->prepare("UPDATE users SET profile_image = :img WHERE user_id = :uid")
        ->execute([':img' => $dbPath, ':uid' => $userId]);

    echo json_encode([
        'status'    => 'success',
        'message'   => 'Profile photo updated successfully!',
        'image_url' => $dbPath
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error saving photo.']);
}
