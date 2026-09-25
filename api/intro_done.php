<?php
/**
 * MarketLink - Intro Completion Endpoint
 * Called via fetch() when user clicks "GO TO HOMEPAGE" in intro.
 * Sets the session flag so index.php lets them through.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['intro_seen'] = true;

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
