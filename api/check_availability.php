<?php
/**
 * MarketLink - Real-Time Availability Check Endpoint
 * Checks whether username or email is already taken in the database
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$type = trim($_GET['type'] ?? $_POST['type'] ?? '');
$value = trim($_GET['value'] ?? $_POST['value'] ?? '');

if (empty($type) || empty($value)) {
    echo json_encode([
        'available' => false,
        'message' => 'Type and value are required.'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();

    if ($type === 'username') {
        // Validation format
        if (strlen($value) < 3 || strlen($value) > 30) {
            echo json_encode([
                'available' => false,
                'message' => 'Username must be between 3 and 30 characters.'
            ]);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            echo json_encode([
                'available' => false,
                'message' => 'Only letters, numbers, and underscores are allowed.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE LOWER(username) = LOWER(:val) LIMIT 1");
        $stmt->execute([':val' => $value]);

        if ($stmt->fetch()) {
            echo json_encode([
                'available' => false,
                'message' => 'Username is already taken'
            ]);
        } else {
            echo json_encode([
                'available' => true,
                'message' => 'Username is available'
            ]);
        }
        exit;
    }

    if ($type === 'email') {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'available' => false,
                'message' => 'Invalid email address format.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE LOWER(email) = LOWER(:val) LIMIT 1");
        $stmt->execute([':val' => $value]);

        if ($stmt->fetch()) {
            echo json_encode([
                'available' => false,
                'message' => 'Email is already registered'
            ]);
        } else {
            echo json_encode([
                'available' => true,
                'message' => 'Email is available'
            ]);
        }
        exit;
    }

    echo json_encode([
        'available' => false,
        'message' => 'Invalid validation type.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'available' => false,
        'message' => 'Server error while checking availability.'
    ]);
}
