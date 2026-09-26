<?php

/**
 * MarketLink - Header Template
 */
if (!defined('APP_NAME')) {
  require_once __DIR__ . '/../config/db.php';
}
if (!function_exists('isLoggedIn')) {
  require_once __DIR__ . '/../includes/auth_guard.php';
}

$pageTitle = $pageTitle ?? 'Direct Farmers Marketplace';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="MarketLink - Bridging local farmers directly with consumers for fresh, seasonal harvest with live market stall reservations and pickup slots.">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

  <!-- Theme Switcher Engine (Prevents FOUC) -->
  <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

  <!-- Core Styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css?v=<?= filemtime(__DIR__ . '/../assets/css/main.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/home.css?v=<?= filemtime(__DIR__ . '/../assets/css/home.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/testimonials.css?v=<?= filemtime(__DIR__ . '/../assets/css/testimonials.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css?v=<?= filemtime(__DIR__ . '/../assets/css/auth.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= filemtime(__DIR__ . '/../assets/css/responsive.css') ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">
</head>

<body>
  <div id="toast-container"></div>
