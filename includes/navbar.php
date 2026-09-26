<?php
/**
 * MarketLink - Universal Glassmorphic Navbar
 * Supports Dark/Light theme switching, navigation anchors, and authentication states
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db.php';
}
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../includes/auth_guard.php';
}

$user = currentUser();
?>
<header class="site-header" id="siteHeader">
  <div class="nav-container">

    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/" class="nav-brand" aria-label="MarketLink Home">
      <img src="<?= BASE_URL ?>/assets/images/logo-dark.svg" alt="MarketLink" class="nav-logo-img logo-dark">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="nav-logo-img logo-light">
    </a>

    <!-- Navigation Links -->
    <nav class="nav-menu" id="navMenu">
      <ul class="nav-list">
        <li><a href="<?= BASE_URL ?>/#how-it-works" class="nav-link">The Journey</a></li>
        <li><a href="<?= BASE_URL ?>/#local-stalls" class="nav-link">Local Stalls</a></li>
        <li><a href="<?= BASE_URL ?>/#testimonials" class="nav-link">Reviews</a></li>
        <li><a href="<?= BASE_URL ?>/#farm-contrast" class="nav-link">Why Direct?</a></li>
        <li><a href="<?= BASE_URL ?>/about.php" class="nav-link">About Us</a></li>
        <li><a href="<?= BASE_URL ?>/contact.php" class="nav-link">Contact</a></li>
      </ul>
    </nav>

    <!-- Nav Actions: Theme Switcher & Auth Buttons -->
    <div class="nav-actions">
      <!-- Theme Toggle Switch (Dark & Light Mode) -->
      <button type="button" class="theme-toggle-btn" aria-label="Toggle Dark and Light Mode" title="Toggle theme">
        <span class="theme-toggle-icon">🌙</span>
        <span class="theme-toggle-text">Dark</span>
      </button>

      <?php if ($user): ?>
        <!-- Logged-in User Profile Dropdown / Badge -->
        <div class="nav-user-badge">
          <div class="nav-user-info">
            <span class="nav-user-name"><?= htmlspecialchars($user['name']) ?></span>
            <span class="nav-user-role role-<?= htmlspecialchars($user['role']) ?>"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
          </div>
          <a href="<?= BASE_URL ?>/<?= htmlspecialchars($user['role']) ?>/dashboard.php" class="btn-dashboard-nav">
            Portal ➔
          </a>
          <a href="<?= BASE_URL ?>/logout.php" class="btn-logout-nav" title="Sign Out" style="display:inline-flex; align-items:center; justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color:var(--rose-500, #ef4444);"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          </a>
        </div>
      <?php else: ?>
        <!-- Guest Auth Links -->
        <a href="<?= BASE_URL ?>/login.php" class="nav-link-login">Sign In</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn-join-nav">
          <span>🌱 Join MarketLink</span>
        </a>
      <?php endif; ?>

      <!-- Mobile Hamburger Menu Button -->
      <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle mobile menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

  </div>
</header>
