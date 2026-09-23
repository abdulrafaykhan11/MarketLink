<?php
/**
 * MarketLink - Authentication Guard & Session Security
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently authenticated
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user details
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
        'name'     => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'
    ];
}

/**
 * Restrict page access to specific roles
 * E.g., requireRole(['customer']) or requireRole(['farmer', 'admin'])
 */
function requireRole(array|string $roles): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }

    $roles = (array) $roles;
    $userRole = $_SESSION['role'] ?? '';

    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:3rem;text-align:center;">
            <h1>403 - Access Denied</h1>
            <p>You do not have permission to view this section.</p>
            <a href="' . BASE_URL . '/login.php">Back to safety</a>
        </div>');
    }
}

/**
 * If user is already logged in, redirect them away from guest pages (login/register)
 */
function redirectIfLoggedIn(): void {
    if (isLoggedIn()) {
        $role = $_SESSION['role'] ?? 'customer';
        switch ($role) {
            case 'admin':
                header("Location: " . BASE_URL . "/admin/dashboard.php");
                break;
            case 'farmer':
                header("Location: " . BASE_URL . "/farmer/dashboard.php");
                break;
            case 'customer':
            default:
                header("Location: " . BASE_URL . "/customer/dashboard.php");
                break;
        }
        exit;
    }
}

/**
 * Flash message helpers
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash(string $type): ?string {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}
