<?php
/**
 * MarketLink - Customer AI Assistant API (Powered by Gemini 3.8 Flash)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/GeminiBot.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$query = trim($_POST['query'] ?? '');
if (empty($query)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $json = json_decode($rawInput, true);
        $query = trim($json['query'] ?? '');
        $rawHistory = $json['history'] ?? null;
    }
} else {
    $rawHistory = $_POST['history'] ?? null;
}

if (empty($query)) {
    echo json_encode([
        'status' => 'error',
        'reply'  => 'Please ask a question about our harvest, stalls, markets, or your orders.'
    ]);
    exit;
}

$history = [];
if (!empty($rawHistory)) {
    if (is_string($rawHistory)) {
        $decoded = json_decode($rawHistory, true);
        if (is_array($decoded)) $history = $decoded;
    } elseif (is_array($rawHistory)) {
        $history = $rawHistory;
    }
}

$userId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$userRole = $_SESSION['role'] ?? 'customer';

$response = GeminiBot::ask($query, $history, $userId, $userRole);

echo json_encode($response);
exit;
