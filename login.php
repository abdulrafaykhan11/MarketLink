<?php
/**
 * MarketLink - User Login Page
 * Authenticates Customer, Farmer, or Admin with live format validations
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Redirect if already logged in
redirectIfLoggedIn();

$registeredUser = trim($_GET['registered'] ?? '');
$passwordReset = !empty($_GET['reset']);
$rememberedUsername = !empty($registeredUser) ? $registeredUser : ($_COOKIE['marketlink_login_id'] ?? '');

$pageTitle = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-wrapper">

    <!-- Left Brand Showcase Banner -->
    <div class="auth-banner">
      <div class="banner-ambient-glow"></div>

      <div class="banner-header">
        <a href="<?= BASE_URL ?>/" class="banner-logo-link">
          <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink Logo" style="height: 48px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));">
        </a>
      </div>

      <div class="banner-content">
        <div class="banner-tag">
          <span class="dot"></span> Fresh Seasonal Marketplaces
        </div>
        <h1 class="banner-title">
          Welcome Back to Your <span class="gradient-text">Local Harvest</span> Hub.
        </h1>
        <p class="banner-desc">
          Sign in to manage your stall reservations, browse fresh weekly inventory, or track your customer pickup orders in real-time.
        </p>

        <div class="banner-features">
          <div class="feature-item">
            <div class="feature-icon-box">📦</div>
            <div>
              <div class="feature-title">Live Pickup Slot Tracking</div>
              <div class="feature-subtitle">Check stall pickup readiness and time windows instantly</div>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon-box">🥦</div>
            <div>
              <div class="feature-title">Weekly Fresh Restock Alerts</div>
              <div class="feature-subtitle">Get notified as soon as local organic farmers post inventory</div>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon-box">🛡</div>
            <div>
              <div class="feature-title">Verified Producers Only</div>
              <div class="feature-subtitle">Every farmer is vetted for community standards</div>
            </div>
          </div>
        </div>
      </div>

      <div class="banner-footer">
        <div class="banner-stats">
          <div class="stat-item">
            <span class="stat-number">100%</span>
            <span class="stat-label">Local Sourced</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">24h</span>
            <span class="stat-label">Farm to Basket</span>
          </div>
        </div>
        <div>
          <span>MarketLink Platform v2.4</span>
        </div>
      </div>
    </div>

    <!-- Right Login Form Container -->
    <div class="auth-form-container">
      <div class="auth-card">

        <div class="auth-header">
          <span class="auth-badge">Account Access</span>
          <h2 class="auth-title">Sign in to MarketLink</h2>
          <p class="auth-subtitle">
            Don't have an account yet? <a href="<?= BASE_URL ?>/register.php">Create one for free</a>
          </p>
        </div>

        <?php if (!empty($registeredUser)): ?>
          <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem;">
            <span style="font-size: 1.5rem; line-height: 1;">🎉</span>
            <div>
              <div style="font-weight: 700; color: #065f46; font-size: 0.9375rem; margin-bottom: 0.15rem;">Registration Successful!</div>
              <div style="font-size: 0.8125rem; color: #047857; line-height: 1.45;">Your account has been created. Please enter your password to sign in and access your dashboard.</div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($passwordReset): ?>
          <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="font-weight: 700; color: #065f46; font-size: 0.9375rem; margin-bottom: 0.15rem;">Password updated successfully</div>
            <div style="font-size: 0.8125rem; color: #047857; line-height: 1.45;">You can now sign in using your new password.</div>
          </div>
        <?php endif; ?>

        <form id="loginForm" action="<?= BASE_URL ?>/api/login_process.php" method="POST" novalidate>

          <!-- Username or Email -->
          <div class="form-group full-width">
            <label class="form-label" for="login_id">
              <span>Username or Email Address <span class="required">*</span></span>
            </label>
            <div class="input-container">
              <span class="input-icon-left">👤</span>
              <input type="text" id="login_id" name="login_id" class="form-input" 
                     placeholder="Enter your username or email" 
                     value="<?= htmlspecialchars($rememberedUsername) ?>" 
                     autocomplete="username" required>
              <span class="input-feedback-icon"></span>
            </div>
            <div class="validation-hint hint-neutral">Enter the username or email registered to your account</div>
          </div>

          <!-- Password -->
          <div class="form-group full-width">
            <label class="form-label" for="password">
              <span>Password <span class="required">*</span></span>
            </label>
            <div class="input-container">
              <span class="input-icon-left">🔒</span>
              <input type="password" id="password" name="password" class="form-input" 
                     placeholder="Enter your account password" 
                     autocomplete="current-password" required>
              <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password visibility">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
            <div class="validation-hint hint-neutral">Enter your secure password</div>
          </div>

          <!-- Remember me & help -->
          <div class="form-options-row">
            <label class="remember-label">
              <input type="checkbox" name="remember" value="1" <?= !empty($rememberedUsername) ? 'checked' : '' ?>>
              <span>Remember me</span>
            </label>
            <a href="<?= BASE_URL ?>/forgot_password.php" class="forgot-link">
              Forgot password?
            </a>
          </div>

          <!-- Submit Button with Loading Indicator -->
          <button type="submit" class="btn-primary" id="loginSubmitBtn">
            <span class="spinner"></span>
            <span class="btn-text">Sign In to Account</span>
            <span style="font-size: 1.1rem;">→</span>
          </button>

          <!-- Quick Demo Hint for Admin & User -->
          <div style="margin-top: 1.75rem; padding: 0.875rem; background: var(--slate-50); border: 1px dashed var(--slate-300); border-radius: var(--radius-md); font-size: 0.75rem; color: var(--slate-600); text-align: center;">
            🌾 <strong>New to the platform?</strong> Register as a <strong>Customer</strong> to browse produce or as a <strong>Farmer</strong> to manage market stalls!
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
