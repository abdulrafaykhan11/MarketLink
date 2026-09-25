<?php
/**
 * MarketLink - Intro Experience
 * Scroll-driven animated intro shown to ALL users before the homepage.
 * Faithful recreation of before_home.mp4
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'Welcome to MarketLink';
?>
<!DOCTYPE html>
<html lang="en" data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="MarketLink — Fresh organic produce, daily harvested and delivered directly from local farmers.">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?></title>

  <!-- Google Fonts: Plus Jakarta Sans (matches video typography) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">

  <!-- Intro styles (standalone, no dependency on main.css) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/intro.css">

  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">

  <style>
    /* Inline critical so there is zero FOUC */
    html, body { margin:0; padding:0; background:#eef0e8; overflow:hidden; }
  </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════
     INTRO STAGE  — fixed overlay, 100vw x 100vh
     ═══════════════════════════════════════════════════════════════ -->
<div id="intro-stage">

  <!-- Warm radial glow (harvest scenes) -->
  <div id="intro-glow"></div>
  <!-- Green radial glow (freshly-picked scene) -->
  <div id="intro-glow-green"></div>

  <!-- Huge background typography "FRESH" -->
  <div id="intro-bg-text" aria-hidden="true">FRESH</div>

  <!-- ── TOP NAVIGATION ─────────────────────────────── -->
  <nav id="intro-nav" aria-label="Intro navigation">
    <a href="<?= BASE_URL ?>/index.php" id="intro-logo" aria-label="FreshFind home">
      <img src="<?= BASE_URL ?>/assets/images/intro/logo.png"
           alt="freshfind logo">
    </a>
    <button id="intro-home-btn" type="button">HOME PAGE</button>
  </nav>

  <!-- ═══ SCENE 1: DAILY HARVEST ═══════════════════════ -->
  <section id="scene-harvest" aria-label="Daily Harvest scene">
    <div class="harvest-left">
      <p class="harvest-eyebrow">Pure &amp; Organic</p>
      <h1 class="harvest-title">
        <span class="line-black">DAILY</span>
        <span class="line-green">HARVEST</span>
      </h1>
      <p class="harvest-desc">
        Scroll down to seamlessly unpack crisp,
        farm-fresh organic produce straight out of
        your market basket.
      </p>
    </div>
  </section>

  <!-- ═══ SCENE 2 + 3: YOUR BASKET IS PACKED ═══════════ -->
  <section id="scene-packed" aria-label="Explore FreshFind scene">
    <p class="packed-eyebrow">Taste the Difference</p>
    <div class="packed-title">
      <span class="tc-dark">YOUR BASKET IS</span>
      <span class="tc-dark">PACKED.</span>
      <span class="tc-green">EXPLORE</span>
      <span class="tc-green">FRESHFIND.</span>
    </div>
    <p class="packed-sub">
      Ready to experience the full interactive prototype?<br>
      Click below to jump straight to your homepage.
    </p>
    <a href="<?= BASE_URL ?>/index.php"
       class="packed-cta intro-exit"
       id="intro-cta-main"
       role="button">
      GO TO HOMEPAGE
    </a>
  </section>

  <!-- ═══ SCENE 4: FRESHLY PICKED ══════════════════════ -->
  <section id="scene-picked" aria-label="Freshly Picked scene">
    <div class="picked-card">
      <div class="picked-tag">Premium Quality</div>
      <h2 class="picked-heading">FRESHLY<br>PICKED</h2>
      <ul class="picked-bullets">
        <li>Sourced directly from certified organic local growers</li>
        <li>Hand-inspected for optimum ripeness and top nutrition</li>
        <li>Delivered clean, crisp, and ready for your kitchen</li>
      </ul>
    </div>
  </section>

  <!-- ═══ HERO BASKET (persists across all scenes) ══════ -->
  <div id="intro-basket" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/basket.png"
         alt="Market basket" draggable="false">
  </div>

  <!-- ═══ FLOATING PRODUCE ══════════════════════════════ -->
  <div id="p-broccoli"  class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/broccoli.png" alt="" draggable="false">
  </div>
  <div id="p-corn"      class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/corn.png"     alt="" draggable="false">
  </div>
  <div id="p-pineapple" class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/pineapple.png" alt="" draggable="false">
  </div>
  <div id="p-tomato"    class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/tomato.png"   alt="" draggable="false">
  </div>
  <div id="p-carrot"    class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/carrot.png"   alt="" draggable="false">
  </div>
  <div id="p-grapes"    class="produce-item" aria-hidden="true">
    <img src="<?= BASE_URL ?>/assets/images/intro/grapes.png"   alt="" draggable="false">
  </div>

  <!-- Scroll hint -->
  <div id="intro-scroll-hint" aria-hidden="true">
    <span class="scroll-hint-text">Scroll</span>
    <div class="scroll-hint-line"></div>
  </div>

</div><!-- /#intro-stage -->

<!-- Scroll-driven animation engine -->
<script src="<?= BASE_URL ?>/assets/js/intro.js"></script>

</body>
</html>
