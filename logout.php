<?php
/**
 * MarketLink - Secure Logout Handler
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session to pass flash message
session_start();
setFlash('info', 'You have been safely signed out. See you soon!');
header("Location: " . BASE_URL . "/login.php");
exit;
