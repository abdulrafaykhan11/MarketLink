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
        <a href="<?= BASE_URL ?>/" class="footer-brand-logo" aria-label="MarketLink home">
          <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="nav-logo-img">
        </a>
        <p class="footer-desc">
          MarketLink is Pakistan's premier digital community farmers market platform. Empowering local agricultural growers with zero-commission stall pre-orders and providing families with transparent, 24-hour dawn fresh harvest.
        </p>
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
        <form id="newsletterForm" class="newsletter-form" action="<?= BASE_URL ?>/api/newsletter_subscribe.php" method="post">
          <label class="sr-only" for="newsletterEmail">Email address</label>
          <input type="email" id="newsletterEmail" name="email" class="newsletter-input" placeholder="Enter your email address" autocomplete="email" required>
          <button type="submit" class="newsletter-btn">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            <span>Subscribe</span>
          </button>
        </form>
        <p class="newsletter-status" id="newsletterStatus" role="status" aria-live="polite"></p>
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
