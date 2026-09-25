<?php
/**
 * MarketLink - Farmer / Seller Dashboard Portal Header
 * Enterprise Silicon Valley Architecture & Modern Emerald Theme
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['farmer', 'admin']);

$pdo = getDBConnection();
$currentUserId = (int)$_SESSION['user_id'];

// 1. Fetch farmer user & profile record
$stmt = $pdo->prepare("SELECT u.user_id, u.username, u.email, u.phone_number, u.profile_image,
                              fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, 
                              fp.business_email, fp.description, fp.address, fp.latitude, fp.longitude,
                              fp.order_cutoff_time, fp.approval_status, fp.created_at as member_since
                       FROM users u 
                       LEFT JOIN farmer_profiles fp ON u.user_id = fp.farmer_id 
                       WHERE u.user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $currentUserId]);
$farmer = $stmt->fetch();

// If farmer profile is missing (e.g. admin browsing or freshly registered), ensure safe defaults
if (!$farmer || empty($farmer['farmer_id'])) {
    $farmerId = $currentUserId;
    $stallName = $farmer['username'] ?? 'My Organic Farm';
    $displayName = $farmer['username'] ?? 'Farmer';
    $approvalStatus = 'approved';
    $cutoffTime = '18:00:00';
} else {
    $farmerId = (int)$farmer['farmer_id'];
    $stallName = $farmer['stall_name'] ?: 'My Organic Farm';
    $displayName = $farmer['contact_person'] ?: $farmer['stall_name'] ?: $farmer['username'];
    $approvalStatus = $farmer['approval_status'] ?? 'pending';
    $cutoffTime = $farmer['order_cutoff_time'] ?: '18:00:00';
}

// 2. Fetch pending pre-orders requiring acceptance
$pendingOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid AND order_status = 'placed'");
$pendingOrdersStmt->execute([':fid' => $farmerId]);
$placedOrdersCount = (int)$pendingOrdersStmt->fetchColumn();

// Total active orders (placed, accepted, ready_for_pickup)
$activeOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid AND order_status IN ('placed', 'accepted', 'ready_for_pickup')");
$activeOrdersStmt->execute([':fid' => $farmerId]);
$activeOrdersCount = (int)$activeOrdersStmt->fetchColumn();

// 3. Fetch unread notifications
$notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
$notifStmt->execute([':uid' => $currentUserId]);
$unreadNotifsCount = (int)$notifStmt->fetchColumn();

// 4. Fetch assigned market stalls
$stallStmt = $pdo->prepare("SELECT fms.*, m.market_name, m.city, m.operating_days as market_days 
                            FROM farmer_market_stalls fms 
                            JOIN markets m ON fms.market_id = m.market_id 
                            WHERE fms.farmer_id = :fid AND fms.status = 'active'");
$stallStmt->execute([':fid' => $farmerId]);
$assignedStalls = $stallStmt->fetchAll();

$pageTitle = $pageTitle ?? 'Farmer Console';
$activePage = $activePage ?? 'dashboard';

// Calculate cutoff remaining display
$cutoffFormatted = date('g:i A', strtotime($cutoffTime));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?> Producer Console</title>

  <!-- Typography: Plus Jakarta Sans (Headings) & Inter (Body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Core Theme Engine (Immediate Execution) -->
  <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>

  <!-- Premium Vector Icons: Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Interactive Data Visualization: Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Interactive OpenStreetMap: Leaflet CSS & JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <!-- Core Design System & Farmer Portal Stylesheets -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/farmer.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">
</head>
<body>

<div class="farmer-layout">

  <!-- Sidebar Component -->
  <?php require_once __DIR__ . '/farmer_sidebar.php'; ?>

  <!-- Mobile Drawer Backdrop Overlay -->
  <div class="farmer-sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Main Portal Shell -->
  <div class="farmer-main">

    <!-- Sticky Topbar Navigation -->
    <header class="farmer-topbar">
      <div class="farmer-topbar-left">
        <button type="button" class="farmer-mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation drawer">
          <i data-lucide="menu"></i>
        </button>

        <!-- Search Bar -->
        <form action="<?= BASE_URL ?>/farmer/orders.php" method="GET" class="farmer-topbar-search">
          <span class="farmer-topbar-search-icon">
            <i data-lucide="search"></i>
          </span>
          <input type="text" name="search" class="farmer-topbar-search-input" placeholder="Search pre-orders, produce, customer phone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
      </div>

      <div class="farmer-topbar-right">
        <!-- Live Cutoff Countdown Ticker -->
        <div class="farmer-cutoff-ticker" title="Orders for tomorrow close at this cutoff window">
          <i data-lucide="clock" style="width: 15px; height: 15px;"></i>
          <span>Cut-off: <strong><?= $cutoffFormatted ?></strong></span>
        </div>

        <!-- Quick Add Harvest Button -->
        <a href="<?= BASE_URL ?>/farmer/products.php?action=new" class="farmer-btn-primary" style="padding: 0.45rem 0.95rem; font-size: 0.8125rem;">
          <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
          <span>Add Produce</span>
        </a>

        <!-- Dark / Light Theme Mode Switcher -->
        <button type="button" class="farmer-topbar-btn theme-toggle-btn" aria-label="Toggle theme mode" title="Toggle dark/light mode">
          <span class="theme-toggle-icon">🌙</span>
        </button>

        <!-- Notification Bell -->
        <a href="<?= BASE_URL ?>/farmer/dashboard.php#announcements" class="farmer-topbar-btn" title="Platform Alerts & Notifications">
          <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
          <?php if ($unreadNotifsCount > 0): ?>
            <span class="farmer-topbar-badge"><?= $unreadNotifsCount ?></span>
          <?php endif; ?>
        </a>

        <!-- User Stall Identity Menu -->
        <div class="farmer-topbar-user">
          <div class="farmer-user-avatar">
            <?php if (!empty($farmer['profile_image'])): ?>
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($farmer['profile_image']) ?>" alt="<?= htmlspecialchars($displayName) ?>">
            <?php else: ?>
              <?= strtoupper(substr($displayName, 0, 1)) ?>
            <?php endif; ?>
          </div>
          <div class="farmer-user-info">
            <span class="farmer-user-name"><?= htmlspecialchars($displayName) ?></span>
            <span class="farmer-user-stall"><?= htmlspecialchars($stallName) ?></span>
          </div>
        </div>
      </div>
    </header>

    <?php if (empty($farmer['profile_image'])): ?>
    <!-- Compulsory Profile Photo Banner — shown until farmer uploads a photo -->
    <div style="background: linear-gradient(90deg, rgba(239,68,68,0.12), rgba(251,191,36,0.08)); border-bottom: 1px solid rgba(239,68,68,0.3); padding: 0.65rem 1.5rem; display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 0.55rem; flex: 1; min-width: 0;">
        <i data-lucide="camera" style="width: 16px; height: 16px; color: #f87171; flex-shrink: 0;"></i>
        <span style="font-size: 0.8125rem; color: var(--text-primary); font-weight: 500;">
          <strong style="color: #f87171;">Profile photo required.</strong>
          Customers cannot verify your identity without a photo. Please upload your farmer profile picture.
        </span>
      </div>
      <a href="<?= BASE_URL ?>/farmer/profile.php" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #f87171; font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.85rem; border-radius: var(--radius-md); text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
        <i data-lucide="upload-cloud" style="width: 13px; height: 13px;"></i>
        Upload Photo Now
      </a>
    </div>
    <?php endif; ?>
