<?php
/**
 * MarketLink - Real-World Gemini AI Chatbot API
 * Endpoint for both Public Visitors & Authenticated Users
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/GeminiBot.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'reply' => 'Method not allowed.']);
    exit;
}

$query = trim($_POST['query'] ?? '');
if (empty($query)) {
    // Also check raw json input if posted as application/json
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
        'reply'  => 'Please ask a question about our farm harvest, markets, farmers, or orders.'
    ]);
    exit;
}

// Decode history if provided as JSON string
$history = [];
if (!empty($rawHistory)) {
    if (is_string($rawHistory)) {
        $decodedHistory = json_decode($rawHistory, true);
        if (is_array($decodedHistory)) {
            $history = $decodedHistory;
        }
    } elseif (is_array($rawHistory)) {
        $history = $rawHistory;
    }
}

$userId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$userRole = $_SESSION['role'] ?? null;

$response = GeminiBot::ask($query, $history, $userId, $userRole);

echo json_encode($response);
exit;
