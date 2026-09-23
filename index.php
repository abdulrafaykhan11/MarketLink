<?php
/**
 * MarketLink - Ultra-Luxury Storytelling Homepage
 * Features:
 * - Jaw-dropping storytelling Hero section with authentic farmer centerpiece
 * - Light and Dark theme toggle support
 * - Farm-to-Fork 3-step interactive journey
 * - Supermarket vs MarketLink artistic contrast showcase
 * - Live market stalls & seasonal harvest explorer
 * - Dual-audience onboarding (Customer & Farmer)
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$pdo = getDBConnection();

// Fetch sample active stalls from database or fallback
$stallsQuery = "SELECT fp.farmer_id, fp.stall_name, fp.contact_person, fp.address, fp.approval_status, u.username, u.email 
                FROM farmer_profiles fp 
                JOIN users u ON fp.farmer_id = u.user_id 
                LIMIT 6";
$dbStalls = [];
try {
    $dbStalls = $pdo->query($stallsQuery)->fetchAll();
} catch (Exception $e) {
    $dbStalls = [];
}

// Fallback showcase stalls if database is fresh
$showcaseStalls = [
    [
        'name' => 'Sunrise Organic Orchard',
        'farmer' => 'Tariq Mehmood',
        'location' => 'Agri Hub 4, Model Town Market',
        'rating' => '4.95 ★ (184 reviews)',
        'tags' => ['Heirloom Tomatoes', 'Bell Peppers', 'Organic Spinach', 'Berries'],
        'cutoff' => 'Fri 06:00 PM',
        'slots' => '8 slots available'
    ],
    [
        'name' => 'Green Valley Farm',
        'farmer' => 'Muhammad Asif',
        'location' => 'Stall 12, DHA Weekend Farmers Bazaar',
        'rating' => '4.98 ★ (240 reviews)',
        'tags' => ['Sweetcorn', 'Baby Carrots', 'Farm Zucchini', 'Beets'],
        'cutoff' => 'Fri 08:00 PM',
        'slots' => '15 slots available'
    ],
    [
        'name' => 'Indus Pure Honey & Dairy',
        'farmer' => 'Zainab Bibi',
        'location' => 'Stall 07, F-7 Organic Community Fair',
        'rating' => '4.92 ★ (119 reviews)',
        'tags' => ['Raw Sidr Honey', 'Grass-fed Butter', 'Country Eggs'],
        'cutoff' => 'Sat 07:00 AM',
        'slots' => '6 slots available'
    ]
];

$pageTitle = 'Direct Farmers Marketplace • Fresh From Soil To Table';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Universal Glassmorphic Navbar with Theme Switcher -->
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ==========================================================================
     HERO SECTION: Full-Screen Farm-Fresh Hero Image
     ========================================================================== -->
<section class="hero-section" id="hero">

  <!-- Full-screen background image -->
  <div class="hero-fullscreen-bg">
    <img
      src="<?= BASE_URL ?>/assets/images/farm_fresh_hero.jpg"
      alt="Fresh Farm Produce — Direct From Local Soil to Your Table"
      class="hero-fullscreen-img"
    >
    <!-- Subtle dark overlay so navbar stays readable -->
    <div class="hero-fullscreen-overlay"></div>
  </div>

</section>

<!-- ==========================================================================
     SECTION 2: The Farm-to-Fork Story (3-Step Connected Journey)
     ========================================================================== -->
<section class="story-section" id="how-it-works">
  <div class="section-container">

    <div class="section-header">
      <span class="section-tag">How MarketLink Works</span>
      <h2 class="section-title">The Farm-to-Fork Journey in 3 Transparent Steps</h2>
      <p class="section-subtitle">
        We stripped away wholesale warehouses, freight middlemen, and supermarket markups. Here is how honest fresh food moves from morning soil straight to your table.
      </p>
    </div>

    <div class="journey-grid">
      <!-- Step 1: Emerald Theme -->
      <div class="journey-step-card step-emerald">
        <div class="step-card-top">
          <div class="step-number-badge">01</div>
          <div class="step-card-icon">🌱</div>
        </div>
        <h3 class="step-card-title">Dawn Harvest &amp; Live Stocking</h3>
        <p class="step-card-desc">
          Local certified farmers harvest crops at first morning light when moisture and vitamins are peak. They update their live weekly stall inventories directly on MarketLink.
        </p>
        <div>
          <span class="step-feature-pill">🌿 Harvested &lt; 24h Before Sale</span>
        </div>
      </div>

      <!-- Step 2: Cyan & Teal Theme -->
      <div class="journey-step-card step-cyan">
        <div class="step-card-top">
          <div class="step-number-badge">02</div>
          <div class="step-card-icon">🛒</div>
        </div>
        <h3 class="step-card-title">Online Basket &amp; Slot Reservation</h3>
        <p class="step-card-desc">
          Browse seasonal vegetables, orchard fruits, and farm dairy from your phone. Reserve your customized produce basket and choose a designated pickup time at your neighborhood market stall.
        </p>
        <div>
          <span class="step-feature-pill pill-cyan">⏱ Zero Lines • Guaranteed Produce</span>
        </div>
      </div>

      <!-- Step 3: Amber & Gold Theme -->
      <div class="journey-step-card step-amber">
        <div class="step-card-top">
          <div class="step-number-badge">03</div>
          <div class="step-card-icon">🤝</div>
        </div>
        <h3 class="step-card-title">Handshake &amp; Stall Pickup</h3>
        <p class="step-card-desc">
          Visit the market stall at your reserved hour. Your basket is pre-packed, crisp, and waiting. Greet the farmer who grew your food, verify your items, and enjoy true harvest flavor.
        </p>
        <div>
          <span class="step-feature-pill pill-amber">💰 100% Direct Support to Farmers</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ==========================================================================
     SECTION 3: Creative Freshness Intelligence Lab: Supermarket vs MarketLink
     (Creative Telemetry & Radar replaces flat ordinary side picture)
     ========================================================================== -->
<section class="contrast-section" id="farm-contrast">
  <div class="section-container">

    <div class="contrast-card-wrapper">
      <!-- Left Visual: Creative Freshness Telemetry & Radar Showcase -->
      <div class="contrast-visual-col">
        <div class="contrast-artistic-backdrop"></div>

        <div class="lab-header-badge">
          <span class="lab-status-pill">
            <span class="pulse-dot"></span>
            Live Soil Telemetry
          </span>
          <span class="lab-chip">Vetted Soil Standard</span>
        </div>

        <!-- Centerpiece Radar Visual -->
        <div class="lab-radar-centerpiece">
          <div class="lab-radar-ring-wrapper">
            <div class="lab-radar-outer-glow"></div>
            <div class="lab-radar-circle"></div>
            <div class="lab-radar-inner">
              <div class="radar-value">99.8%</div>
              <div class="radar-label">Peak Bio-Nutrients</div>
            </div>
          </div>
          <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            Real Food Grew in Dirt &amp; Sunlight
          </div>
          <div style="font-size: 0.8125rem; color: var(--text-muted); max-width: 340px;">
            Zero cold-storage nitrogen tanks. Zero synthetic shelf-life spray.
          </div>
        </div>

        <!-- Floating Micro-Telemetry Badges -->
        <div class="lab-telemetry-pills-grid">
          <div class="telemetry-micro-card">
            <div class="telemetry-micro-icon">🌱</div>
            <div>
              <div class="telemetry-micro-title">Harvest Age</div>
              <div class="telemetry-micro-sub">Under 3.5 Hours</div>
            </div>
          </div>

          <div class="telemetry-micro-card">
            <div class="telemetry-micro-icon">🧪</div>
            <div>
              <div class="telemetry-micro-title">Chemical Residue</div>
              <div class="telemetry-micro-sub cyan">0.0% Zero Wax &amp; Gas</div>
            </div>
          </div>

          <div class="telemetry-micro-card">
            <div class="telemetry-micro-icon">💰</div>
            <div>
              <div class="telemetry-micro-title">Grower Payment</div>
              <div class="telemetry-micro-sub gold">100% Direct to Farmer</div>
            </div>
          </div>

          <div class="telemetry-micro-card">
            <div class="telemetry-micro-icon">📍</div>
            <div>
              <div class="telemetry-micro-title">Soil Origin</div>
              <div class="telemetry-micro-sub">Verified Farm Plot #14</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Comparative Analysis Matrix -->
      <div class="contrast-content-col">
        <span class="section-tag section-tag-gold" style="width: fit-content;">The Real Truth</span>
        <h2 style="font-family: var(--font-heading); font-size: 2.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.75rem; line-height: 1.2;">
          Why the Supermarket Supply Chain is Broken
        </h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.65;">
          Most retail supermarket vegetables are harvested unripe, preserved in artificial cold chillers for up to 2 weeks, and handled by 4 to 6 middlemen who consume up to 65% of your grocery bill.
        </p>

        <div class="contrast-table">
          <div class="contrast-row">
            <div class="contrast-cell-bad">
              <span class="cross">✕</span>
              <div>
                <strong style="color: var(--text-primary);">Supermarket Produce</strong>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Stored 7–14 days in nitrogen chillers</div>
              </div>
            </div>
            <div class="contrast-cell-good">
              <span class="check">✓</span>
              <div>
                <strong>MarketLink Direct</strong>
                <div style="font-size: 0.75rem; color: var(--primary-400); margin-top: 2px;">Picked at dawn within 24 hours</div>
              </div>
            </div>
          </div>

          <div class="contrast-row">
            <div class="contrast-cell-bad">
              <span class="cross">✕</span>
              <div>
                <strong style="color: var(--text-primary);">Price Markups</strong>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">60%+ eaten by middlemen brokers</div>
              </div>
            </div>
            <div class="contrast-cell-good">
              <span class="check">✓</span>
              <div>
                <strong>Direct Farm Pricing</strong>
                <div style="font-size: 0.75rem; color: var(--accent-400); margin-top: 2px;">100% fair price paid straight to grower</div>
              </div>
            </div>
          </div>

          <div class="contrast-row">
            <div class="contrast-cell-bad">
              <span class="cross">✕</span>
              <div>
                <strong style="color: var(--text-primary);">Anonymous Origin</strong>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Zero traceability on pesticides &amp; source</div>
              </div>
            </div>
            <div class="contrast-cell-good">
              <span class="check">✓</span>
              <div>
                <strong>Full Traceability</strong>
                <div style="font-size: 0.75rem; color: var(--accent-cyan-light); margin-top: 2px;">Know your grower, stall, and exact plot</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>

<!-- ==========================================================================
     SECTION 4: Featured Live Stalls & Seasonal Showcase
     ========================================================================== -->
<section class="stalls-section" id="local-stalls">
  <div class="section-container">

    <div class="section-header">
      <span class="section-tag section-tag-cyan">Direct From Verified Stalls</span>
      <h2 class="section-title">Explore Active Local Producer Stalls</h2>
      <p class="section-subtitle">
        Meet our community of vetted agricultural producers, review their current weekly crops, and reserve your pickup box before slots fill up.
      </p>
    </div>

    <div class="stalls-grid">
      <?php foreach ($showcaseStalls as $i => $stall): ?>
        <div class="stall-card">
          <div class="stall-card-header">
            <span class="stall-badge">🏡 Certified Producer</span>
            <span class="stall-rating"><?= htmlspecialchars($stall['rating']) ?></span>
          </div>

          <h3 class="stall-name"><?= htmlspecialchars($stall['name']) ?></h3>
          <div class="stall-location">
            <span>📍</span>
            <span><?= htmlspecialchars($stall['location']) ?> (Farmer: <?= htmlspecialchars($stall['farmer']) ?>)</span>
          </div>

          <div class="stall-produce-tags">
            <?php foreach ($stall['tags'] as $tIndex => $tag): ?>
              <?php 
                $tagClass = ($tIndex % 3 == 0) ? 'tag-green' : (($tIndex % 3 == 1) ? 'tag-amber' : 'tag-cyan');
              ?>
              <span class="produce-tag <?= $tagClass ?>"><?= htmlspecialchars($tag) ?></span>
            <?php endforeach; ?>
          </div>

          <!-- Live Slot Progress Bar -->
          <div class="stall-slot-bar-wrap">
            <div class="stall-slot-labels">
              <span>Weekly Reserved Slots</span>
              <span><strong><?= htmlspecialchars($stall['slots']) ?></strong></span>
            </div>
            <div class="slot-progress-track">
              <div class="slot-progress-fill" style="width: <?= 65 + (($i * 11) % 30) ?>%;"></div>
            </div>
          </div>

          <div class="stall-footer">
            <div class="cutoff-time">
              <span>Order Cutoff: <strong><?= htmlspecialchars($stall['cutoff']) ?></strong></span>
            </div>

            <a href="<?= BASE_URL ?>/register.php" class="btn-reserve-stall">
              <span>Reserve</span>
              <span>→</span>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ==========================================================================
     SECTION 5: Dual Audience Community Invitation
     ========================================================================== -->
<section class="audience-section" id="community">
  <div class="section-container">

    <div class="audience-grid">
      <!-- For Customers: Emerald & Cyan Glow -->
      <div class="audience-card customer-card">
        <span class="audience-badge">For Conscious Families &amp; Foodies</span>
        <h3 class="audience-title">Eat Healthier, Live Better &amp; Save Money</h3>
        <p class="audience-desc">
          Bring restaurant-quality ingredients into your home kitchen. From heirloom tomatoes bursting with natural juice to raw unfiltered honey straight from hive to jar.
        </p>
        <ul class="audience-features-list">
          <li><span class="bullet">✓</span> Real-time harvest notifications every Thursday morning</li>
          <li><span class="bullet">✓</span> Guaranteed pickup slot with pre-packed, crisp boxes</li>
          <li><span class="bullet">✓</span> Up to 35% cheaper than organic supermarket grocers</li>
          <li><span class="bullet">✓</span> Free cancellation up to 2 hours before cutoff</li>
        </ul>
        <a href="<?= BASE_URL ?>/register.php" class="btn-primary" style="width: auto; display: inline-flex; padding: 0 2rem; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);">
          Join as a Shopper →
        </a>
      </div>

      <!-- For Farmers: Amber & Honey Gold Glow -->
      <div class="audience-card farmer-card">
        <span class="audience-badge">For Farmers &amp; Stallholders</span>
        <h3 class="audience-title">Scale Your Stall with Guaranteed Pre-Orders</h3>
        <p class="audience-desc">
          Stop dumping unsold evening produce or suffering price gouging by middlemen commission agents. Take command of your agricultural enterprise with guaranteed orders.
        </p>
        <ul class="audience-features-list">
          <li><span class="bullet">✓</span> 0% commission drain on direct stall sales</li>
          <li><span class="bullet">✓</span> Know your exact harvest quota before dawn picking</li>
          <li><span class="bullet">✓</span> Direct relationship with loyal local neighborhood households</li>
          <li><span class="bullet">✓</span> Free stall management software &amp; slot planner</li>
        </ul>
        <a href="<?= BASE_URL ?>/register.php" class="btn-primary" style="width: auto; display: inline-flex; padding: 0 2rem; background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);">
          Register Your Stall →
        </a>
      </div>
    </div>

  </div>
</section>

<!-- ==========================================================================
     SECTION 6: Interactive FAQ Accordion
     ========================================================================== -->
<section class="faq-section" id="faq">
  <div class="section-container">

    <div class="section-header">
      <span class="section-tag section-tag-gold">Got Questions?</span>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p class="section-subtitle">Everything you need to know about reserving fresh produce and collecting your basket.</p>
    </div>

    <div class="faq-accordion">
      <div class="faq-item active">
        <button type="button" class="faq-question-btn">
          <span>How does pickup slot reservation work?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          When you reserve items online, you choose an exact time window (e.g., Saturday 9:00 AM – 10:30 AM) at your nearest market stall. The farmer packs your basket prior to your arrival. When you arrive, simply show your digital pickup slip and collect your fresh goods instantly with zero waiting.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question-btn">
          <span>When do I pay for my harvest basket?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          MarketLink allows you to verify your produce in person upon pickup. You can settle directly at the stall via cash, mobile digital wallet (Easypaisa/JazzCash), or online payment depending on the farmer's stall options.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question-btn">
          <span>How are farmers vetted for quality?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          Every farmer account is manually reviewed by platform administration before stalls go live. We inspect farm location credentials, verify contact information, and moderate transparent community reviews from verified buyers.
        </div>
      </div>

      <div class="faq-item">
        <button type="button" class="faq-question-btn">
          <span>What if I cannot make it to my pickup slot?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          You can easily modify or cancel your order up to 2 hours before the farmer's published order cutoff time directly from your customer dashboard.
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ==========================================================================
     SECTION 7: High-Impact Call to Action Banner
     ========================================================================== -->
<section style="padding: 6rem 1.5rem; background: var(--bg-primary); position: relative;">
  <div class="section-container">
    <div style="background: linear-gradient(135deg, #09261a 0%, #0c3826 40%, #0e2942 100%); border: 2px solid var(--border-hover); border-radius: var(--radius-xl); padding: 5rem 2.5rem; text-align: center; position: relative; overflow: hidden; box-shadow: 0 30px 70px -15px rgba(0,0,0,0.6);">
      <!-- Subtle ambient inner glow -->
      <div style="position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 300px; background: radial-gradient(ellipse, rgba(52, 211, 153, 0.25) 0%, transparent 70%); pointer-events: none;"></div>
      
      <div style="max-width: 720px; margin: 0 auto; position: relative; z-index: 2;">
        <span style="background: rgba(16,185,129,0.22); border: 1px solid rgba(52,211,153,0.5); color: #6ee7b7; font-weight: 800; font-size: 0.8125rem; padding: 0.45rem 1.15rem; border-radius: var(--radius-full); text-transform: uppercase; letter-spacing: 0.08em; display: inline-block; margin-bottom: 1.5rem;">
          Join the Fresh Harvest Revolution
        </span>
        <h2 style="font-family: var(--font-heading); font-size: clamp(2.2rem, 3.8vw, 3.4rem); font-weight: 800; color: #ffffff; line-height: 1.15; margin-bottom: 1.35rem;">
          Taste What Real Farming Tastes Like This Weekend.
        </h2>
        <p style="color: #cbd5e1; font-size: 1.15rem; line-height: 1.7; margin-bottom: 2.5rem;">
          Create your free account today. Pre-order directly from the soil and make supermarket artificial refrigeration a thing of the past.
        </p>
        <div style="display: flex; justify-content: center; gap: 1.25rem; flex-wrap: wrap;">
          <a href="<?= BASE_URL ?>/register.php" class="btn-primary" style="width: auto; padding: 0 2.75rem; height: 54px; font-size: 1.05rem; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);">
            Create Free Account →
          </a>
          <a href="<?= BASE_URL ?>/#local-stalls" class="btn-secondary" style="height: 54px; padding: 0 2.25rem; display: inline-flex; align-items: center; font-size: 1.05rem; background: rgba(255,255,255,0.08); color: #ffffff; border-color: rgba(255,255,255,0.25);">
            Browse Active Stalls
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     8. LUXURY FOOTER
     ========================================================================== -->
<footer class="site-footer">
  <div class="footer-container">

    <div class="footer-top-grid">
      <!-- Brand Column -->
      <div class="footer-brand-col">
        <a href="<?= BASE_URL ?>/" class="nav-brand">
          <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" class="nav-logo-img">
        </a>
        <p class="footer-desc">
          MarketLink is Pakistan's premier digital community farmers market platform. Empowering local agricultural growers with zero-commission stall pre-orders and providing families with transparent, 24-hour dawn fresh harvest.
        </p>
        <div>
          <!-- Dark & Light Mode Toggle inside Footer -->
          <button type="button" class="theme-toggle-btn" aria-label="Toggle theme in footer">
            <span class="theme-toggle-icon">🌙</span>
            <span class="theme-toggle-text">Dark Mode</span>
          </button>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <div class="footer-col-title">Navigation</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/#how-it-works">The 3-Step Journey</a></li>
          <li><a href="<?= BASE_URL ?>/#farm-contrast">Why Direct From Soil?</a></li>
          <li><a href="<?= BASE_URL ?>/#local-stalls">Active Stalls</a></li>
          <li><a href="<?= BASE_URL ?>/#faq">FAQ</a></li>
          <li><a href="#terms" class="terms-link">Terms &amp; Agreements</a></li>
        </ul>
      </div>

      <!-- Portal Access -->
      <div>
        <div class="footer-col-title">Account Portals</div>
        <ul class="footer-links-list">
          <li><a href="<?= BASE_URL ?>/login.php">Sign In to Account</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Customer Registration</a></li>
          <li><a href="<?= BASE_URL ?>/register.php">Farmer Stall Application</a></li>
          <li><a href="<?= BASE_URL ?>/admin/dashboard.php">Administration</a></li>
        </ul>
      </div>

      <!-- Weekly Harvest Newsletter -->
      <div>
        <div class="footer-col-title">Fresh Harvest Alert</div>
        <p class="footer-desc" style="margin-bottom: 0.5rem;">
          Get every Thursday's dawn harvest list and weekend stall pickup schedules straight to your inbox.
        </p>
        <form id="newsletterForm" class="newsletter-form">
          <input type="email" id="newsletterEmail" class="newsletter-input" placeholder="Enter your email address" required>
          <button type="submit" class="newsletter-btn">Subscribe</button>
        </form>
      </div>
    </div>

    <!-- Bottom Copyright & Credits -->
    <div class="footer-bottom">
      <div>
        © <?= date('Y') ?> MarketLink Direct Agriculture Systems. All Rights Reserved.
      </div>
      <div>
        Built with clean architecture &bull; Handcrafted for Local Farm Communities
      </div>
    </div>

  </div>
</footer>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
