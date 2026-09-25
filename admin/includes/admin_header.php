<?php
/**
 * MarketLink - Super Admin Dashboard Header
 * Enterprise Silicon Valley Architecture & Executive Theme
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['admin']);

$pdo = getDBConnection();
$adminId = (int)($_SESSION['user_id'] ?? 0);
$adminUsername = $_SESSION['username'] ?? 'Administrator';

// 1. Fetch live metrics for admin sidebar badges & alerts
try {
    $pendingFarmersCount = (int)$pdo->query("SELECT COUNT(*) FROM farmer_profiles WHERE approval_status = 'pending'")->fetchColumn();
    $activeOrdersCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('placed', 'accepted', 'ready_for_pickup')")->fetchColumn();
    
    // Unmoderated reviews or flagged content
    $pendingReviewsCount = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM farmer_reviews WHERE is_moderated = 0) + (SELECT COUNT(*) FROM product_reviews WHERE is_moderated = 0)")->fetchColumn();
    
    // Unread contact form inquiries
    $unreadInquiriesCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
} catch (Exception $e) {
    $pendingFarmersCount = 0;
    $activeOrdersCount = 0;
    $pendingReviewsCount = 0;
    $unreadInquiriesCount = 0;
}

$pageTitle = $pageTitle ?? 'Admin Console';
$activeNav = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?> Super Admin</title>

  <!-- Google Fonts: Plus Jakarta Sans & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Chart.js for High-End Executive Analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <!-- Leaflet CSS for Map Integration (SRS requirement) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <!-- Theme Engine -->
  <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>

  <!-- Core & Admin Stylesheets -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">
</head>
<body class="admin-body">

<div class="admin-layout">

  <!-- Sidebar Navigation -->
  <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

  <!-- Main Content Shell -->
  <div class="admin-main">

    <!-- Topbar Header -->
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button type="button" class="admin-mobile-toggle" id="adminSidebarToggle" aria-label="Toggle navigation drawer">
          <i data-lucide="menu"></i>
        </button>

        <div class="admin-page-breadcrumb">
          <span>Admin</span>
          <span>/</span>
          <span class="current"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
      </div>

      <div class="admin-topbar-right">
        <!-- Quick Link to Public Site -->
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-topbar-btn" title="View Public Marketplace">
          <i data-lucide="external-link"></i>
          <span>Live Site</span>
        </a>

        <!-- Theme Toggle -->
        <button type="button" class="admin-topbar-btn theme-toggle-btn" aria-label="Toggle Theme" title="Toggle Theme">
          <i data-lucide="moon" class="theme-icon-dark"></i>
          <span class="theme-toggle-label">Theme</span>
        </button>

        <!-- Admin Profile Pill -->
        <div style="display:flex; align-items:center; gap:0.75rem; padding-left:0.5rem; border-left:1px solid var(--admin-card-border);">
          <div class="admin-avatar">
            <?= strtoupper(substr($adminUsername, 0, 1)) ?>
          </div>
          <div style="display:none; @media(min-width:640px){display:block;}">
            <div style="font-size:0.85rem; font-weight:700; color:var(--admin-text-main); line-height:1.2;">
              <?= htmlspecialchars($adminUsername) ?>
            </div>
            <div style="font-size:0.7rem; color:var(--admin-emerald); font-weight:600;">Super Administrator</div>
          </div>
          <a href="<?= BASE_URL ?>/logout.php" class="admin-topbar-btn" style="color:var(--admin-rose); border-color:rgba(239,68,68,0.25);" title="Sign Out">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          </a>
        </div>
      </div>
    </header>

    <!-- Admin Content Container -->
    <main class="admin-content">
