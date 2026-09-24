<?php
/**
 * MarketLink - Login Handler API
 * Authenticates users, validates account status, and routes to appropriate dashboards
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

function sendResponse(bool $success, string $message, ?string $redirect = null, array $errors = []): void {
    global $isAjax;
    if ($isAjax) {
        echo json_encode([
            'status'   => $success ? 'success' : 'error',
            'message'  => $message,
            'redirect' => $redirect,
            'errors'   => $errors
        ]);
        exit;
    } else {
        if ($success) {
            setFlash('success', $message);
            header("Location: " . ($redirect ?: BASE_URL . "/index.php"));
        } else {
            setFlash('error', $message);
            header("Location: " . BASE_URL . "/login.php");
        }
        exit;
    }
}

$loginId  = trim($_POST['login_id'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);

$errors = [];

if (empty($loginId)) {
    $errors['login_id'] = 'Please enter your username or email address.';
}

if (empty($password)) {
    $errors['password'] = 'Please enter your password.';
}

if (!empty($errors)) {
    sendResponse(false, 'Please fill in all required fields.', null, $errors);
}

try {
    $pdo = getDBConnection();

    // Query user by username OR email
    $stmt = $pdo->prepare("SELECT user_id, username, email, password_hash, role, status, phone_number 
                           FROM users 
                           WHERE LOWER(username) = LOWER(:id1) OR LOWER(email) = LOWER(:id2) 
                           LIMIT 1");
    $stmt->execute([':id1' => $loginId, ':id2' => $loginId]);
    $user = $stmt->fetch();

    if (!$user) {
        sendResponse(false, 'No account found with this username or email.', null, [
            'login_id' => 'Account does not exist.'
        ]);
    }

    // Check account status
    if ($user['status'] === 'suspended') {
        sendResponse(false, 'Your account has been suspended by administration. Please contact support.', null, [
            'account' => 'Account suspended'
        ]);
    }

    if ($user['status'] === 'inactive') {
        sendResponse(false, 'Your account is currently inactive.', null, [
            'account' => 'Account inactive'
        ]);
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        sendResponse(false, 'Incorrect password. Please try again.', null, [
            'password' => 'Incorrect password'
        ]);
    }

    // Fetch user's display name from their profile
    $displayName = $user['username'];
    if ($user['role'] === 'customer') {
        $pStmt = $pdo->prepare("SELECT full_name FROM customer_profiles WHERE customer_id = :uid LIMIT 1");
        $pStmt->execute([':uid' => $user['user_id']]);
        if ($profile = $pStmt->fetch()) {
            $displayName = $profile['full_name'] ?: $user['username'];
        }
        $redirectUrl = BASE_URL . "/customer/dashboard.php";
    } elseif ($user['role'] === 'farmer') {
        $pStmt = $pdo->prepare("SELECT contact_person, stall_name FROM farmer_profiles WHERE farmer_id = :uid LIMIT 1");
        $pStmt->execute([':uid' => $user['user_id']]);
        if ($profile = $pStmt->fetch()) {
            $displayName = $profile['contact_person'] ?: $profile['stall_name'] ?: $user['username'];
        }
        $redirectUrl = BASE_URL . "/farmer/dashboard.php";
    } elseif ($user['role'] === 'admin') {
        $redirectUrl = BASE_URL . "/admin/dashboard.php";
    } else {
        $redirectUrl = BASE_URL . "/index.php";
    }

    // Regenerate session ID for security against session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']   = (int) $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['full_name'] = $displayName;

    if ($remember) {
        // Optional cookie setting
        setcookie('marketlink_login_id', $user['username'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    // Dispatch login security notification via PHPMailer
    require_once __DIR__ . '/../includes/mailer.php';
    sendLoginAlertEmail($user['email'], $user['username'], $displayName);

    sendResponse(true, "Welcome back, " . htmlspecialchars($displayName) . "!", $redirectUrl);

} catch (Exception $e) {
    error_log("Login Exception: " . $e->getMessage());
    sendResponse(false, "Server error during login. Please try again later.");
}
