<?php
/**
 * MarketLink - User Registration Page
 * Features role selection (Customer vs Farmer), live client-side validations,
 * AJAX availability checking, and compulsory Terms & Agreement modal.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Redirect if already logged in
redirectIfLoggedIn();

$pageTitle = 'Create Account';
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
          <span class="dot"></span> Direct Farm-To-Fork Network
        </div>
        <h1 class="banner-title">
          Connecting <span class="gradient-text">Local Farms</span> Directly to You.
        </h1>
        <p class="banner-desc">
          Skip supermarket markups and long supply chains. Pre-order fresh local harvests, reserve convenient market pickup slots, and empower local agricultural growers.
        </p>

        <div class="banner-features">
          <div class="feature-item">
            <div class="feature-icon-box">🌱</div>
            <div>
              <div class="feature-title">Fresh, Seasonal Produce</div>
              <div class="feature-subtitle">Harvested within 24 hours of market pickup</div>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon-box">📍</div>
            <div>
              <div class="feature-title">Convenient Market Pickup Slots</div>
              <div class="feature-subtitle">Choose exact dates and stall locations without long queues</div>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon-box">🤝</div>
            <div>
              <div class="feature-title">Zero Middlemen</div>
              <div class="feature-subtitle">100% of stall sales go straight to supporting local growers</div>
            </div>
          </div>
        </div>
      </div>

      <div class="banner-footer">
        <div class="banner-stats">
          <div class="stat-item">
            <span class="stat-number">500+</span>
            <span class="stat-label">Active Farmers</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">12,000+</span>
            <span class="stat-label">Happy Families</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">4.9 ★</span>
            <span class="stat-label">Produce Rating</span>
          </div>
        </div>
        <div>
          <span>© <?= date('Y') ?> MarketLink System</span>
        </div>
      </div>
    </div>

    <!-- Right Registration Form Container -->
    <div class="auth-form-container">
      <div class="auth-card">

        <div class="auth-header">
          <span class="auth-badge">Account Registration</span>
          <h2 class="auth-title">Join MarketLink Today</h2>
          <p class="auth-subtitle">
            Already have an account? <a href="<?= BASE_URL ?>/login.php">Sign In here</a>
          </p>
        </div>

        <form id="registrationForm" action="<?= BASE_URL ?>/api/register_process.php" method="POST" novalidate>

          <!-- 1. Interactive Role Selector (Customer vs Farmer) -->
          <div class="role-selector" role="radiogroup" aria-label="Select Account Type">
            <label class="role-option active" id="role-customer-label">
              <input type="radio" name="role" value="customer" checked>
              <span class="role-icon">🛒</span>
              <span>I'm a Customer</span>
            </label>
            <label class="role-option" id="role-farmer-label">
              <input type="radio" name="role" value="farmer">
              <span class="role-icon">🚜</span>
              <span>I'm a Farmer / Stall</span>
            </label>
          </div>

          <!-- 2. Dynamic Role Fields: Customer vs Farmer -->
          <div id="customer-specific-fields" class="conditional-fields">
            <div class="form-group full-width">
              <label class="form-label" for="full_name">
                <span>Full Name <span class="required">*</span></span>
              </label>
              <div class="input-container">
                <span class="input-icon-left">👤</span>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="e.g. Ali Khan" autocomplete="name">
                <span class="input-feedback-icon"></span>
              </div>
              <div class="validation-hint hint-neutral">Enter your first and last name</div>
            </div>
          </div>

          <div id="farmer-specific-fields" class="conditional-fields" style="display: none;">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label" for="stall_name">
                  <span>Farm / Stall Name <span class="required">*</span></span>
                </label>
                <div class="input-container">
                  <span class="input-icon-left">🏡</span>
                  <input type="text" id="stall_name" name="stall_name" class="form-input" placeholder="e.g. Green Valley Farm" autocomplete="organization">
                  <span class="input-feedback-icon"></span>
                </div>
                <div class="validation-hint hint-neutral">Public stall display name</div>
              </div>

              <div class="form-group">
                <label class="form-label" for="contact_person">
                  <span>Contact Person <span class="required">*</span></span>
                </label>
                <div class="input-container">
                  <span class="input-icon-left">👤</span>
                  <input type="text" id="contact_person" name="contact_person" class="form-input" placeholder="e.g. Muhammad Asif" autocomplete="name">
                  <span class="input-feedback-icon"></span>
                </div>
                <div class="validation-hint hint-neutral">Owner or manager name</div>
              </div>
            </div>

            <div class="form-group full-width">
              <label class="form-label" for="business_address">
                <span>Farm / Pickup Address <span class="required">*</span></span>
              </label>
              <div class="input-container">
                <span class="input-icon-left">📍</span>
                <input type="text" id="business_address" name="business_address" class="form-input" placeholder="e.g. Plot 14, Agricultural Belt, Farm Market Road">
                <span class="input-feedback-icon"></span>
              </div>
              <div class="validation-hint hint-neutral">Physical farm or primary stall address</div>
            </div>
          </div>

          <!-- 3. Common Account Fields -->
          <div class="form-grid">
            <!-- Username with Live AJAX Check & Auto Suggestions -->
            <div class="form-group">
              <div class="form-label">
                <label for="username">Username <span class="required">*</span></label>
                <button type="button" id="btn-trigger-suggestions" class="btn-text-action" title="Generate available username suggestions">
                  ✨ Suggest
                </button>
              </div>
              <div class="input-container">
                <span class="input-icon-left">@</span>
                <input type="text" id="username" name="username" class="form-input" placeholder="e.g. alikhan99" autocomplete="username" maxlength="30" required>
                <span class="input-feedback-icon"></span>
              </div>
              <div class="validation-hint hint-neutral">Letters, numbers, and underscores (3-30 chars)</div>

              <!-- Suggested Usernames Chips Container -->
              <div id="username-suggestions-wrap" class="username-suggestions-container" style="display: none;">
                <div class="suggestions-label">💡 Suggested available usernames:</div>
                <div class="suggestions-chips" id="suggestions-chips-list"></div>
              </div>
            </div>

            <!-- Pakistani Mobile Phone Number -->
            <div class="form-group">
              <label class="form-label" for="phone_number">
                <span>Pakistani Mobile No.</span>
              </label>
              <div class="input-container">
                <span class="input-icon-left">📞</span>
                <input type="tel" id="phone_number" name="phone_number" class="form-input" placeholder="e.g. 03001234567" autocomplete="tel">
                <span class="input-feedback-icon"></span>
              </div>
              <div class="validation-hint hint-neutral">Pakistani format: 03001234567 or +923001234567</div>
            </div>
          </div>

          <!-- Email with Live RFC & AJAX Check -->
          <div class="form-group full-width">
            <label class="form-label" for="email">
              <span>Email Address <span class="required">*</span></span>
            </label>
            <div class="input-container">
              <span class="input-icon-left">✉</span>
              <input type="email" id="email" name="email" class="form-input" placeholder="e.g. ali.khan@example.com" autocomplete="email" required>
              <span class="input-feedback-icon"></span>
            </div>
            <div class="validation-hint hint-neutral">We will send confirmation and pickup slips here</div>
          </div>

          <!-- Password & Strength Meter -->
          <div class="form-group full-width">
            <label class="form-label" for="password">
              <span>Password <span class="required">*</span></span>
            </label>
            <div class="input-container">
              <span class="input-icon-left">🔒</span>
              <input type="password" id="password" name="password" class="form-input" placeholder="Create a strong password" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password visibility">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>

            <!-- Dynamic Password Strength Meter -->
            <div class="password-meter-wrap">
              <div class="meter-header">
                <span>Strength Assessment</span>
                <span class="meter-strength-label" id="strengthText">Too Weak</span>
              </div>
              <div class="meter-bars">
                <div class="meter-bar" id="meterBar1"></div>
                <div class="meter-bar" id="meterBar2"></div>
                <div class="meter-bar" id="meterBar3"></div>
                <div class="meter-bar" id="meterBar4"></div>
              </div>
              <ul class="meter-checklist">
                <li class="checklist-item" id="req-length">
                  <span class="icon">○</span> Minimum 8 characters
                </li>
                <li class="checklist-item" id="req-upper">
                  <span class="icon">○</span> At least 1 uppercase (A-Z)
                </li>
                <li class="checklist-item" id="req-lower">
                  <span class="icon">○</span> At least 1 lowercase (a-z)
                </li>
                <li class="checklist-item" id="req-number">
                  <span class="icon">○</span> At least 1 number (0-9)
                </li>
                <li class="checklist-item" id="req-special">
                  <span class="icon">○</span> At least 1 symbol (!@#$%)
                </li>
              </ul>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group full-width">
            <label class="form-label" for="confirm_password">
              <span>Confirm Password <span class="required">*</span></span>
            </label>
            <div class="input-container">
              <span class="input-icon-left">🛡</span>
              <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Re-enter password" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="confirm_password" aria-label="Toggle password visibility">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
            <div class="validation-hint hint-neutral">Re-type your password to confirm</div>
          </div>

          <!-- 4. Mandatory Terms & Agreement Checkbox with Interactive Modal Link -->
          <div class="terms-agreement-card">
            <label class="custom-checkbox-label">
              <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required>
              <div class="terms-text">
                I have read and agree to the 
                <a href="#terms" class="terms-link">Terms &amp; Conditions</a> and 
                <a href="#terms" class="terms-link">Platform Agreement</a>. 
                I understand this is required to create an account.
              </div>
            </label>
          </div>

          <!-- Submit Button with Loading Indicator -->
          <button type="submit" class="btn-primary" id="registerSubmitBtn">
            <span class="spinner"></span>
            <span class="btn-text">Create MarketLink Account</span>
            <span style="font-size: 1.1rem;">→</span>
          </button>

        </form>

      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
