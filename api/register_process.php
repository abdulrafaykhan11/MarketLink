<?php
/**
 * MarketLink - Registration Handler API
 * Handles user signup with atomic database transactions & strict validation
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
            header("Location: " . ($redirect ?: BASE_URL . "/login.php"));
        } else {
            setFlash('error', $message);
            header("Location: " . BASE_URL . "/register.php");
        }
        exit;
    }
}

// 1. Gather & Sanitize POST Inputs
$role             = trim($_POST['role'] ?? 'customer');
$username         = trim($_POST['username'] ?? '');
$email            = trim($_POST['email'] ?? '');
$phone_number     = trim($_POST['phone_number'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms_accepted   = isset($_POST['terms_accepted']) && ($_POST['terms_accepted'] === '1' || $_POST['terms_accepted'] === 'on');

// Customer-specific
$full_name        = trim($_POST['full_name'] ?? '');
$address          = trim($_POST['address'] ?? '');

// Farmer-specific
$stall_name       = trim($_POST['stall_name'] ?? '');
$contact_person   = trim($_POST['contact_person'] ?? $full_name);
$business_address = trim($_POST['business_address'] ?? $address);

$errors = [];

// 2. Comprehensive Validations
// Mandatory Terms & Agreement acceptance
if (!$terms_accepted) {
    $errors['terms'] = 'You must review and agree to the Terms of Service and Privacy Policy to create an account.';
}

// Role validation
if (!in_array($role, ['customer', 'farmer'], true)) {
    $errors['role'] = 'Invalid account role selected.';
}

// Username validation
if (strlen($username) < 3 || strlen($username) > 30) {
    $errors['username'] = 'Username must be between 3 and 30 characters.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

// Phone number validation (Pakistani mobile format: 03XX-XXXXXXX or +923XXXXXXXXX)
if (!empty($phone_number)) {
    $cleanPhone = preg_replace('/[-\s]/', '', $phone_number);
    if (!preg_match('/^((\+92)|(0092)|(92)|(0))?3[0-9]{9}$/', $cleanPhone)) {
        $errors['phone_number'] = 'Please enter a valid Pakistani mobile number (e.g. 03001234567 or +923001234567).';
    }
}

// Password validations
if (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long.';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter (A-Z).';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter (a-z).';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one digit (0-9).';
} elseif (!preg_match('/[\W_]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character (!@#$%^&*).';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

// Role-specific fields
if ($role === 'customer') {
    if (strlen($full_name) < 2) {
        $errors['full_name'] = 'Full Name is required (minimum 2 characters).';
    }
} elseif ($role === 'farmer') {
    if (strlen($stall_name) < 2) {
        $errors['stall_name'] = 'Farm / Stall Name is required.';
    }
    if (strlen($contact_person) < 2) {
        $errors['contact_person'] = 'Contact Person name is required.';
    }
    if (empty($business_address)) {
        $errors['business_address'] = 'Farm / Market pickup address is required.';
    }
}

if (!empty($errors)) {
    sendResponse(false, 'Please correct the highlighted errors before submitting.', null, $errors);
}

// 3. Database Execution
try {
    $pdo = getDBConnection();

    // Check uniqueness for username and email
    $checkStmt = $pdo->prepare("SELECT username, email FROM users WHERE LOWER(username) = LOWER(:u) OR LOWER(email) = LOWER(:e) LIMIT 1");
    $checkStmt->execute([':u' => $username, ':e' => $email]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        if (strcasecmp($existing['username'], $username) === 0) {
            $errors['username'] = 'This username is already taken. Please choose another.';
        }
        if (strcasecmp($existing['email'], $email) === 0) {
            $errors['email'] = 'This email address is already registered. Please log in.';
        }
        sendResponse(false, 'Account with provided details already exists.', null, $errors);
    }

    // Begin atomic transaction
    $pdo->beginTransaction();

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insertUserSql = "INSERT INTO users (username, email, password_hash, role, status, phone_number) 
                      VALUES (:username, :email, :password_hash, :role, 'active', :phone_number)";
    $userStmt = $pdo->prepare($insertUserSql);
    $userStmt->execute([
        ':username'      => $username,
        ':email'         => strtolower($email),
        ':password_hash' => $passwordHash,
        ':role'          => $role,
        ':phone_number'  => $phone_number ?: null
    ]);

    $newUserId = (int) $pdo->lastInsertId();

    if ($role === 'customer') {
        $profileSql = "INSERT INTO customer_profiles (customer_id, full_name, default_address) 
                       VALUES (:cid, :full_name, :address)";
        $profileStmt = $pdo->prepare($profileSql);
        $profileStmt->execute([
            ':cid'       => $newUserId,
            ':full_name' => $full_name,
            ':address'   => $address ?: null
        ]);
        $displayName = $full_name;
        $redirectUrl = BASE_URL . "/customer/dashboard.php";
    } else {
        // Farmer profile
        $farmerSql = "INSERT INTO farmer_profiles 
                      (farmer_id, stall_name, contact_person, business_phone, business_email, address, latitude, longitude, approval_status) 
                      VALUES (:fid, :stall_name, :contact_person, :bphone, :bemail, :address, 0.00000000, 0.00000000, 'pending')";
        $farmerStmt = $pdo->prepare($farmerSql);
        $farmerStmt->execute([
            ':fid'            => $newUserId,
            ':stall_name'     => $stall_name,
            ':contact_person' => $contact_person,
            ':bphone'         => $phone_number ?: 'N/A',
            ':bemail'         => $email,
            ':address'        => $business_address
        ]);
        $displayName = $contact_person;
        $redirectUrl = BASE_URL . "/farmer/dashboard.php";
    }

    $pdo->commit();

    // Dispatch welcome email via PHPMailer
    require_once __DIR__ . '/../includes/mailer.php';
    sendWelcomeEmail($email, $username, $displayName, $role);

    // Do NOT auto-login user: redirect to login page with prefilled username
    $loginRedirect = BASE_URL . "/login.php?registered=" . urlencode($username);

    sendResponse(true, "Account created successfully! A welcome confirmation email has been dispatched to your address.", $loginRedirect);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Registration Exception: " . $e->getMessage());
    sendResponse(false, "Registration could not be completed: " . $e->getMessage());
}
