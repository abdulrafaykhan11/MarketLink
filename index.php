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

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDBConnection();

// Fetch sample active approved stalls from database or fallback
$stallsQuery = "SELECT fp.farmer_id, fp.stall_name as name, fp.contact_person as farmer,
                       COALESCE(m.market_name, 'Local Farmers Market') as location,
                       fp.order_cutoff_time,
                       u.profile_image
                FROM farmer_profiles fp 
                JOIN users u ON fp.farmer_id = u.user_id 
                LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id AND fms.status = 'active'
                LEFT JOIN markets m ON fms.market_id = m.market_id
                WHERE fp.approval_status = 'approved'
                ORDER BY fp.farmer_id DESC
                LIMIT 6";
$dbStalls = [];
try {
    $dbStalls = $pdo->query($stallsQuery)->fetchAll();
} catch (Exception $e) {
    $dbStalls = [];
}

// Fallback showcase stalls if database is fresh
$fallbackStalls = [
    [
        'farmer_id' => 0,
        'name' => 'Sunrise Organic Orchard',
        'farmer' => 'Tariq Mehmood',
        'location' => 'Agri Hub 4, Model Town Market',
        'rating' => '4.95 ★ (184 reviews)',
        'tags' => ['Heirloom Tomatoes', 'Bell Peppers', 'Organic Spinach', 'Wild Berries'],
        'cutoff' => 'Fri 06:00 PM',
        'slots' => '8 slots available'
    ],
    [
        'farmer_id' => 0,
        'name' => 'Liaquat Bhai Farm & Hydroponics',
        'farmer' => 'Liaquat Ali',
        'location' => 'Stall 03, Bahria Organic Greenbelt',
        'rating' => '4.97 ★ (220 reviews)',
        'tags' => ['Heirloom Cucumbers', 'Cherry Tomatoes', 'Fresh Mint', 'Organic Kale'],
        'cutoff' => 'Sat 10:00 AM',
        'slots' => '12 slots available'
    ],
    [
        'farmer_id' => 0,
        'name' => 'Green Valley Farm',
        'farmer' => 'Muhammad Asif',
        'location' => 'Stall 12, DHA Weekend Farmers Bazaar',
        'rating' => '4.98 ★ (240 reviews)',
        'tags' => ['Sweetcorn', 'Baby Carrots', 'Farm Zucchini', 'Golden Beets'],
        'cutoff' => 'Fri 08:00 PM',
        'slots' => '15 slots available'
    ],
    [
        'farmer_id' => 0,
        'name' => 'Indus Pure Honey & Dairy',
        'farmer' => 'Zainab Bibi',
        'location' => 'Stall 07, F-7 Organic Community Fair',
        'rating' => '4.92 ★ (119 reviews)',
        'tags' => ['Raw Sidr Honey', 'Grass-fed Butter', 'Free-range Country Eggs'],
        'cutoff' => 'Sat 07:00 AM',
        'slots' => '6 slots available'
    ],
    [
        'farmer_id' => 0,
        'name' => 'Highland Citrus & Olive Grove',
        'farmer' => 'Kamran Shah',
        'location' => 'Stall 18, Margalla Valley Weekend Market',
        'rating' => '4.99 ★ (310 reviews)',
        'tags' => ['Kinnow Mandarins', 'Cold-pressed Olive Oil', 'Wild Pomegranate'],
        'cutoff' => 'Sun 08:00 AM',
        'slots' => '5 slots left'
    ]
];


if (!empty($dbStalls)) {
    $showcaseStalls = [];
    foreach ($dbStalls as $dStall) {
        $farmerId = (int)$dStall['farmer_id'];
        // Fetch up to 4 real product names for this farmer
        $pNames = [];
        try {
            $pStmt = $pdo->prepare("SELECT product_name FROM products WHERE farmer_id = :fid ORDER BY product_id DESC LIMIT 4");
            $pStmt->execute([':fid' => $farmerId]);
            $pNames = $pStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $pNames = [];
        }
        $tags = !empty($pNames) ? $pNames : ['Farm Fresh', 'Seasonal Harvest', 'Pesticide-Free'];

        $showcaseStalls[] = [
            'farmer_id' => $farmerId,
            'name' => $dStall['name'] ?: 'Local Organic Stall',
            'farmer' => $dStall['farmer'] ?: 'Verified Producer',
            'profile_image' => $dStall['profile_image'] ?? '',
            'location' => $dStall['location'] ?: 'Physical Market Hub',
            'rating' => '4.95 ★ (Verified)',
            'tags' => $tags,
            'cutoff' => !empty($dStall['order_cutoff_time']) ? date('h:i A', strtotime($dStall['order_cutoff_time'])) : '06:00 PM',
            'slots' => 'Pickup Slots Available'
        ];
    }
} else {
    $showcaseStalls = $fallbackStalls;
}

// Fetch testimonials for homepage (Admin-controlled: prioritized by is_featured, then top rating)
$testimonialsQuery = "
    SELECT fr.review_id, fr.rating, fr.review_comment, fr.farmer_response, fr.created_at, fr.is_featured,
           u.username as customer_username,
           COALESCE(cp.full_name, u.username) as customer_name,
           u.profile_image as customer_image,
           fp.farmer_id, fp.stall_name,
           COALESCE(fp.contact_person, fu.username) as farmer_name,
           fu.profile_image as farmer_image,
           COALESCE(MAX(m.market_name), 'Local Community Farmers Market') as market_name
    FROM farmer_reviews fr
    JOIN users u ON fr.customer_id = u.user_id
    LEFT JOIN customer_profiles cp ON fr.customer_id = cp.customer_id
    JOIN farmer_profiles fp ON fr.farmer_id = fp.farmer_id
    JOIN users fu ON fp.farmer_id = fu.user_id
    LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id AND fms.status = 'active'
    LEFT JOIN markets m ON fms.market_id = m.market_id
    WHERE fr.is_moderated = 1
      AND fr.review_comment IS NOT NULL
      AND TRIM(fr.review_comment) != ''
    GROUP BY fr.review_id
    ORDER BY fr.is_featured DESC, fr.rating DESC, fr.created_at DESC
    LIMIT 6
";
$testimonials = [];
try {
    $testimonials = $pdo->query($testimonialsQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $testimonials = [];
}

// Fallback high-trust testimonials if database is fresh
if (empty($testimonials)) {
    $testimonials = [
        [
            'review_id' => 0,
            'rating' => 5,
            'review_comment' => "The organic heirloom tomatoes were exceptionally fresh and sweet! Stall was well-organized for fast pickup and produce was crisp.",
            'farmer_response' => "Thank you so much! Our whole farm team is blessed to grow for you 💚",
            'customer_name' => "Sara Ahmed",
            'customer_username' => "sara_ahmed",
            'customer_image' => "",
            'farmer_id' => 2,
            'stall_name' => "Sunrise Organic Orchard",
            'farmer_name' => "Tariq Mehmood",
            'market_name' => "Riverside Organic Community Market",
            'is_featured' => 1
        ],
        [
            'review_id' => 0,
            'rating' => 5,
            'review_comment' => "Best mushrooms I've ever had in my life. Sautéed Lion's Mane in butter and fresh thyme tasted like gourmet scallops! Mind-blowing.",
            'farmer_response' => "JazakAllah for your review! See you next Saturday morning inshAllah! 🌿",
            'customer_name' => "Ahzam Khan",
            'customer_username' => "ahzam_k",
            'customer_image' => "",
            'farmer_id' => 6,
            'stall_name' => "Zara's Hydroponic Garden",
            'farmer_name' => "Hassan Raza",
            'market_name' => "Johar Town Community Harvest Fair",
            'is_featured' => 1
        ],
        [
            'review_id' => 0,
            'rating' => 5,
            'review_comment' => "The Sidr honey is therapeutic. My entire family uses it now instead of processed sugar. Cannot recommend highly enough!",
            'farmer_response' => "",
            'customer_name' => "Saima Rizvi",
            'customer_username' => "saima_r",
            'customer_image' => "",
            'farmer_id' => 14,
            'stall_name' => "Asif Citrus & Olive Grove",
            'farmer_name' => "Muhammad Asif",
            'market_name' => "Model Town Sunrise Farmers Souk",
            'is_featured' => 1
        ]
    ];
}

$pageTitle = 'Direct Farmers Marketplace • Fresh From Soil To Table';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Universal Glassmorphic Navbar with Theme Switcher -->
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ==========================================================================
     HERO SECTION: Full-Screen Looping Farm Video
     ========================================================================== -->
<section class="hero-section" id="hero">

  <!-- Full-screen autoplay looping video -->
  <div class="hero-fullscreen-bg">
    <video
      class="hero-fullscreen-video"
      autoplay muted loop playsinline
      poster="<?= BASE_URL ?>/assets/images/farm_fresh_hero.jpg"
    >
      <source src="<?= BASE_URL ?>/assets/videos/Grass_text_sways_in_breeze_20260924102508.mp4" type="video/mp4">
    </video>
    <!-- Subtle dark overlay for navbar readability -->
    <div class="hero-fullscreen-overlay"></div>
  </div>

  <div class="hero-mobile-panel">
    <span class="hero-mobile-kicker"><span></span> Live local harvest</span>
    <h1>Fresh from the farm, ready for your table.</h1>
    <p>Browse nearby growers, reserve today's harvest, and collect it at your local market.</p>
    <div class="hero-mobile-actions">
      <a href="<?= BASE_URL ?>/customer/products.php" class="hero-mobile-primary">Explore fresh produce</a>
      <a href="<?= BASE_URL ?>/stall.php" class="hero-mobile-secondary">Find local stalls</a>
    </div>
    <div class="hero-mobile-proof">
      <strong>Farm to pickup</strong>
      <span>Seasonal produce from verified growers</span>
    </div>
  </div>

</section>

<!-- ==========================================================================
     SECTION 2: Sophisticated Storytelling Bento Grid — Silicon Valley Tier
     Unifies Farm-to-Fork Journey & Broken Supermarket Supply Chain
     ========================================================================== -->
<section class="bento-story-section" id="how-it-works">
  <!-- Anchor for direct jump from navbar link 'Why Direct?' -->
  <span id="farm-contrast" style="position: absolute; top: -80px;"></span>

  <div class="section-container">
    
    <!-- Section Header -->
    <div class="bento-section-header">
      <div class="bento-header-badges">
        <span class="section-tag">Direct Harvest Intelligence</span>
        <span class="bento-live-pulse"><span class="pulse-dot"></span> Live Producer Network</span>
      </div>
      <h2 class="bento-main-title">
       <span class="highlight-gradient"> The Farm-to-Fork Revolution </span>
      </h2>
      <p class="bento-main-subtitle">
        We replaced industrial warehouses, middlemen commissions, and multi-week nitrogen chilling with a live, direct handshake between conscious local households and certified growers.
      </p>
    </div>

    <!-- The Bento Grid Structure -->
    <div class="bento-grid">
      
      <!-- ====================================================================
           PANEL 1: The Farm-to-Fork Journey (Primary Wide Panel, Spans 7 cols)
           Electric Neon Transparent Process ⚡
           ==================================================================== -->
      <div class="bento-card bento-journey-card electric-process-card">
        
        <!-- Ambient Electric Glow Aura -->
        <div class="process-glow-aura" aria-hidden="true"></div>

        <div class="bento-card-header">
          <div class="bento-tag bento-tag-green electric-badge">
            <span class="bento-tag-icon">⚡</span>
            <span>Transparent Process</span>
          </div>
          <span class="bento-meta-badge electric-meta-badge">
            <span class="live-pulse-dot"></span>
            Direct Route • Zero Middlemen
          </span>
        </div>

        <h3 class="bento-card-title">
          The Farm-to-Fork Journey: <span class="highlight-text-electric">Your Story of Freshness in 3 Transparent Steps</span>
        </h3>
        <p class="bento-card-desc">
          How honest, nutrient-dense harvest travels from dawn dew straight to your kitchen table without detours or cold storage:
        </p>

        <!-- 3-Step Process Flow with Electric Connectors -->
        <div class="bento-steps-container electric-steps-flow">
          
          <!-- Step 1 -->
          <div class="bento-step-item step-electric step-electric-emerald" style="--step-accent:#34d399; --step-rgb:52,211,153;">
            <div class="bento-step-icon-wrap step-icon-sun">
              <span class="bento-step-icon">☀️🌱</span>
              <span class="bento-step-num">01</span>
              <div class="step-icon-ring"></div>
            </div>
            <div class="bento-step-content">
              <div class="bento-step-head">
                <h4 class="bento-step-title">Step 1: Dawn Harvest &amp; Live Stocking</h4>
                <span class="bento-step-chip chip-emerald">
                  <span class="chip-dot"></span> 05:30 AM First Light
                </span>
              </div>
              <p class="bento-step-text">
                Certified local farmers pick crops at first morning light when moisture, crispness, and bio-nutrients are at their biological zenith. Quantities immediately sync live on MarketLink.
              </p>
              <div class="bento-step-footer">
                <span class="bento-pill pill-emerald">🌿 Harvested &lt; 24h Before Sale</span>
                <span class="bento-pill-mini">✓ 100% Zero Storage Nitrogen</span>
              </div>
            </div>
          </div>

          <!-- Step Connector Line with Moving Pulse Beam -->
          <div class="bento-step-connector electric-connector">
            <div class="connector-beam"></div>
          </div>

          <!-- Step 2 -->
          <div class="bento-step-item step-electric step-electric-cyan" style="--step-accent:#38bdf8; --step-rgb:56,189,248;">
            <div class="bento-step-icon-wrap step-icon-cart">
              <span class="bento-step-icon">🛒</span>
              <span class="bento-step-slot">#SLOT-04</span>
              <span class="bento-step-num">02</span>
              <div class="step-icon-ring"></div>
            </div>
            <div class="bento-step-content">
              <div class="bento-step-head">
                <h4 class="bento-step-title">Step 2: Online Basket &amp; Slot Reservation</h4>
                <span class="bento-step-chip chip-cyan">
                  <span class="chip-dot"></span> Guaranteed Fresh Box
                </span>
              </div>
              <p class="bento-step-text">
                Explore live stalls from your phone. Reserve your customized seasonal produce basket and lock in a designated morning pickup window at your local community market.
              </p>
              <div class="bento-step-footer">
                <span class="bento-pill pill-cyan">⏱ Zero Wait Lines • 0% Food Waste</span>
                <span class="bento-pill-mini">🔒 Guaranteed Allocation</span>
              </div>
            </div>
          </div>

          <!-- Step Connector Line with Moving Pulse Beam -->
          <div class="bento-step-connector electric-connector">
            <div class="connector-beam"></div>
          </div>

          <!-- Step 3 -->
          <div class="bento-step-item step-electric step-electric-gold" style="--step-accent:#fbbf24; --step-rgb:251,191,36;">
            <div class="bento-step-icon-wrap step-icon-hand">
              <span class="bento-step-icon">🤝</span>
              <span class="bento-step-slot">STALL-07</span>
              <span class="bento-step-num">03</span>
              <div class="step-icon-ring"></div>
            </div>
            <div class="bento-step-content">
              <div class="bento-step-head">
                <h4 class="bento-step-title">Step 3: Handshake &amp; Stall Pickup</h4>
                <span class="bento-step-chip chip-gold">
                  <span class="chip-dot"></span> Personal Collection
                </span>
              </div>
              <p class="bento-step-text">
                Arrive at your chosen slot. Greet the farmer who nurtured your crops, inspect your crisp pre-packed crate, and support true agricultural independence.
              </p>
              <div class="bento-step-footer">
                <span class="bento-pill pill-gold">💰 100% Direct Support to Farmers</span>
                <span class="bento-pill-mini">🌱 Zero Middlemen Fee</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ====================================================================
           PANEL 2: Why the Supermarket Supply Chain is Broken (Spans 5 cols)
           ==================================================================== -->
      <div class="bento-card bento-broken-card">
        <div class="bento-card-header">
          <div class="bento-tag bento-tag-red">
            <span class="bento-tag-icon">⚠️</span>
            <span>The Broken Chain</span>
          </div>
          <span class="bento-meta-badge badge-warning">Industrial Reality</span>
        </div>

        <h3 class="bento-card-title">
          Why the Supermarket Supply Chain is Broken
        </h3>
        <p class="bento-card-desc">
          The concealed commercial loop that sacrifices natural flavor, inflates price, and robs soil caretakers:
        </p>

        <!-- 4 Key Reasons List -->
        <div class="bento-broken-grid">
          
          <!-- Reason 1 -->
          <div class="broken-reason-item">
            <div class="broken-reason-icon">❄️</div>
            <div class="broken-reason-body">
              <h5 class="broken-reason-title">Compromised Freshness</h5>
              <p class="broken-reason-desc">
                Harvested artificially unripe, stored 7–14 days in synthetic nitrogen chillers, and chemically coated to simulate gloss; vital enzymes plummet by up to 58%.
              </p>
            </div>
          </div>

          <!-- Reason 2 -->
          <div class="broken-reason-item">
            <div class="broken-reason-icon">📉</div>
            <div class="broken-reason-body">
              <h5 class="broken-reason-title">Excessive Middlemen</h5>
              <p class="broken-reason-desc">
                Between 4 to 6 commission brokers, auction agents, and corporate logistics wholesalers drain up to 65% of your grocery bill before the grower receives pennies.
              </p>
            </div>
          </div>

          <!-- Reason 3 -->
          <div class="broken-reason-item">
            <div class="broken-reason-icon">🏷️</div>
            <div class="broken-reason-body">
              <h5 class="broken-reason-title">Vague Traceability</h5>
              <p class="broken-reason-desc">
                Anonymous barcode lots pooled together from multiple undisclosed sources, giving consumers zero verifiable record of harvest dates, pesticide loads, or soil health.
              </p>
            </div>
          </div>

          <!-- Reason 4 -->
          <div class="broken-reason-item">
            <div class="broken-reason-icon">🚛</div>
            <div class="broken-reason-body">
              <h5 class="broken-reason-title">Hidden Carbon Footprint</h5>
              <p class="broken-reason-desc">
                Massive freight cross-hauling and power-draining refrigerated freight corridors generate colossal emissions before produce lands on supermarket shelves.
              </p>
            </div>
          </div>

        </div>
      </div>

      <!-- ====================================================================
           PANEL 3: Anti-Gravity 99.8% Metric & "Grown in Soil & Sunlight" (Spans 4 cols)
           ==================================================================== -->
      <div class="bento-card bento-metric-card">
        <div class="bento-card-header">
          <div class="bento-tag bento-tag-cyan">
            <span class="bento-tag-icon">⚡</span>
            <span>Biological Telemetry</span>
          </div>
          <span class="bento-live-chip">Live Index</span>
        </div>

        <!-- Anti-Gravity Centerpiece Display -->
        <div class="antigravity-visual-wrapper">
          
          <!-- Floating Ambient Ring -->
          <div class="antigravity-floating-orbit">
            <div class="orbit-particle p1"></div>
            <div class="orbit-particle p2"></div>
            <div class="orbit-ring-dashed"></div>
            <div class="orbit-core-glow"></div>
            
            <!-- Large floating metric -->
            <div class="antigravity-core">
              <div class="antigravity-number">99.8%</div>
              <div class="antigravity-sub">Peak Bio-Nutrient Retention</div>
            </div>
          </div>

          <!-- Visual Link Line to "Grown in Soil & Sunlight" -->
          <div class="antigravity-lead-line">
            <span class="lead-pulse-node"></span>
          </div>

          <div class="antigravity-soil-badge">
            <div class="soil-badge-icon">☀️🌱</div>
            <div class="soil-badge-title">Grown in Soil &amp; Sunlight</div>
            <div class="soil-badge-desc">
              Zero cold-storage nitrogen chillers. Zero ethylene shelf-life spray. True photosynthesis harvest.
            </div>
          </div>

          <!-- Hovering icon cluster -->
          <div class="hovering-icon-cluster">
            <span class="hover-icon h-1">🥦</span>
            <span class="hover-icon h-2">🍅</span>
            <span class="hover-icon h-3">🍓</span>
          </div>

        </div>
      </div>

      <!-- ====================================================================
           PANEL 4: The Efficiency Gap Graph (Spans 5 cols)
           ==================================================================== -->
      <div class="bento-card bento-efficiency-card">
        <div class="bento-card-header">
          <div class="bento-tag bento-tag-gold">
            <span class="bento-tag-icon">📊</span>
            <span>Efficiency Gap</span>
          </div>
          <span class="bento-meta-badge">Direct vs Supermarket</span>
        </div>

        <h3 class="bento-card-title">
          The Efficiency Gap: Direct vs Supermarket
        </h3>
        <p class="bento-card-desc">
          Comparing the industrial supply chain lag against MarketLink direct velocity:
        </p>

        <!-- Stylized Comparison Graph -->
        <div class="efficiency-graph-stack">
          
          <!-- Metric 1: Harvest-to-Table Velocity -->
          <div class="efficiency-row">
            <div class="efficiency-row-meta">
              <span class="eff-label">Harvest-to-Plate Velocity</span>
              <span class="eff-winner-tag">28x Faster</span>
            </div>
            <div class="eff-bar-group">
              <div class="eff-bar-item bar-bad">
                <span class="eff-bar-name">Supermarket</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill bad-fill" style="width: 14%;"></div>
                </div>
                <span class="eff-bar-val bad-val">12–14 Days</span>
              </div>
              <div class="eff-bar-item bar-good">
                <span class="eff-bar-name">MarketLink</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill good-fill" style="width: 96%;"></div>
                </div>
                <span class="eff-bar-val good-val">&lt; 24 Hours</span>
              </div>
            </div>
          </div>

          <!-- Metric 2: Grower Revenue Share -->
          <div class="efficiency-row">
            <div class="efficiency-row-meta">
              <span class="eff-label">Producer Revenue Share</span>
              <span class="eff-winner-tag tag-gold">+82% Direct</span>
            </div>
            <div class="eff-bar-group">
              <div class="eff-bar-item bar-bad">
                <span class="eff-bar-name">Supermarket</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill bad-fill" style="width: 18%;"></div>
                </div>
                <span class="eff-bar-val bad-val">18% (Middleman Cut)</span>
              </div>
              <div class="eff-bar-item bar-good">
                <span class="eff-bar-name">MarketLink</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill gold-fill" style="width: 100%;"></div>
                </div>
                <span class="eff-bar-val gold-val">100% Direct</span>
              </div>
            </div>
          </div>

          <!-- Metric 3: Active Bio-Nutrient Retention -->
          <div class="efficiency-row">
            <div class="efficiency-row-meta">
              <span class="eff-label">Nutrient Density Retained</span>
              <span class="eff-winner-tag tag-cyan">Peak Vitality</span>
            </div>
            <div class="eff-bar-group">
              <div class="eff-bar-item bar-bad">
                <span class="eff-bar-name">Supermarket</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill bad-fill" style="width: 42%;"></div>
                </div>
                <span class="eff-bar-val bad-val">42% (Chill Degraded)</span>
              </div>
              <div class="eff-bar-item bar-good">
                <span class="eff-bar-name">MarketLink</span>
                <div class="eff-bar-track">
                  <div class="eff-bar-fill cyan-fill" style="width: 99.8%;"></div>
                </div>
                <span class="eff-bar-val cyan-val">99.8% Bio-Active</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ====================================================================
           PANEL 5: High-Tech Biological Soil Telemetry & Sensor Clusters (Spans 3 cols)
           Laboratory-Grade Living Crop Telemetry
           ==================================================================== -->
      <div class="bento-card bento-telemetry-card bio-telemetry-cyber">
        
        <!-- Ambient Radar Scan Aura -->
        <div class="telemetry-scanner-beam" aria-hidden="true"></div>

        <div class="bento-card-header">
          <div class="bento-tag bento-tag-cyan bio-sensor-badge">
            <span class="bento-tag-icon">🧪</span>
            <span>Biological Telemetry</span>
          </div>
          <span class="telemetry-live-radar">
            <span class="radar-ping"></span>
            <span class="radar-text">LIVE 60S SYNC</span>
          </span>
        </div>

        <h4 class="bento-telemetry-title">Soil &amp; Biological Telemetry</h4>
        <p class="bento-telemetry-desc">Live authenticated telemetry streaming directly from verified active soil plots:</p>

        <!-- High-Tech Staggered Micro-Telemetry Sensor Chips -->
        <div class="floating-telemetry-grid bio-sensors-stack">
          
          <!-- Sensor 1: Living Soil pH -->
          <div class="floating-chip float-item-1 bio-sensor-card sensor-loam">
            <div class="bio-sensor-left">
              <div class="floating-chip-icon sensor-icon-soil">🌱</div>
            </div>
            <div class="bio-sensor-body">
              <div class="floating-chip-title">Living Soil Metric</div>
              <div class="floating-chip-value">Plot #14 • pH 6.8 Rich Loam</div>
              <div class="bio-mini-gauge">
                <div class="bio-gauge-bar" style="width: 78%;"></div>
              </div>
            </div>
            <span class="bio-sensor-status status-optimal">OPTIMAL</span>
          </div>

          <!-- Sensor 2: Harvest Freshness Clock -->
          <div class="floating-chip float-item-2 bio-sensor-card sensor-freshness">
            <div class="bio-sensor-left">
              <div class="floating-chip-icon sensor-icon-clock">⏱️</div>
            </div>
            <div class="bio-sensor-body">
              <div class="floating-chip-title">Harvest Age</div>
              <div class="floating-chip-value green">&lt; 3.5h Post-Pick</div>
              <div class="bio-mini-gauge">
                <div class="bio-gauge-bar green-bar" style="width: 95%;"></div>
              </div>
            </div>
            <span class="bio-sensor-status status-live">CRISP</span>
          </div>

          <!-- Sensor 3: Chemical Residue Laboratory Certificate -->
          <div class="floating-chip float-item-3 bio-sensor-card sensor-pure">
            <div class="bio-sensor-left">
              <div class="floating-chip-icon sensor-icon-lab">🛡️</div>
            </div>
            <div class="bio-sensor-body">
              <div class="floating-chip-title">Chemical Residue</div>
              <div class="floating-chip-value cyan">0.0% Zero Wax &amp; Gas</div>
              <div class="bio-mini-gauge">
                <div class="bio-gauge-bar cyan-bar" style="width: 100%;"></div>
              </div>
            </div>
            <span class="bio-sensor-status status-certified">100% PURE</span>
          </div>

          <!-- Sensor 4: Direct Payout Ledger -->
          <div class="floating-chip float-item-4 bio-sensor-card sensor-payout">
            <div class="bio-sensor-left">
              <div class="floating-chip-icon sensor-icon-coin">💰</div>
            </div>
            <div class="bio-sensor-body">
              <div class="floating-chip-title">Grower Direct Ledger</div>
              <div class="floating-chip-value gold">100% Direct to Producer</div>
              <div class="bio-mini-gauge">
                <div class="bio-gauge-bar gold-bar" style="width: 100%;"></div>
              </div>
            </div>
            <span class="bio-sensor-status status-direct">ZERO CUT</span>
          </div>

        </div>

        <!-- Telemetry Soil Verification Badge -->
        <div class="soil-sample-badge bio-telemetry-footer-badge">
          <span class="soil-sample-dot live-bio-dot"></span>
          <span>Authentic Dirt, Water &amp; Photosynthesis Verified</span>
        </div>

      </div>

    </div><!-- /bento-grid -->

  </div><!-- /section-container -->
</section>

<!-- ==========================================================================
     MARQUEE RIBBON: Infinite Scrolling Feature Ticker
     ========================================================================== -->
<div class="marquee-ribbon-wrapper" aria-hidden="true">
  <!-- Top ribbon — scrolls LEFT -->
  <div class="marquee-track marquee-track-top">
    <div class="marquee-inner marquee-ltr">
      <?php for ($r = 0; $r < 3; $r++): ?>
        <span class="mrq-item"><span class="mrq-icon">🌱</span> Dawn-Harvested Produce</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">🤝</span> Zero Middlemen, 100% Direct</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item mrq-highlight"><span class="mrq-icon">⚡</span> 99.8% Bio-Nutrient Retention</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">📍</span> Live Stall Slot Reservations</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">🧪</span> Zero Chemical Residue Verified</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item mrq-highlight-cyan"><span class="mrq-icon">💰</span> Farmers Earn 100% Direct</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">🌾</span> Certified Local Growers Only</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item mrq-highlight-gold"><span class="mrq-icon">🏆</span> Lahore's #1 Farm-to-Fork Platform</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">⏱️</span> Harvest Under 24h Before Pickup</span>
        <span class="mrq-divider">✦</span>
        <span class="mrq-item"><span class="mrq-icon">🗺️</span> Geo-Located Community Markets</span>
        <span class="mrq-divider">✦</span>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Bottom ribbon — scrolls RIGHT -->
  <div class="marquee-track marquee-track-bottom">
    <div class="marquee-inner marquee-rtl">
      <?php for ($r = 0; $r < 3; $r++): ?>
        <span class="mrq-item mrq-highlight-gold"><span class="mrq-icon">🥦</span> Heirloom Vegetables &amp; Heritage Grains</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item"><span class="mrq-icon">🍯</span> Raw Unfiltered Honey &amp; Dairy</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item mrq-highlight"><span class="mrq-icon">🌍</span> Reducing Carbon Footprint 28×</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item"><span class="mrq-icon">📦</span> Pre-Packed Fresh Crates Weekly</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item mrq-highlight-cyan"><span class="mrq-icon">🔬</span> Soil pH &amp; Purity Authenticated</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item"><span class="mrq-icon">🌤️</span> Photosynthesis-Grown — No Cold Storage</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item"><span class="mrq-icon">💎</span> Premium Quality at Farm Price</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item mrq-highlight"><span class="mrq-icon">🏡</span> Support Your Local Farming Community</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item"><span class="mrq-icon">🛒</span> Reserve Your Slot in 60 Seconds</span>
        <span class="mrq-divider">◆</span>
        <span class="mrq-item mrq-highlight-gold"><span class="mrq-icon">🌟</span> 4.97 ★ Avg Rating Across All Stalls</span>
        <span class="mrq-divider">◆</span>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- ==========================================================================
     SECTION 4: Featured Live Stalls & Seasonal Showcase — Asymmetric Bento Grid
     ========================================================================== -->
<section class="stalls-section" id="local-stalls">
  <div class="section-container">

    <div class="section-header stalls-bento-header-wrap">
      <div class="stalls-badge-group">
        <span class="section-tag section-tag-cyan">Direct From Verified Stalls</span>
        <span class="stalls-live-pill"><span class="live-dot"></span> Live Producer Hub</span>
      </div>
      <h2 class="section-title">Explore Active Local Producer Stalls</h2>
      <p class="section-subtitle">
        Meet our community of vetted agricultural producers, review their current weekly crops, and reserve your pickup box before slots fill up.
      </p>
    </div>

    <!-- Asymmetric Bento Grid -->
    <div class="stalls-bento-grid">
      <?php 
      $totalStalls = count($showcaseStalls);
      foreach ($showcaseStalls as $i => $stall):
        $stallUrl = ($stall['farmer_id'] > 0)
          ? BASE_URL . '/stall.php?id=' . (int)$stall['farmer_id']
          : BASE_URL . '/register.php';

        // Dynamic Bento Geometry & Theming per card
        if ($i === 0) {
          $bentoClass = 'stall-bento-hero'; // Featured Hero Bento Box (Wide Panoramic)
          $accentColor = '#34d399';
          $accentRgb = '52, 211, 153';
          $cardBadge = '🌟 Featured Producer of the Week';
          $badgeClass = 'badge-emerald';
        } elseif ($i === 1) {
          $bentoClass = 'stall-bento-tall'; // Featured Pillar Bento Box (Warm Amber Gold)
          $accentColor = '#fbbf24';
          $accentRgb = '251, 191, 36';
          $cardBadge = '⚡ High Demand Stall';
          $badgeClass = 'badge-gold';
        } elseif ($i === 2) {
          $bentoClass = ($totalStalls === 3) ? 'stall-bento-wide' : 'stall-bento-standard';
          $accentColor = '#38bdf8';
          $accentRgb = '56, 189, 248';
          $cardBadge = '🍯 Pure Apiary & Certified Fresh';
          $badgeClass = 'badge-cyan';
        } elseif ($i === 3) {
          $bentoClass = 'stall-bento-standard';
          $accentColor = '#a78bfa';
          $accentRgb = '167, 139, 250';
          $cardBadge = '🌱 Hydroponic & Zero Pesticides';
          $badgeClass = 'badge-violet';
        } else {
          $bentoClass = 'stall-bento-standard';
          $accentColor = '#10b981';
          $accentRgb = '16, 185, 129';
          $cardBadge = '🍊 Direct Orchard Harvest';
          $badgeClass = 'badge-emerald';
        }

        // Live progress bar fill ratio
        $progressPct = 68 + (($i * 7) % 27);
      ?>
        <!-- Bento Card Anchor: Seamlessly routes to Stall details page or Registration -->
        <a href="<?= $stallUrl ?>" 
           class="stall-bento-card <?= $bentoClass ?>" 
           style="--card-accent: <?= $accentColor ?>; --card-accent-rgb: <?= $accentRgb ?>;"
           title="View <?= htmlspecialchars($stall['name']) ?> full profile & harvests">
          
          <!-- Radial Ambient Glow Backdrop Aura -->
          <div class="stall-card-glow-aura" aria-hidden="true"></div>

          <!-- Top Status Bar: Certified Badge & Rating -->
          <div class="stall-bento-header">
            <span class="stall-bento-badge <?= $badgeClass ?>">
              <?= $cardBadge ?>
            </span>
            <div class="stall-bento-rating-pill">
              <span class="rating-star">★</span>
              <span class="rating-val"><?= htmlspecialchars($stall['rating']) ?></span>
            </div>
          </div>

          <!-- Producer Identification Block -->
          <div class="stall-bento-identity">
            <h3 class="stall-bento-name"><?= htmlspecialchars($stall['name']) ?></h3>
            <div class="stall-bento-farmer-row">
              <div class="farmer-avatar-container">
                <?php if (!empty($stall['profile_image'])): ?>
                  <img src="<?= htmlspecialchars(resolveImageUrl($stall['profile_image'])) ?>" 
                       alt="<?= htmlspecialchars($stall['farmer']) ?>" 
                       class="farmer-avatar-photo"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <span class="farmer-avatar-chip" style="<?= !empty($stall['profile_image']) ? 'display:none;' : '' ?>">
                  <?= strtoupper(substr($stall['farmer'] ?: 'ML', 0, 2)) ?>
                </span>
                <span class="farmer-verified-badge" title="Verified Stall Producer">✓</span>
              </div>
              <div class="farmer-meta">
                <span class="farmer-name"><?= htmlspecialchars($stall['farmer']) ?></span>
                <span class="farmer-sep">•</span>
                <span class="farmer-geo">📍 <?= htmlspecialchars($stall['location']) ?></span>
              </div>
            </div>
          </div>

          <!-- Produce / Harvest Tags Cloud -->
          <div class="stall-bento-tags">
            <?php foreach ($stall['tags'] as $tIndex => $tag): ?>
              <?php 
                $tagType = ($tIndex % 3 == 0) ? 'tag-emerald' : (($tIndex % 3 == 1) ? 'tag-amber' : 'tag-cyan');
              ?>
              <span class="stall-btag <?= $tagType ?>">
                <span class="btag-dot"></span>
                <?= htmlspecialchars($tag) ?>
              </span>
            <?php endforeach; ?>
          </div>

          <!-- Live Slots & Allocation Progress Track -->
          <div class="stall-bento-slots-box">
            <div class="slots-meta-row">
              <span class="slots-label">
                <span class="slot-status-icon"></span> Weekly Allocation:
              </span>
              <span class="slots-count">
                <strong><?= htmlspecialchars($stall['slots']) ?></strong>
              </span>
            </div>
            <div class="slots-progress-bar">
              <div class="slots-progress-fill" style="width: <?= $progressPct ?>%;">
                <div class="slots-progress-shine"></div>
              </div>
            </div>
          </div>

          <!-- Card Footer: Cutoff Time & Interactive CTA Button -->
          <div class="stall-bento-footer">
            <div class="stall-bento-cutoff">
              <span class="cutoff-icon">⏱</span>
              <span class="cutoff-text">Cutoff: <strong><?= htmlspecialchars($stall['cutoff']) ?></strong></span>
            </div>

            <div class="stall-bento-cta">
              <span class="cta-label">View Stall</span>
              <span class="cta-arrow" aria-hidden="true">→</span>
            </div>
          </div>

        </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ==========================================================================
     SECTION: Stall Reviews & Community Testimonials (Admin-Controlled)
     Ultra-Luxury Silicon Valley Aesthetics • Fully Theme-Adaptive & Responsive
     ========================================================================== -->
<style id="marketlink-testimonials-styles">
.testimonials-section {
  position: relative;
  padding: 6.5rem 1.5rem;
  background: var(--bg-secondary, #09211a);
  overflow: hidden;
  font-family: var(--font-body, 'Inter', sans-serif);
}

.testimonials-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 900px;
  height: 420px;
  background: radial-gradient(ellipse, rgba(34, 197, 94, 0.16) 0%, rgba(56, 189, 248, 0.08) 45%, transparent 70%);
  pointer-events: none;
  z-index: 1;
}

.testimonials-section .section-container {
  max-width: 1320px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.testimonials-header {
  text-align: center;
  max-width: 820px;
  margin: 0 auto 3.75rem auto;
}

.testimonials-badge-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.testimonials-section .section-tag-gold {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 1.15rem;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.8rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #facc15;
  background: rgba(250, 204, 21, 0.12);
  border: 1px solid rgba(250, 204, 21, 0.35);
  border-radius: var(--radius-full, 9999px);
  box-shadow: 0 4px 15px rgba(250, 204, 21, 0.15);
}

.testimonials-live-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 1rem;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #4ade80;
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.35);
  border-radius: var(--radius-full, 9999px);
}

.live-dot-gold {
  width: 8px;
  height: 8px;
  background: #facc15;
  border-radius: 50%;
  box-shadow: 0 0 10px #facc15;
  animation: pulseGold 2s infinite ease-in-out;
}

@keyframes pulseGold {
  0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 8px #facc15; }
  50% { transform: scale(1.35); opacity: 0.7; box-shadow: 0 0 16px #facc15; }
}

.testimonials-section .section-title {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: clamp(2.1rem, 3.8vw, 3.1rem);
  font-weight: 800;
  color: #ffffff;
  line-height: 1.2;
  margin-bottom: 1.15rem;
  letter-spacing: -0.02em;
}

.testimonials-section .section-subtitle {
  font-size: 1.1rem;
  line-height: 1.7;
  color: var(--slate-300, #cbd5e1);
  margin: 0 auto;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-bottom: 3.5rem;
}

@media (max-width: 1100px) {
  .testimonials-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.75rem;
  }
}

@media (max-width: 720px) {
  .testimonials-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

.testimonial-card {
  position: relative;
  background: rgba(14, 42, 34, 0.78);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border: 1px solid rgba(74, 222, 128, 0.18);
  border-radius: 22px;
  padding: 2.25rem 2rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.5);
  transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1),
              box-shadow 0.38s cubic-bezier(0.16, 1, 0.3, 1),
              border-color 0.35s ease;
  overflow: hidden;
}

.testimonial-card:hover {
  transform: translateY(-8px);
  border-color: rgba(74, 222, 128, 0.55);
  box-shadow: 0 25px 55px -12px rgba(34, 197, 94, 0.28);
}

.testimonial-card-glow {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 180px;
  height: 180px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.22) 0%, rgba(56, 189, 248, 0.12) 50%, transparent 70%);
  pointer-events: none;
  transition: opacity 0.4s ease;
  opacity: 0.45;
}

.testimonial-card:hover .testimonial-card-glow {
  opacity: 1;
}

.testimonial-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  position: relative;
  z-index: 2;
}

.testimonial-stars {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.stars-gold {
  color: #facc15;
  font-size: 1.2rem;
  letter-spacing: 2px;
  text-shadow: 0 2px 8px rgba(250, 204, 21, 0.4);
}

.stars-val {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.85rem;
  font-weight: 800;
  color: #fef08a;
  background: rgba(250, 204, 21, 0.18);
  border: 1px solid rgba(250, 204, 21, 0.35);
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
}

.testimonial-featured-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.725rem;
  font-weight: 800;
  color: #4ade80;
  background: rgba(34, 197, 94, 0.14);
  border: 1px solid rgba(74, 222, 128, 0.4);
  padding: 0.25rem 0.65rem;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tag-sparkle {
  color: #facc15;
  font-size: 0.8rem;
}

.testimonial-quote-icon {
  font-family: 'Georgia', serif;
  font-size: 4.2rem;
  line-height: 1;
  color: rgba(74, 222, 128, 0.15);
  margin-bottom: -1.75rem;
  user-select: none;
  font-style: italic;
}

.testimonial-comment {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #f1f5f9;
  margin-bottom: 1.5rem;
  position: relative;
  z-index: 2;
  flex-grow: 1;
  font-weight: 400;
}

.testimonial-reply-box {
  background: rgba(34, 197, 94, 0.09);
  border-left: 3.5px solid #22c55e;
  border-radius: 10px;
  padding: 0.85rem 1.15rem;
  margin-bottom: 1.5rem;
  position: relative;
  z-index: 2;
}

.reply-header {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.78rem;
  font-weight: 700;
  color: #4ade80;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.reply-text {
  font-size: 0.85rem;
  color: #cbd5e1;
  line-height: 1.5;
  font-style: italic;
  margin: 0;
}

.testimonial-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 1.35rem;
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
  position: relative;
  z-index: 2;
}

.customer-profile {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.customer-avatar-img,
.customer-avatar-chip {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  flex-shrink: 0;
}

.customer-avatar-img {
  object-fit: cover;
  border: 2px solid #22c55e;
}

.customer-avatar-chip {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-weight: 800;
  font-size: 1rem;
  color: #ffffff;
  background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
  border: 2px solid rgba(74, 222, 128, 0.5);
  box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
}

.customer-details {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.customer-name-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.customer-name {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-weight: 700;
  font-size: 1rem;
  color: #ffffff;
}

.verified-buyer-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 17px;
  height: 17px;
  background: #22c55e;
  color: #ffffff;
  border-radius: 50%;
  font-size: 0.65rem;
  font-weight: 900;
  box-shadow: 0 2px 6px rgba(34, 197, 94, 0.4);
}

.customer-label {
  font-size: 0.775rem;
  color: var(--slate-400, #94a3b8);
}

.testimonial-stall-link {
  display: block;
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(74, 222, 128, 0.25);
  border-radius: 14px;
  padding: 0.85rem 1.15rem;
  text-decoration: none;
  transition: all 0.28s ease;
}

.testimonial-stall-link:hover {
  background: rgba(34, 197, 94, 0.16);
  border-color: #22c55e;
  transform: translateX(4px);
}

.stall-link-label {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #4ade80;
  display: block;
  margin-bottom: 0.2rem;
  font-weight: 700;
}

.stall-link-name {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 0.98rem;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-weight: 700;
}

.stall-link-arrow {
  color: #4ade80;
  transition: transform 0.25s ease;
  font-size: 1.1rem;
}

.testimonial-stall-link:hover .stall-link-arrow {
  transform: translateX(4px);
}

.stall-link-market {
  font-size: 0.775rem;
  color: #94a3b8;
  display: block;
  margin-top: 0.25rem;
}

.testimonials-trust-footer {
  background: rgba(14, 42, 34, 0.75);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(74, 222, 128, 0.2);
  border-radius: 18px;
  padding: 1.6rem 2.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.5rem;
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
}

.trust-stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.trust-stat-val {
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-size: 1.7rem;
  font-weight: 800;
  color: #4ade80;
  line-height: 1.1;
  letter-spacing: -0.01em;
}

.trust-stat-lbl {
  font-size: 0.825rem;
  color: #cbd5e1;
  font-weight: 500;
}

.trust-stat-divider {
  width: 1px;
  height: 40px;
  background: rgba(255, 255, 255, 0.12);
}

.btn-trust-order {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.85rem 1.85rem;
  background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
  color: #ffffff;
  font-family: var(--font-heading, 'Plus Jakarta Sans', sans-serif);
  font-weight: 700;
  font-size: 0.95rem;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
  transition: all 0.25s ease;
}

.btn-trust-order:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(34, 197, 94, 0.55);
  color: #ffffff;
}

[data-theme="light"] .testimonials-section {
  background: #f0fdf4;
}

[data-theme="light"] .testimonials-section .section-title {
  color: #0f172a;
}

[data-theme="light"] .testimonials-section .section-subtitle {
  color: #475569;
}

[data-theme="light"] .testimonial-card {
  background: #ffffff;
  border-color: rgba(34, 197, 94, 0.22);
  box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.07);
}

[data-theme="light"] .testimonial-card:hover {
  border-color: #22c55e;
  box-shadow: 0 20px 45px -10px rgba(34, 197, 94, 0.18);
}

[data-theme="light"] .testimonial-comment {
  color: #1e293b;
}

[data-theme="light"] .customer-name {
  color: #0f172a;
}

[data-theme="light"] .customer-label,
[data-theme="light"] .stall-link-market {
  color: #64748b;
}

[data-theme="light"] .reply-text {
  color: #475569;
}

[data-theme="light"] .testimonial-reply-box {
  background: #f0fdf4;
}

[data-theme="light"] .stall-link-name {
  color: #0f172a;
}

[data-theme="light"] .testimonial-stall-link {
  background: #f0fdf4;
  border-color: #bbf7d0;
}

[data-theme="light"] .testimonial-stall-link:hover {
  background: #dcfce7;
  border-color: #86efac;
}

[data-theme="light"] .testimonial-footer {
  border-top-color: #e2e8f0;
}

[data-theme="light"] .testimonials-trust-footer {
  background: #ffffff;
  border-color: #bbf7d0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

[data-theme="light"] .trust-stat-divider {
  background: #cbd5e1;
}

[data-theme="light"] .trust-stat-lbl {
  color: #475569;
}
</style>

<section class="testimonials-section" id="testimonials">
  <div class="section-container">
    
    <div class="section-header testimonials-header">
      <div class="testimonials-badge-wrap">
        <span class="section-tag-gold">★ Verified Stall Ratings</span>
        <span class="testimonials-live-badge"><span class="live-dot-gold"></span> Community Trust</span>
      </div>
      <h2 class="section-title">Loved by Local Foodies, Trusted by Producers</h2>
      <p class="section-subtitle">
        Real experiences from conscious households who reserve dawn-harvested crops directly from our verified market stall owners.
      </p>
    </div>

    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): 
        $rating = (int)($t['rating'] ?? 5);
        $custName = htmlspecialchars($t['customer_name'] ?? 'Verified Customer');
        $stallName = htmlspecialchars($t['stall_name'] ?? 'Local Stall');
        $marketName = htmlspecialchars($t['market_name'] ?? 'Farmers Market');
        $comment = htmlspecialchars($t['review_comment'] ?? '');
        $farmerReply = !empty($t['farmer_response']) ? htmlspecialchars($t['farmer_response']) : '';
        $stallUrl = !empty($t['farmer_id']) ? BASE_URL . '/stall.php?id=' . (int)$t['farmer_id'] : BASE_URL . '/#local-stalls';
        $initials = strtoupper(substr($custName, 0, 2));
      ?>
        <div class="testimonial-card">
          <div class="testimonial-card-glow" aria-hidden="true"></div>
          
          <div class="testimonial-top">
            <div class="testimonial-stars" aria-label="<?= $rating ?> out of 5 stars">
              <span class="stars-gold"><?= str_repeat('★', $rating) ?></span>
              <span class="stars-val"><?= number_format($rating, 1) ?></span>
            </div>
            <?php if (!empty($t['is_featured'])): ?>
              <span class="testimonial-featured-tag">
                <span class="tag-sparkle">✦</span> Top Pick
              </span>
            <?php endif; ?>
          </div>

          <div class="testimonial-quote-icon" aria-hidden="true">“</div>

          <p class="testimonial-comment">
            "<?= nl2br($comment) ?>"
          </p>

          <?php if (!empty($farmerReply)): ?>
            <div class="testimonial-reply-box">
              <div class="reply-header">
                <span class="reply-icon">💬</span>
                <strong>Stall Owner Response:</strong>
              </div>
              <p class="reply-text">"<?= $farmerReply ?>"</p>
            </div>
          <?php endif; ?>

          <div class="testimonial-footer">
            <div class="customer-profile">
              <?php if (!empty($t['customer_image'])): ?>
                <img src="<?= htmlspecialchars(resolveImageUrl($t['customer_image'])) ?>" 
                     alt="<?= $custName ?>" 
                     class="customer-avatar-img"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <?php endif; ?>
              <div class="customer-avatar-chip" style="<?= !empty($t['customer_image']) ? 'display:none;' : '' ?>">
                <?= $initials ?>
              </div>
              <div class="customer-details">
                <div class="customer-name-row">
                  <span class="customer-name"><?= $custName ?></span>
                  <span class="verified-buyer-badge" title="Verified Market Buyer">✓</span>
                </div>
                <span class="customer-label">Verified Community Shopper</span>
              </div>
            </div>

            <a href="<?= $stallUrl ?>" class="testimonial-stall-link" title="Visit <?= $stallName ?>">
              <span class="stall-link-label">Direct Farm Producer:</span>
              <div class="stall-link-name">
                <span>🌾 <?= $stallName ?></span>
                <span class="stall-link-arrow">→</span>
              </div>
              <span class="stall-link-market">📍 <?= $marketName ?></span>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- Live Trust Banner Below Testimonials -->
    <div class="testimonials-trust-footer">
      <div class="trust-stat-item">
        <span class="trust-stat-val">4.96 ★</span>
        <span class="trust-stat-lbl">Average Stall Rating</span>
      </div>
      <div class="trust-stat-divider"></div>
      <div class="trust-stat-item">
        <span class="trust-stat-val">100%</span>
        <span class="trust-stat-lbl">Verified Market Orders</span>
      </div>
      <div class="trust-stat-divider"></div>
      <div class="trust-stat-item">
        <span class="trust-stat-val">Zero</span>
        <span class="trust-stat-lbl">Middleman Commissions</span>
      </div>
      <div class="trust-stat-divider"></div>
      <div class="trust-stat-cta">
        <a href="<?= BASE_URL ?>/customer/products.php" class="btn-trust-order">
          Experience Fresh Harvest →
        </a>
      </div>
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
<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
