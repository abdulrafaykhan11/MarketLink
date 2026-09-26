<?php
/**
 * MarketLink - Shared Site Footer
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db.php';
}
?>
<!-- ==========================================================================
     LUXURY FOOTER
     ========================================================================== -->
<footer class="site-footer section-reveal">
  <div class="footer-container section-reveal-container">

    <div class="footer-top-grid">
      <!-- Brand Column -->
      <div class="footer-brand-col">
        <a href="<?= BASE_URL ?>/" class="nav-brand">
          <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="nav-logo-img">
        </a>
        <p class="footer-desc">
          MarketLink is Pakistan's premier digital community farmers market platform. Empowering local agricultural growers with zero-commission stall pre-orders and providing families with transparent, 24-hour dawn fresh harvest.
        </p>
        <div>
          <button type="button" class="theme-toggle-btn" aria-label="Toggle theme in footer">
            <span class="theme-toggle-icon">🌙</span>
            <span class="theme-toggle-text">Dark Mode</span>
          </button>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <div class="footer-col-title">Navigation</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/#how-it-works">The 3-Step Journey</a></li>
          <li><a href="<?= BASE_URL ?>/#farm-contrast">Why Direct From Soil?</a></li>
          <li><a href="<?= BASE_URL ?>/#local-stalls">Active Stalls</a></li>
          <li><a href="<?= BASE_URL ?>/about.php">About Us</a></li>
          <li><a href="<?= BASE_URL ?>/contact.php">Contact Us</a></li>
        </ul>
      </div>

      <!-- Portal Access -->
      <div>
        <div class="footer-col-title">Account Portals</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/login.php">Sign In to Account</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Customer Registration</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Farmer Stall Application</a></li>
          <li><a href="<?= BASE_URL ?>/admin/dashboard.php">Administration</a></li>
        </ul>
      </div>

      <!-- Weekly Harvest Newsletter -->
      <div>
        <div class="footer-col-title">Fresh Harvest Alert</div>
        <p class="footer-desc" style="margin-bottom: 0.5rem;">
          Get every Thursday's dawn harvest list and weekend stall pickup schedules straight to your inbox.
        </p>
        <form id="newsletterForm" class="newsletter-form">
          <input type="email" id="newsletterEmail" class="newsletter-input" placeholder="Enter your email address" required>
          <button type="submit" class="newsletter-btn">Subscribe</button>
        </form>
      </div>
    </div>

    <!-- Bottom Copyright & Credits -->
    <div class="footer-bottom">
      <div>
        &copy; <?= date('Y') ?> MarketLink Direct Agriculture Systems. All Rights Reserved.
      </div>
      <div>
        Built with clean architecture &bull; Handcrafted for Local Farm Communities
      </div>
    </div>

  </div>
</footer>
