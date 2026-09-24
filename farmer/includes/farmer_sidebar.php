<?php
/**
 * MarketLink - Farmer / Seller Dashboard Portal Sidebar
 * Clean Silicon Valley Navigation with Lucide Premium Icons
 */
?>
<aside class="farmer-sidebar" id="farmerSidebar">
  <!-- Brand Area -->
  <div class="farmer-sidebar-brand">
    <a href="<?= BASE_URL ?>/farmer/dashboard.php" style="display: flex; align-items: center; text-decoration: none;">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="farmer-brand-logo">
    </a>
    <span class="farmer-badge-role">
      <i data-lucide="sprout" style="width: 13px; height: 13px;"></i>
      <span>Producer</span>
    </span>
  </div>

  <!-- Navigation Links -->
  <nav class="farmer-sidebar-nav">
    <div class="farmer-nav-section-title">Operations</div>

    <a href="<?= BASE_URL ?>/farmer/dashboard.php" class="farmer-sidebar-link <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="layout-dashboard"></i>
        </span>
        <span>Dashboard</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/farmer/orders.php" class="farmer-sidebar-link <?= ($activePage === 'orders') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="shopping-bag"></i>
        </span>
        <span>Pre-Orders</span>
      </div>
      <?php if ($placedOrdersCount > 0): ?>
        <span class="farmer-sidebar-count-badge badge-urgent" title="<?= $placedOrdersCount ?> orders waiting for confirmation">
          <?= $placedOrdersCount ?> new
        </span>
      <?php elseif ($activeOrdersCount > 0): ?>
        <span class="farmer-sidebar-count-badge" title="<?= $activeOrdersCount ?> active pickup orders">
          <?= $activeOrdersCount ?>
        </span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/farmer/pickup-slots.php" class="farmer-sidebar-link <?= ($activePage === 'pickup-slots') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="calendar-clock"></i>
        </span>
        <span>Pickup Slots &amp; Cutoff</span>
      </div>
    </a>

    <div class="farmer-nav-section-title">Produce &amp; Inventory</div>

    <a href="<?= BASE_URL ?>/farmer/products.php" class="farmer-sidebar-link <?= ($activePage === 'products') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="package"></i>
        </span>
        <span>Produce Catalog</span>
      </div>
    </a>

    <div class="farmer-nav-section-title">Insights &amp; Feedback</div>

    <a href="<?= BASE_URL ?>/farmer/analytics.php" class="farmer-sidebar-link <?= ($activePage === 'analytics') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="trending-up"></i>
        </span>
        <span>Sales Analytics</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/farmer/reviews.php" class="farmer-sidebar-link <?= ($activePage === 'reviews') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="star"></i>
        </span>
        <span>Customer Reviews</span>
      </div>
    </a>

    <div class="farmer-nav-section-title">Stall Settings</div>

    <a href="<?= BASE_URL ?>/farmer/profile.php" class="farmer-sidebar-link <?= ($activePage === 'profile') ? 'active' : '' ?>">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="store"></i>
        </span>
        <span>Stall Profile &amp; Map</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/index.php" class="farmer-sidebar-link" target="_blank" rel="noopener">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon">
          <i data-lucide="globe"></i>
        </span>
        <span>Marketplace Home</span>
      </div>
      <i data-lucide="external-link" style="width: 14px; height: 14px; color: var(--slate-400);"></i>
    </a>

    <a href="<?= BASE_URL ?>/logout.php" class="farmer-sidebar-link" style="color: #f87171;">
      <div class="farmer-sidebar-link-content">
        <span class="farmer-sidebar-icon" style="color: #f87171;">
          <i data-lucide="log-out"></i>
        </span>
        <span>Sign Out</span>
      </div>
    </a>
  </nav>

  <!-- Stall Card Snapshot at bottom -->
  <div class="farmer-sidebar-stall-card">
    <div class="farmer-stall-card-header">
      <span style="font-size: 0.6875rem; text-transform: uppercase; font-weight: 700; color: var(--slate-400);">Primary Stall</span>
      <span class="farmer-stall-status-dot <?= ($approvalStatus === 'approved') ? '' : 'pending' ?>" title="Status: <?= ucfirst($approvalStatus) ?>"></span>
    </div>
    <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
      <?= htmlspecialchars($stallName) ?>
    </div>
    <div style="font-size: 0.75rem; color: var(--slate-400); display: flex; align-items: center; gap: 0.35rem;">
      <i data-lucide="map-pin" style="width: 12px; height: 12px; color: var(--primary-500);"></i>
      <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
        <?= !empty($assignedStalls) ? htmlspecialchars($assignedStalls[0]['market_name']) : 'Central Market' ?>
      </span>
    </div>
  </div>
</aside>
