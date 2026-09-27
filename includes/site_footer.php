<?php
/**
 * MarketLink - Shared Site Footer
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db.php';
}
?>
<footer class="site-footer section-reveal">
  <div class="footer-container section-reveal-container">

    <div class="footer-top-grid">
      <!-- Brand Column -->
      <div class="footer-brand-col">
        <a href="<?= BASE_URL ?>/" class="footer-brand-logo" aria-label="MarketLink home">
          <img src="<?= BASE_URL ?>/assets/images/logo-dark.svg" alt="" class="nav-logo-img logo-dark">
          <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="" class="nav-logo-img logo-light">
        </a>
        <p class="footer-desc">
          MarketLink is Pakistan's premier digital community farmers market platform. Empowering local agricultural growers with zero-commission stall pre-orders and providing families with transparent, 24-hour dawn fresh harvest.
        </p>
      </div>

      <!-- Explore -->
      <div>
        <div class="footer-col-title">Explore</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/#how-it-works">How It Works</a></li>
          <li><a href="<?= BASE_URL ?>/#local-stalls">Browse Stalls</a></li>
          <li><a href="<?= BASE_URL ?>/#testimonials">Customer Reviews</a></li>
          <li><a href="<?= BASE_URL ?>/about.php">About Us</a></li>
          <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        </ul>
      </div>

      <!-- Portal Access -->
      <div>
        <div class="footer-col-title">Account</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/login.php">Sign In</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Create an Account</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Become a Farmer</a></li>
        </ul>
      </div>
    </div>

    <!-- Bottom Copyright & Credits -->
    <div class="footer-bottom">
      <div>
        &copy; <?= date('Y') ?> MarketLink. All rights reserved.
      </div>
      <div>
        Fresh food, directly from local growers.
      </div>
    </div>

  </div>
</footer>
