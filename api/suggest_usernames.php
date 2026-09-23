<?php
/**
 * MarketLink - Username Suggestion API
 * Generates smart, unique, available usernames based on user's name or input
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$name = trim($_GET['name'] ?? $_POST['name'] ?? '');

if (empty($name)) {
    echo json_encode(['status' => 'error', 'suggestions' => []]);
    exit;
}

try {
    $pdo = getDBConnection();

    // Clean name into clean lowercase alphanumeric tokens
    $clean = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    $parts = array_values(array_filter(explode(' ', $clean)));

    $candidates = [];

    if (count($parts) >= 2) {
        $first = $parts[0];
        $last = $parts[count($parts) - 1];
        
        $candidates[] = $first . '_' . $last;
        $candidates[] = $first . $last;
        $candidates[] = $last . '_' . $first;
        $candidates[] = $first . '.' . $last;
        $candidates[] = $first . rand(10, 99);
        $candidates[] = $first . '_' . $last . rand(1, 9);
        $candidates[] = substr($first, 0, 1) . '_' . $last;
        $candidates[] = $first . '_' . substr($last, 0, 1);
    } elseif (count($parts) === 1) {
        $single = $parts[0];
        $candidates[] = $single . '_' . rand(10, 99);
        $candidates[] = $single . rand(100, 999);
        $candidates[] = 'fresh_' . $single;
        $candidates[] = $single . '_pk';
        $candidates[] = 'user_' . $single;
    }

    // Filter valid candidate formats (3-30 chars, allowed chars)
    $validCandidates = [];
    foreach ($candidates as $cand) {
        $cand = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $cand));
        if (strlen($cand) >= 3 && strlen($cand) <= 30 && !in_array($cand, $validCandidates, true)) {
            $validCandidates[] = $cand;
        }
    }

    // Check availability in users table
    $availableSuggestions = [];
    if (!empty($validCandidates)) {
        $placeholders = implode(',', array_fill(0, count($validCandidates), '?'));
        $stmt = $pdo->prepare("SELECT LOWER(username) as username FROM users WHERE LOWER(username) IN ($placeholders)");
        $stmt->execute($validCandidates);
        $taken = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $takenMap = array_flip($taken);

        foreach ($validCandidates as $cand) {
            if (!isset($takenMap[$cand])) {
                $availableSuggestions[] = $cand;
                if (count($availableSuggestions) >= 4) {
                    break;
                }
            }
        }
    }

    // If still less than 3, generate random numbered fallbacks
    $baseName = !empty($parts) ? $parts[0] : 'user';
    $counter = 1;
    while (count($availableSuggestions) < 4 && $counter <= 10) {
        $fallback = $baseName . '_' . rand(10, 999);
        $chk = $pdo->prepare("SELECT user_id FROM users WHERE LOWER(username) = ? LIMIT 1");
        $chk->execute([$fallback]);
        if (!$chk->fetch() && !in_array($fallback, $availableSuggestions, true)) {
            $availableSuggestions[] = $fallback;
        }
        $counter++;
    }

    echo json_encode([
        'status' => 'success',
        'suggestions' => $availableSuggestions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate suggestions',
        'suggestions' => []
    ]);
}
