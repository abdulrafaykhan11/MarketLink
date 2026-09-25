<?php
/**
 * MarketLink - Password reset flow
 * Email -> OTP verification -> new password
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

redirectIfLoggedIn();

$error = '';
$notice = '';
$stage = $_SESSION['password_reset_verified'] ?? false ? 'reset' : (!empty($_SESSION['password_reset_email']) ? 'verify' : 'email');

function clearPasswordReset(): void {
    unset(
        $_SESSION['password_reset_email'],
        $_SESSION['password_reset_user_id'],
        $_SESSION['password_reset_otp_hash'],
        $_SESSION['password_reset_expires'],
        $_SESSION['password_reset_attempts'],
        $_SESSION['password_reset_verified']
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_otp') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = getDBConnection()->prepare('SELECT user_id, username, email FROM users WHERE LOWER(email) = :email LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $error = 'No MarketLink account was found with this email address.';
                } else {
                    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    $otp = '';
                    for ($i = 0; $i < 6; $i++) {
                        $otp .= $characters[random_int(0, strlen($characters) - 1)];
                    }
                    $_SESSION['password_reset_email'] = $user['email'];
                    $_SESSION['password_reset_user_id'] = (int) $user['user_id'];
                    $_SESSION['password_reset_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
                    $_SESSION['password_reset_expires'] = time() + 600;
                    $_SESSION['password_reset_attempts'] = 0;
                    unset($_SESSION['password_reset_verified']);

                    require_once __DIR__ . '/includes/mailer.php';
                    if (sendPasswordResetOtpEmail($user['email'], $user['username'], $otp)) {
                        $stage = 'verify';
                        $notice = 'A 6-character verification code has been sent to ' . htmlspecialchars($user['email']) . '.';
                    } else {
                        clearPasswordReset();
                        $error = 'We could not send the verification email. Please try again shortly.';
                    }
                }
            } catch (Throwable $e) {
                error_log('Password reset OTP error: ' . $e->getMessage());
                $error = 'Unable to process your request right now. Please try again later.';
            }
        }
    } elseif ($action === 'verify_otp') {
        $otp = strtoupper(trim($_POST['otp'] ?? ''));
        $expires = (int) ($_SESSION['password_reset_expires'] ?? 0);
        $attempts = (int) ($_SESSION['password_reset_attempts'] ?? 0);
        if (empty($_SESSION['password_reset_otp_hash'])) {
            $error = 'Please request a new verification code.';
            $stage = 'email';
        } elseif (time() > $expires) {
            clearPasswordReset();
            $error = 'Your verification code has expired. Please request a new one.';
            $stage = 'email';
        } elseif ($attempts >= 5) {
            clearPasswordReset();
            $error = 'Too many incorrect attempts. Please request a new code.';
            $stage = 'email';
        } elseif (!preg_match('/^[A-Z0-9]{6}$/', $otp) || !password_verify($otp, $_SESSION['password_reset_otp_hash'])) {
            $_SESSION['password_reset_attempts'] = $attempts + 1;
            $error = 'That verification code is incorrect.';
            $stage = 'verify';
        } else {
            session_regenerate_id(true);
            $_SESSION['password_reset_verified'] = true;
            unset($_SESSION['password_reset_otp_hash'], $_SESSION['password_reset_attempts']);
            $stage = 'reset';
            $notice = 'Email verified. Choose a new secure password.';
        }
    } elseif ($action === 'reset_password') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $expires = (int) ($_SESSION['password_reset_expires'] ?? 0);
        if (empty($_SESSION['password_reset_verified']) || empty($_SESSION['password_reset_user_id']) || time() > $expires) {
            clearPasswordReset();
            $error = 'Your reset session has expired. Please request a new code.';
            $stage = 'email';
        } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
            $error = 'Use at least 8 characters with uppercase, lowercase, a number, and a special character.';
            $stage = 'reset';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
            $stage = 'reset';
        } else {
            try {
                $stmt = getDBConnection()->prepare('UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id');
                $stmt->execute([
                    ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    ':user_id' => (int) $_SESSION['password_reset_user_id']
                ]);
                clearPasswordReset();
                header('Location: ' . BASE_URL . '/login.php?reset=1');
                exit;
            } catch (Throwable $e) {
                error_log('Password reset update error: ' . $e->getMessage());
                $error = 'Your password could not be updated. Please try again.';
                $stage = 'reset';
            }
        }
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-banner">
      <div class="banner-ambient-glow"></div>
      <div class="banner-header"><a href="<?= BASE_URL ?>/" class="banner-logo-link"><img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink Logo" style="height:48px;"></a></div>
      <div class="banner-content">
        <div class="banner-tag"><span class="dot"></span> Account Recovery</div>
        <h1 class="banner-title">Get back to your <span class="gradient-text">local harvest</span> hub.</h1>
        <p class="banner-desc">Verify your email with a one-time code, then choose a new secure password.</p>
      </div>
      <div class="banner-footer"><span>MarketLink Account Security</span></div>
    </div>
    <div class="auth-form-container"><div class="auth-card">
      <div class="auth-header">
        <span class="auth-badge">Secure Password Reset</span>
        <h2 class="auth-title"><?= $stage === 'email' ? 'Forgot your password?' : ($stage === 'verify' ? 'Verify your email' : 'Create a new password') ?></h2>
        <p class="auth-subtitle"><?= $stage === 'email' ? 'Enter the email address connected to your account.' : ($stage === 'verify' ? 'Enter the 6-character code we sent to your email.' : 'Your new password will be used the next time you sign in.') ?></p>
      </div>
      <?php if ($error): ?><div class="reset-alert reset-alert-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($notice): ?><div class="reset-alert reset-alert-success" role="status"><?= $notice ?></div><?php endif; ?>
      <?php if ($stage === 'email'): ?>
        <form method="POST" novalidate><input type="hidden" name="action" value="send_otp"><div class="form-group full-width"><label class="form-label" for="email"><span>Email Address <span class="required">*</span></span></label><div class="input-container"><span class="input-icon-left">✉</span><input class="form-input" type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required></div></div><button type="submit" class="btn-primary"><span class="btn-text">Send verification code</span><span>→</span></button></form>
      <?php elseif ($stage === 'verify'): ?>
        <form method="POST"><input type="hidden" name="action" value="verify_otp"><div class="form-group full-width"><label class="form-label" for="otp"><span>Verification Code <span class="required">*</span></span></label><input class="form-input otp-input" type="text" id="otp" name="otp" placeholder="ABC123" autocomplete="one-time-code" maxlength="6" pattern="[A-Za-z0-9]{6}" required></div><button type="submit" class="btn-primary"><span class="btn-text">Verify code</span><span>→</span></button></form><form method="POST" class="reset-resend"><input type="hidden" name="action" value="send_otp"><input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['password_reset_email'] ?? '') ?>"><button type="submit" class="forgot-link reset-link-button">Send a new code</button></form>
      <?php else: ?>
        <form method="POST" novalidate><input type="hidden" name="action" value="reset_password"><div class="form-group full-width"><label class="form-label" for="password"><span>New Password <span class="required">*</span></span></label><div class="input-container"><span class="input-icon-left">🔒</span><input class="form-input" type="password" id="password" name="password" autocomplete="new-password" required><button type="button" class="password-toggle-btn" data-target="password" aria-label="Show new password" title="Show password"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button></div></div><div class="form-group full-width"><label class="form-label" for="confirm_password"><span>Confirm New Password <span class="required">*</span></span></label><div class="input-container"><span class="input-icon-left">🔒</span><input class="form-input" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required><button type="button" class="password-toggle-btn" data-target="confirm_password" aria-label="Show confirmed password" title="Show password"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button></div></div><p class="reset-password-help">At least 8 characters, with uppercase, lowercase, a number, and a special character.</p><button type="submit" class="btn-primary"><span class="btn-text">Update password</span><span>→</span></button></form>
      <?php endif; ?>
      <p class="reset-back"><a href="<?= BASE_URL ?>/login.php" class="forgot-link">← Back to sign in</a></p>
    </div></div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
