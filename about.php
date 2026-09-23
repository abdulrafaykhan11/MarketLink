<?php
/**
 * MarketLink - About Us Page
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'About Us • Our Story & Mission';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/about.css">

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ====== ABOUT HERO ====== -->
<section class="about-hero" id="about-hero">
  <div class="about-hero-overlay"></div>
  <div class="about-hero-content">
    <span class="abt-eyebrow">Our Story</span>
    <h1 class="about-hero-title">
      Grown from <span class="abt-grad-green">Honest Soil.</span><br>
      Built for <span class="abt-grad-sky">Real People.</span>
    </h1>
    <p class="about-hero-lead">
      MarketLink was born in the heart of a Pakistani farming community — where brilliant farmers harvested world-class produce but earned a fraction of its value. We set out to change that, forever.
    </p>
  </div>
</section>

<!-- ====== MISSION & VISION ====== -->
<section class="abt-section" id="mission">
  <div class="section-container">
    <div class="abt-mv-grid">

      <div class="abt-mv-card card-mission">
        <div class="abt-mv-icon">🌱</div>
        <h2 class="abt-mv-title">Our Mission</h2>
        <p class="abt-mv-desc">
          To eliminate every middleman standing between a farmer's morning harvest and a family's dinner table — making fresh, traceable, chemical-free produce accessible to every Pakistani household through transparent direct trade.
        </p>
        <ul class="abt-mv-list">
          <li>Zero commission on direct stall sales</li>
          <li>Farmers set their own fair prices</li>
          <li>Full harvest transparency for buyers</li>
        </ul>
      </div>

      <div class="abt-mv-card card-vision">
        <div class="abt-mv-icon">🔭</div>
        <h2 class="abt-mv-title">Our Vision</h2>
        <p class="abt-mv-desc">
          A Pakistan where every rural farming family thrives with guaranteed income, every urban household eats real food, and the trust between grower and consumer is as natural as the seasons themselves.
        </p>
        <ul class="abt-mv-list">
          <li>1,000+ verified farm stalls across Pakistan</li>
          <li>Every market hub powered by MarketLink</li>
          <li>A new standard for food transparency</li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- ====== PROBLEM STATS ====== -->
<section class="abt-problem-section">
  <div class="section-container">
    <div class="section-header">
      <span class="section-tag section-tag-gold">The Problem We Solve</span>
      <h2 class="section-title">Pakistan's Broken Food Supply Chain</h2>
      <p class="section-subtitle">Between a farmer's field and your plate, up to 6 middlemen consume the majority of the food's true value.</p>
    </div>
    <div class="abt-stats-grid">
      <div class="abt-stat-card">
        <div class="abt-stat-num" style="color:var(--accent-carrot);">65%</div>
        <div class="abt-stat-label">Of grocery price eaten by middlemen</div>
      </div>
      <div class="abt-stat-card">
        <div class="abt-stat-num" style="color:var(--sky-400);">14 Days</div>
        <div class="abt-stat-label">Average cold-storage before supermarket sale</div>
      </div>
      <div class="abt-stat-card">
        <div class="abt-stat-num" style="color:var(--primary-400);">6+</div>
        <div class="abt-stat-label">Middlemen between field and family</div>
      </div>
      <div class="abt-stat-card">
        <div class="abt-stat-num" style="color:var(--accent-gold);">0%</div>
        <div class="abt-stat-label">Traceability in conventional retail</div>
      </div>
    </div>
  </div>
</section>

<!-- ====== PRINCIPLES ====== -->
<section class="abt-section">
  <div class="section-container">
    <div class="section-header">
      <span class="section-tag section-tag-cyan">The MarketLink Difference</span>
      <h2 class="section-title">Our Platform Principles</h2>
    </div>
    <div class="abt-principles-grid">
      <div class="abt-principle-card">
        <div class="abt-principle-icon">🤝</div>
        <h3 class="abt-principle-title">Zero Commission</h3>
        <p class="abt-principle-desc">We charge farmers nothing on their sales. We sustain ourselves through optional premium features — never at the cost of a farmer's fair wage.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">📍</div>
        <h3 class="abt-principle-title">Full Traceability</h3>
        <p class="abt-principle-desc">Every product on MarketLink carries the farmer's name, farm location, and harvest date. No anonymous produce, ever.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">⏱️</div>
        <h3 class="abt-principle-title">24-Hour Freshness</h3>
        <p class="abt-principle-desc">Our platform is designed for same-week stall pickups. From harvest at dawn to your kitchen table in under 24 hours.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">🛡️</div>
        <h3 class="abt-principle-title">Vetted Farmers Only</h3>
        <p class="abt-principle-desc">Every farmer is manually reviewed before their stall goes live. We verify identity, farm location, and produce authenticity.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">🌿</div>
        <h3 class="abt-principle-title">Community First</h3>
        <p class="abt-principle-desc">MarketLink is not just a marketplace — it is a community of farmers and families who believe food should be honest, local, and fair.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">📱</div>
        <h3 class="abt-principle-title">Simple for Everyone</h3>
        <p class="abt-principle-desc">Built for farmers who may not be tech-savvy and families who are busy. Reserve a basket in under 60 seconds, collect at market day.</p>
      </div>
    </div>
  </div>
</section>

<!-- ====== FOUNDING STORY ====== -->
<section class="abt-story-section">
  <div class="section-container">
    <div class="abt-story-layout">
      <div class="abt-story-text">
        <span class="abt-eyebrow">Our Founding Story</span>
        <h2 class="abt-story-heading">Started at a Saturday Morning Market</h2>
        <p class="abt-story-para">Our founder visited a weekend farmers bazaar in Lahore and met a vegetable farmer named Iqbal. Iqbal harvested some of the finest heirloom tomatoes in the region — yet earned less per kilogram than the cost of his fuel to reach market. Three middlemen had taken the rest.</p>
        <p class="abt-story-para">That conversation sparked MarketLink. We spent six months speaking with over 200 farmers and 500 households across Pakistan's major cities to understand exactly what a fair, modern, community farmers market platform needed to do.</p>
        <p class="abt-story-para">Today, MarketLink gives farmers like Iqbal a direct channel to loyal local households — with guaranteed pre-orders, zero commission, and the dignity of knowing their harvest is truly valued.</p>
        <a href="<?= BASE_URL ?>/register.php" class="btn-primary" style="width:auto;padding:0 2rem;display:inline-flex;margin-top:1.5rem;">
          Join Our Community →
        </a>
      </div>
      <div class="abt-story-visual">
        <div class="abt-story-img-frame">
          <img src="<?= BASE_URL ?>/assets/images/farmer_hero.jpg" alt="Local Pakistani Farmer" class="abt-story-img">
          <div class="abt-story-badge">
            <span style="font-size:1.5rem;">👨‍🌾</span>
            <div>
              <div style="font-weight:800;font-size:0.9rem;color:var(--text-primary);">40+ Farmers</div>
              <div style="font-size:0.75rem;color:var(--text-muted);">Verified & Live on Platform</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== CTA ====== -->
<section class="abt-cta-section">
  <div class="section-container">
    <div class="abt-cta-box">
      <h2 class="abt-cta-title">Ready to Taste the Difference?</h2>
      <p class="abt-cta-sub">Join thousands of families eating fresher, supporting local farmers, and paying fair prices.</p>
      <div class="abt-cta-btns">
        <a href="<?= BASE_URL ?>/register.php" class="btn-primary" style="width:auto;padding:0 2.5rem;">Create Free Account →</a>
        <a href="<?= BASE_URL ?>/contact.php" class="btn-secondary" style="height:54px;padding:0 2rem;display:inline-flex;align-items:center;">Contact Us</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
