<?php
/**
 * MarketLink - Customer Dashboard Portal Sidebar
 */
?>
<aside class="customer-sidebar">
  <!-- Brand Area -->
  <div class="sidebar-brand">
    <a href="<?= BASE_URL ?>/index.php" style="display:flex; align-items:center; gap:0.6rem; text-decoration:none;">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="sidebar-brand-img">
    </a>
    <span class="sidebar-badge">Customer</span>
  </div>

  <!-- Navigation Links -->
  <nav class="sidebar-nav">
    <div class="sidebar-nav-title">Marketplace</div>

    <a href="<?= BASE_URL ?>/customer/dashboard.php" class="sidebar-link <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">📊</span>
        <span>Dashboard</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/customer/products.php" class="sidebar-link <?= ($activePage === 'products') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">🥬</span>
        <span>Browse Harvest</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/customer/markets.php" class="sidebar-link <?= ($activePage === 'markets') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">📍</span>
        <span>Markets & Map</span>
      </div>
    </a>

    <div class="sidebar-nav-title">Orders & Cart</div>

    <a href="<?= BASE_URL ?>/customer/cart.php" class="sidebar-link <?= ($activePage === 'cart') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">🛒</span>
        <span>Pre-Order Basket</span>
      </div>
      <span class="sidebar-count-badge cart-count-badge" style="<?= ($cartItemCount > 0) ? '' : 'display:none;' ?>">
        <?= $cartItemCount ?>
      </span>
    </a>

    <a href="<?= BASE_URL ?>/customer/orders.php" class="sidebar-link <?= ($activePage === 'orders') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">📦</span>
        <span>My Orders & Tracking</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/customer/chat.php" class="sidebar-link <?= ($activePage === 'chat') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">💬</span>
        <span>Seller Order Chat</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/customer/favorites.php" class="sidebar-link <?= ($activePage === 'favorites') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">❤️</span>
        <span>Saved Favorites</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/customer/reviews.php" class="sidebar-link <?= ($activePage === 'reviews') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">⭐</span>
        <span>Reviews & Ratings</span>
      </div>
    </a>

    <div class="sidebar-nav-title">Preferences</div>

    <a href="<?= BASE_URL ?>/customer/notifications.php" class="sidebar-link <?= ($activePage === 'notifications') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">🔔</span>
        <span>Notifications</span>
      </div>
      <?php if ($unreadNotifsCount > 0): ?>
        <span class="sidebar-count-badge" style="background:#ef4444;"><?= $unreadNotifsCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/customer/profile.php" class="sidebar-link <?= ($activePage === 'profile') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">👤</span>
        <span>Profile & Pickup</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/index.php" class="sidebar-link" target="_blank">
      <div class="sidebar-link-content">
        <span class="sidebar-link-icon">🌐</span>
        <span>Visit Main Website</span>
      </div>
      <span style="font-size:0.75rem; color:var(--text-muted);">↗</span>
    </a>
  </nav>

  <!-- Sidebar Footer / Account Details -->
  <div class="sidebar-footer">
    <div class="sidebar-user-card">
      <div class="sidebar-user-avatar">
        <?= strtoupper(substr($displayName, 0, 1)) ?>
      </div>
      <div class="sidebar-user-details">
        <div class="sidebar-user-name" title="<?= htmlspecialchars($displayName) ?>"><?= htmlspecialchars($displayName) ?></div>
        <div class="sidebar-user-role"><?= htmlspecialchars($customerUser['email']) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" title="Sign Out" style="color:var(--text-muted); display:flex; align-items:center; justify-content:center; text-decoration:none; padding:4px; border-radius:6px; transition:color 0.2s ease;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      </a>
    </div>
  </div>
</aside>
