<?php
/**
 * MarketLink - Super Admin Sidebar Navigation
 */
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-header">
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink">
      <span class="admin-badge-exec">Admin</span>
    </a>
    <button type="button" class="admin-modal-close" id="adminSidebarClose" style="display:none;" aria-label="Close Sidebar">
      <i data-lucide="x"></i>
    </button>
  </div>

  <nav class="admin-sidebar-nav">
    <div class="admin-nav-group-label">Overview</div>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-nav-link <?= ($activeNav === 'dashboard') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
      </div>
    </a>

    <div class="admin-nav-group-label">User & Vendor Management</div>
    <a href="<?= BASE_URL ?>/admin/farmers.php" class="admin-nav-link <?= ($activeNav === 'farmers') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="tractor"></i>
        <span>Farmer Verification</span>
      </div>
      <?php if (!empty($pendingFarmersCount) && $pendingFarmersCount > 0): ?>
        <span class="admin-badge-count urgent" title="<?= $pendingFarmersCount ?> Farmer approvals pending!"><?= $pendingFarmersCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/admin/users.php" class="admin-nav-link <?= ($activeNav === 'users') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="users"></i>
        <span>Users & Customers</span>
      </div>
    </a>

    <div class="admin-nav-group-label">Marketplace & Logistics</div>
    <a href="<?= BASE_URL ?>/admin/markets.php" class="admin-nav-link <?= ($activeNav === 'markets') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="map-pin"></i>
        <span>Markets & Stalls</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/admin/products.php" class="admin-nav-link <?= ($activeNav === 'products') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="package"></i>
        <span>Products Catalog</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/admin/orders.php" class="admin-nav-link <?= ($activeNav === 'orders') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="shopping-bag"></i>
        <span>Pre-Orders Central</span>
      </div>
      <?php if (!empty($activeOrdersCount) && $activeOrdersCount > 0): ?>
        <span class="admin-badge-count"><?= $activeOrdersCount ?></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-group-label">Content & System Ops</div>
    <a href="<?= BASE_URL ?>/admin/reviews.php" class="admin-nav-link <?= ($activeNav === 'reviews') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="star"></i>
        <span>Reviews & Moderation</span>
      </div>
      <?php if (!empty($pendingReviewsCount) && $pendingReviewsCount > 0): ?>
        <span class="admin-badge-count urgent"><?= $pendingReviewsCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/admin/inquiries.php" class="admin-nav-link <?= ($activeNav === 'inquiries') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="mail"></i>
        <span>Contact Inquiries</span>
      </div>
      <?php if (!empty($unreadInquiriesCount) && $unreadInquiriesCount > 0): ?>
        <span class="admin-badge-count urgent" title="<?= $unreadInquiriesCount ?> unread inquiries!"><?= $unreadInquiriesCount ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>/admin/announcements.php" class="admin-nav-link <?= ($activeNav === 'announcements') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="megaphone"></i>
        <span>Announcements</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/admin/categories.php" class="admin-nav-link <?= ($activeNav === 'categories') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="tags"></i>
        <span>Master Categories</span>
      </div>
    </a>

    <a href="<?= BASE_URL ?>/admin/reports.php" class="admin-nav-link <?= ($activeNav === 'reports') ? 'active' : '' ?>">
      <div class="admin-nav-link-inner">
        <i data-lucide="bar-chart-3"></i>
        <span>Reports & Analytics</span>
      </div>
    </a>
  </nav>

  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="admin-user-info">
        <div class="admin-avatar">
          <?= strtoupper(substr($adminUsername ?? 'A', 0, 1)) ?>
        </div>
        <div class="admin-user-details">
          <span class="admin-user-name"><?= htmlspecialchars($adminUsername ?? 'Admin') ?></span>
          <span class="admin-user-role">Super Admin</span>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" title="Sign Out" style="color:var(--admin-rose, #ef4444); display:flex; align-items:center; justify-content:center; text-decoration:none; padding:4px; border-radius:6px; transition:background 0.2s ease;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      </a>
    </div>
  </div>
</aside>
