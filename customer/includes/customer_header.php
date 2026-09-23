<?php
/**
 * MarketLink - Customer Dashboard Portal Header
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['customer', 'admin']);

$pdo = getDBConnection();
$currentUserId = $_SESSION['user_id'];

// Fetch customer user & profile record
$stmt = $pdo->prepare("SELECT u.user_id, u.username, u.email, u.phone_number, cp.full_name, cp.default_address 
                       FROM users u 
                       LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id 
                       WHERE u.user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $currentUserId]);
$customerUser = $stmt->fetch();

$displayName = !empty($customerUser['full_name']) ? $customerUser['full_name'] : $customerUser['username'];

// Unread notifications count
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
$unreadStmt->execute([':uid' => $currentUserId]);
$unreadNotifsCount = (int)$unreadStmt->fetchColumn();

// Active cart items count
$cartStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_items WHERE customer_id = :uid");
$cartStmt->execute([':uid' => $currentUserId]);
$cartItemCount = (int)$cartStmt->fetchColumn();

$pageTitle = $pageTitle ?? 'Customer Portal';
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?> Customer Portal</title>

  <!-- Google Fonts: Plus Jakarta Sans (Headings) & Inter (Body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Core & Theme Engine -->
  <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/home.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer.css">

  <!-- Leaflet CSS for Map integration -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">
</head>
<body>

<div class="customer-layout">

  <!-- Sidebar Component -->
  <?php require_once __DIR__ . '/customer_sidebar.php'; ?>

  <!-- Main Portal Content Shell -->
  <div class="customer-main">

    <!-- Sticky Topbar Navigation -->
    <header class="customer-topbar">
      <div class="topbar-left">
        <button type="button" class="topbar-mobile-toggle" id="sidebarToggleBtn" aria-label="Toggle navigation menu">
          ☰
        </button>
        <form action="<?= BASE_URL ?>/customer/products.php" method="GET" class="topbar-search-box">
          <span class="topbar-search-icon">🔍</span>
          <input type="text" name="search" class="topbar-search-input" placeholder="Search organic vegetables, fruits, honey, farm dairy..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
      </div>

      <div class="topbar-right">
        <!-- Theme Switcher -->
        <button type="button" class="theme-toggle-btn" aria-label="Toggle theme mode" title="Toggle theme">
          <span class="theme-toggle-icon">🌙</span>
        </button>

        <!-- Notification Bell -->
        <a href="<?= BASE_URL ?>/customer/notifications.php" class="topbar-btn" title="Notifications">
          🔔
          <?php if ($unreadNotifsCount > 0): ?>
            <span class="topbar-badge"><?= $unreadNotifsCount ?></span>
          <?php endif; ?>
        </a>

        <!-- Cart Quick Access -->
        <a href="<?= BASE_URL ?>/customer/cart.php" class="topbar-btn" title="Pre-Order Basket">
          🛒
          <span class="topbar-badge topbar-cart-badge cart-count-badge" style="<?= $cartItemCount > 0 ? '' : 'display:none;' ?>">
            <?= $cartItemCount ?>
          </span>
        </a>

        <!-- Customer Profile Link -->
        <a href="<?= BASE_URL ?>/customer/profile.php" style="text-decoration:none; display:flex; align-items:center; gap:0.65rem;">
          <div class="sidebar-user-avatar" style="width:36px; height:36px; font-size:0.9rem;">
            <?= strtoupper(substr($displayName, 0, 1)) ?>
          </div>
          <div style="display:none; @media(min-width:768px){display:block;}">
            <span style="font-size:0.85rem; font-weight:700; color:var(--text-primary); display:block; line-height:1.2;">
              <?= htmlspecialchars($displayName) ?>
            </span>
            <span style="font-size:0.7rem; color:var(--emerald-400); font-weight:600;">Verified Customer</span>
          </div>
        </a>
      </div>
    </header>

    <!-- Page Body -->
    <main class="customer-content">
