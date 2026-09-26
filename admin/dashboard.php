<?php
/**
 * MarketLink - Super Admin Executive Dashboard
 * Enterprise Silicon Valley Architecture & Modern Visual Analytics
 */

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Fetch aggregate platform metrics
try {
    $totalGrossRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status NOT IN ('cancelled', 'declined')")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalFarmers = (int)$pdo->query("SELECT COUNT(*) FROM farmer_profiles")->fetchColumn();
    $pendingApprovals = (int)$pdo->query("SELECT COUNT(*) FROM farmer_profiles WHERE approval_status = 'pending'")->fetchColumn();
    $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $totalMarkets = (int)$pdo->query("SELECT COUNT(*) FROM markets WHERE status = 'active'")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Order status breakdown for doughnut chart
    $statusStmt = $pdo->query("SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status");
    $statusData = [];
    while ($row = $statusStmt->fetch()) {
        $statusData[$row['order_status']] = (int)$row['cnt'];
    }

    // 1. Revenue & Order Growth Trend (past 14 active points)
    $trendStmt = $pdo->query("
        SELECT DATE(created_at) as order_date, 
               COUNT(*) as order_count, 
               COALESCE(SUM(total_amount), 0) as gross_revenue
        FROM orders
        WHERE order_status NOT IN ('cancelled', 'declined')
        GROUP BY DATE(created_at)
        ORDER BY order_date ASC
        LIMIT 14
    ");
    $trendRows = $trendStmt->fetchAll(PDO::FETCH_ASSOC);
    $trendDates = [];
    $trendRevenues = [];
    $trendOrderCounts = [];
    foreach ($trendRows as $r) {
        $trendDates[] = date('M d', strtotime($r['order_date']));
        $trendRevenues[] = round((float)$r['gross_revenue'], 2);
        $trendOrderCounts[] = (int)$r['order_count'];
    }

    // 2. Top Performing Stalls (by gross volume & total orders)
    $topStallsStmt = $pdo->query("
        SELECT fp.stall_name, 
               COUNT(o.order_id) as total_orders, 
               COALESCE(SUM(o.total_amount), 0) as total_revenue
        FROM farmer_profiles fp
        LEFT JOIN orders o ON fp.farmer_id = o.farmer_id AND o.order_status NOT IN ('cancelled', 'declined')
        WHERE fp.approval_status = 'approved'
        GROUP BY fp.farmer_id
        ORDER BY total_revenue DESC, total_orders DESC
        LIMIT 6
    ");
    $topStallsRows = $topStallsStmt->fetchAll(PDO::FETCH_ASSOC);
    $stallLabels = [];
    $stallRevenues = [];
    $stallOrders = [];
    foreach ($topStallsRows as $ts) {
        $stallLabels[] = $ts['stall_name'];
        $stallRevenues[] = round((float)$ts['total_revenue'], 2);
        $stallOrders[] = (int)$ts['total_orders'];
    }

    // 3. Category Distribution (Products count per category)
    $catDistStmt = $pdo->query("
        SELECT pc.category_name, COUNT(p.product_id) as product_count
        FROM product_categories pc
        LEFT JOIN products p ON pc.category_id = p.category_id
        WHERE pc.is_active = 1
        GROUP BY pc.category_id
        ORDER BY product_count DESC
        LIMIT 7
    ");
    $catDistRows = $catDistStmt->fetchAll(PDO::FETCH_ASSOC);
    $catLabels = [];
    $catCounts = [];
    foreach ($catDistRows as $cd) {
        $catLabels[] = $cd['category_name'];
        $catCounts[] = (int)$cd['product_count'];
    }

    // 4. Market Hub Stall Density Distribution
    $marketDistStmt = $pdo->query("
        SELECT m.market_name, COUNT(fms.stall_id) as stall_count
        FROM markets m
        LEFT JOIN farmer_market_stalls fms ON m.market_id = fms.market_id AND fms.status = 'active'
        WHERE m.status = 'active'
        GROUP BY m.market_id
        ORDER BY stall_count DESC
        LIMIT 6
    ");
    $marketDistRows = $marketDistStmt->fetchAll(PDO::FETCH_ASSOC);
    $marketLabels = [];
    $marketStallCounts = [];
    foreach ($marketDistRows as $md) {
        $shortName = preg_replace('/(Farmers Market|Community Market|Open Market|Souk|Bazaar|Fair|Hub)/i', '', $md['market_name']);
        $shortName = trim(trim($shortName), '-');
        $marketLabels[] = $shortName ?: $md['market_name'];
        $marketStallCounts[] = (int)$md['stall_count'];
    }

    // 5. Executive Quick Metrics
    $avgOrderValue = $totalOrders > 0 ? ($totalGrossRevenue / $totalOrders) : 0;
    $completedCount = ($statusData['completed'] ?? 0) + ($statusData['ready_for_pickup'] ?? 0);
    $fulfillmentRate = $totalOrders > 0 ? round(($completedCount / $totalOrders) * 100, 1) : 100;

    // Pending Farmers awaiting verification
    $pendingFarmers = $pdo->query("SELECT fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, fp.business_email, fp.address, fp.created_at, u.username, u.email
                                   FROM farmer_profiles fp
                                   JOIN users u ON fp.farmer_id = u.user_id
                                   WHERE fp.approval_status = 'pending'
                                   ORDER BY fp.created_at DESC LIMIT 5")->fetchAll();

    // Recent 7 Orders across platform
    $recentOrders = $pdo->query("SELECT o.order_id, o.order_number, o.total_amount, o.order_status, o.pickup_date, o.created_at,
                                        u.username as customer_name,
                                        fp.stall_name as farmer_stall,
                                        m.market_name
                                 FROM orders o
                                 JOIN users u ON o.customer_id = u.user_id
                                 JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                                 JOIN markets m ON o.market_id = m.market_id
                                 ORDER BY o.created_at DESC LIMIT 7")->fetchAll();

    // Recent Users
    $recentUsers = $pdo->query("SELECT user_id, username, email, role, status, created_at FROM users ORDER BY user_id DESC LIMIT 6")->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard query error: " . $e->getMessage());
}
?>

<!-- Executive Header -->
<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="shield-check" style="color:var(--admin-accent);"></i>
      Platform Command Center
    </h1>
    <p>Real-time ecosystem metrics, seller verification queue, and logistics oversight.</p>
  </div>

  <div class="admin-header-actions">
    <a href="<?= BASE_URL ?>/admin/reports.php" class="admin-btn admin-btn-secondary">
      <i data-lucide="file-text"></i>
      <span>Generate Reports</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/announcements.php" class="admin-btn admin-btn-primary">
      <i data-lucide="megaphone"></i>
      <span>Broadcast Alert</span>
    </a>
  </div>
</div>

<?php if ($pendingApprovals > 0): ?>
  <!-- Urgent Action Banner -->
  <div class="admin-alert-banner urgent">
    <div style="display:flex; align-items:center; gap:0.85rem;">
      <i data-lucide="alert-triangle" style="width:24px; height:24px; color:#f59e0b;"></i>
      <div>
        <strong style="display:block; font-size:0.95rem; font-family:var(--font-heading);">
          Action Required: <?= $pendingApprovals ?> Farmer <?= $pendingApprovals === 1 ? 'Stall is' : 'Stalls are' ?> awaiting registration approval!
        </strong>
        <span style="font-size:0.8125rem; opacity:0.9;">Unapproved farmers cannot publish inventories or accept pickup orders.</span>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-btn admin-btn-sm" style="background:#f59e0b; color:#0f172a; font-weight:700;">
      Review Queue &rarr;
    </a>
  </div>
<?php endif; ?>

<?php if (!empty($unreadInquiriesCount) && $unreadInquiriesCount > 0): ?>
  <!-- Unread Contact Inquiries Banner -->
  <div class="admin-alert-banner" style="background:rgba(56, 189, 248, 0.12); border:1.5px solid #38bdf8; color:#7dd3fc; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; padding:1rem 1.4rem; border-radius:14px;">
    <div style="display:flex; align-items:center; gap:0.85rem;">
      <i data-lucide="mail-warning" style="width:24px; height:24px; color:#38bdf8;"></i>
      <div>
        <strong style="display:block; font-size:0.95rem; font-family:var(--font-heading); color:#f0f9ff;">
          Customer Support: <?= $unreadInquiriesCount ?> New Contact <?= $unreadInquiriesCount === 1 ? 'Inquiry' : 'Inquiries' ?> Awaiting Response!
        </strong>
        <span style="font-size:0.8125rem; opacity:0.9; color:#bae6fd;">Visitors have submitted questions or feedback via the website contact form.</span>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/admin/inquiries.php?status=unread" class="admin-btn admin-btn-sm" style="background:#38bdf8; color:#081b14; font-weight:700; border-radius:8px;">
      View &amp; Reply &rarr;
    </a>
  </div>
<?php endif; ?>

<!-- Top Metrics Grid -->
<div class="admin-stats-grid">
  <!-- Gross Pre-Orders Revenue -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Platform Gross Volume</span>
      <div class="admin-stat-icon-wrapper icon-emerald">
        <i data-lucide="dollar-sign"></i>
      </div>
    </div>
    <div class="admin-stat-value">$<?= number_format($totalGrossRevenue, 2) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend positive"><i data-lucide="trending-up"></i> Live</span>
      <span>Total pre-order reservations</span>
    </div>
  </div>

  <!-- Total Pre-Orders -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Total Pre-Orders</span>
      <div class="admin-stat-icon-wrapper icon-purple">
        <i data-lucide="shopping-bag"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalOrders) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend neutral"><?= $activeOrdersCount ?> Active</span>
      <span>placed / ready for pickup</span>
    </div>
  </div>

  <!-- Registered Farmers -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Producer Stalls</span>
      <div class="admin-stat-icon-wrapper icon-amber">
        <i data-lucide="tractor"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalFarmers) ?></div>
    <div class="admin-stat-footer">
      <?php if ($pendingApprovals > 0): ?>
        <span class="stat-trend negative" style="color:var(--admin-amber);"><i data-lucide="clock"></i> <?= $pendingApprovals ?> Pending</span>
      <?php else: ?>
        <span class="stat-trend positive">100% Verified</span>
      <?php endif; ?>
      <span>Registered farms</span>
    </div>
  </div>

  <!-- Customers & Markets -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Active Markets / Hubs</span>
      <div class="admin-stat-icon-wrapper icon-cyan">
        <i data-lucide="map-pin"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalMarkets) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend positive"><?= number_format($totalCustomers) ?> Customers</span>
      <span>Community pickup hubs</span>
    </div>
  </div>
</div>

<!-- Quick Executive Shortcuts Bar -->
<div class="admin-quick-strip">
  <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-quick-tile">
    <div class="admin-quick-tile-left">
      <div class="admin-stat-icon-wrapper icon-amber">
        <i data-lucide="user-check"></i>
      </div>
      <div>
        <span class="admin-quick-tile-title">Farmer Verifications</span>
        <span class="admin-quick-tile-sub"><?= $pendingApprovals ?> Pending Review</span>
      </div>
    </div>
    <span class="admin-btn admin-btn-sm" style="padding:0.35rem 0.65rem; font-size:0.75rem;">Manage &rarr;</span>
  </a>

  <a href="<?= BASE_URL ?>/admin/markets.php" class="admin-quick-tile">
    <div class="admin-quick-tile-left">
      <div class="admin-stat-icon-wrapper icon-emerald">
        <i data-lucide="map-pin"></i>
      </div>
      <div>
        <span class="admin-quick-tile-title">Farmers Markets</span>
        <span class="admin-quick-tile-sub"><?= $totalMarkets ?> Active Hubs</span>
      </div>
    </div>
    <span class="admin-btn admin-btn-sm" style="padding:0.35rem 0.65rem; font-size:0.75rem;">Explore &rarr;</span>
  </a>

  <a href="<?= BASE_URL ?>/admin/categories.php" class="admin-quick-tile">
    <div class="admin-quick-tile-left">
      <div class="admin-stat-icon-wrapper icon-cyan">
        <i data-lucide="tags"></i>
      </div>
      <div>
        <span class="admin-quick-tile-title">Crop Categories</span>
        <span class="admin-quick-tile-sub"><?= count($catLabels) ?> Active Categories</span>
      </div>
    </div>
    <span class="admin-btn admin-btn-sm" style="padding:0.35rem 0.65rem; font-size:0.75rem;">Edit &rarr;</span>
  </a>

  <a href="<?= BASE_URL ?>/admin/reviews.php" class="admin-quick-tile">
    <div class="admin-quick-tile-left">
      <div class="admin-stat-icon-wrapper icon-rose">
        <i data-lucide="shield-alert"></i>
      </div>
      <div>
        <span class="admin-quick-tile-title">Review Moderation</span>
        <span class="admin-quick-tile-sub"><?= $pendingReviewsCount ?> Awaiting Approval</span>
      </div>
    </div>
    <span class="admin-btn admin-btn-sm" style="padding:0.35rem 0.65rem; font-size:0.75rem;">Moderate &rarr;</span>
  </a>
</div>

<!-- ==========================================================================
     Executive Visual Intelligence Hub (Multi-Chart Analytics Suite)
     ========================================================================== -->
<div class="admin-analytics-section">
  <!-- Analytics Section Header -->
  <div class="admin-analytics-header">
    <div class="admin-analytics-title-wrap">
      <h2 class="admin-analytics-title">
        <i data-lucide="activity" style="color:var(--admin-accent);"></i>
        Executive Visual Intelligence Hub
      </h2>
      <span class="admin-live-pulse-badge">
        <span class="live-pulse-dot"></span> Live Telemetry
      </span>
    </div>
    <div style="display:flex; align-items:center; gap:0.5rem;">
      <span style="font-size:0.8rem; color:var(--admin-text-subtle);">Real-time database sync</span>
    </div>
  </div>

  <!-- Micro-KPI Summary Ribbon -->
  <div class="admin-analytics-summary-bar">
    <div class="analytics-micro-kpi anim-stagger-kpi" data-kpi-index="0">
      <div class="analytics-micro-icon" style="background:rgba(34, 197, 94, 0.15); color:#4ade80;">
        <i data-lucide="receipt"></i>
      </div>
      <div class="analytics-micro-info">
        <span class="analytics-micro-lbl">Avg Order Value</span>
        <span class="analytics-micro-val" data-counter-prefix="Rs. " data-counter-val="<?= round($avgOrderValue) ?>">Rs. <?= number_format($avgOrderValue, 0) ?></span>
      </div>
    </div>

    <div class="analytics-micro-kpi anim-stagger-kpi" data-kpi-index="1">
      <div class="analytics-micro-icon" style="background:rgba(56, 189, 248, 0.15); color:#38bdf8;">
        <i data-lucide="check-check"></i>
      </div>
      <div class="analytics-micro-info">
        <span class="analytics-micro-lbl">Fulfillment Rate</span>
        <span class="analytics-micro-val" data-counter-suffix="%" data-counter-val="<?= $fulfillmentRate ?>"><?= $fulfillmentRate ?>%</span>
      </div>
    </div>

    <div class="analytics-micro-kpi anim-stagger-kpi" data-kpi-index="2">
      <div class="analytics-micro-icon" style="background:rgba(250, 204, 21, 0.15); color:#facc15;">
        <i data-lucide="award"></i>
      </div>
      <div class="analytics-micro-info">
        <span class="analytics-micro-lbl">Top Grossing Stall</span>
        <span class="analytics-micro-val" title="<?= htmlspecialchars($stallLabels[0] ?? 'N/A') ?>">
          <?= htmlspecialchars($stallLabels[0] ?? 'N/A') ?>
        </span>
      </div>
    </div>

    <div class="analytics-micro-kpi anim-stagger-kpi" data-kpi-index="3">
      <div class="analytics-micro-icon" style="background:rgba(167, 139, 250, 0.15); color:#a78bfa;">
        <i data-lucide="sprout"></i>
      </div>
      <div class="analytics-micro-info">
        <span class="analytics-micro-lbl">Leading Crop Class</span>
        <span class="analytics-micro-val" title="<?= htmlspecialchars($catLabels[0] ?? 'N/A') ?>">
          <?= htmlspecialchars($catLabels[0] ?? 'N/A') ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Hero Row: Revenue Spline Area Chart + Order Fulfillment Doughnut -->
  <div class="admin-charts-hero-grid">
    <!-- Chart 1: Revenue & Order Trajectory Spline -->
    <div class="admin-chart-card anim-stagger-chart" id="cardRevenueGrowth" data-chart-index="1">
      <div class="admin-chart-card-header">
        <div>
          <h3 class="admin-chart-title">
            <i data-lucide="trending-up" style="color:var(--admin-accent);"></i>
            Gross Revenue &amp; Pre-Order Trajectory
          </h3>
          <p class="admin-chart-subtitle">Smooth spline trend showing daily revenue velocity and customer order demand</p>
        </div>
        <div class="admin-chart-actions">
          <span class="chart-seq-badge"><span class="chart-seq-dot"></span> Live Stream</span>
          <div class="admin-chart-toggle-group" id="revenueViewToggles">
            <button type="button" class="admin-chart-toggle-btn active" data-view="both">Dual Stream</button>
            <button type="button" class="admin-chart-toggle-btn" data-view="revenue">Revenue</button>
            <button type="button" class="admin-chart-toggle-btn" data-view="orders">Orders</button>
          </div>
        </div>
      </div>
      <div class="admin-chart-canvas-wrap" style="height: 310px;">
        <canvas id="revenueGrowthChart"></canvas>
      </div>
    </div>

    <!-- Chart 2: Fulfillment Status Doughnut -->
    <div class="admin-chart-card anim-stagger-chart" id="cardOrderStatus" data-chart-index="2">
      <div class="admin-chart-card-header">
        <div>
          <h3 class="admin-chart-title">
            <i data-lucide="pie-chart" style="color:#38bdf8;"></i>
            Order Fulfillment Health
          </h3>
          <p class="admin-chart-subtitle">Distribution across fulfillment lifecycle</p>
        </div>
        <span class="chart-seq-badge" style="background:rgba(56, 189, 248, 0.1); color:#38bdf8; border-color:rgba(56, 189, 248, 0.25);">
          <span class="chart-seq-dot" style="background:#38bdf8; box-shadow:0 0 8px #38bdf8;"></span> Lifecycle
        </span>
      </div>
      <div class="admin-chart-canvas-wrap" style="height: 310px; display:flex; align-items:center; justify-content:center;">
        <canvas id="orderStatusChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Row 2: Triple Intelligence Bento Grid -->
  <div class="admin-charts-triple-grid">
    <!-- Chart 3: Top Producer Stalls by Gross Volume (Horizontal Bar) -->
    <div class="admin-chart-card anim-stagger-chart" id="cardTopStalls" data-chart-index="3">
      <div class="admin-chart-card-header">
        <div>
          <h3 class="admin-chart-title">
            <i data-lucide="trophy" style="color:#facc15;"></i>
            Top Producer Stalls
          </h3>
          <p class="admin-chart-subtitle">Ranked by total sales revenue volume</p>
        </div>
        <span class="admin-btn admin-btn-sm" style="pointer-events:none; font-size:0.7rem; padding:0.2rem 0.5rem; background:rgba(250,204,21,0.12); color:#facc15; border:1px solid rgba(250,204,21,0.3);">Top 6</span>
      </div>
      <div class="admin-chart-canvas-wrap" style="height: 280px;">
        <canvas id="topStallsChart"></canvas>
      </div>
    </div>

    <!-- Chart 4: Live Crop Category & Product Saturation (Vertical Rounded Columns) -->
    <div class="admin-chart-card anim-stagger-chart" id="cardCategoryDist" data-chart-index="4">
      <div class="admin-chart-card-header">
        <div>
          <h3 class="admin-chart-title">
            <i data-lucide="layers" style="color:#4ade80;"></i>
            Crop Category Saturation
          </h3>
          <p class="admin-chart-subtitle">Active produce catalog items per class</p>
        </div>
        <span class="admin-btn admin-btn-sm" style="pointer-events:none; font-size:0.7rem; padding:0.2rem 0.5rem; background:rgba(34,197,94,0.12); color:#4ade80; border:1px solid rgba(34,197,94,0.3);">Catalog</span>
      </div>
      <div class="admin-chart-canvas-wrap" style="height: 280px;">
        <canvas id="categoryDistChart"></canvas>
      </div>
    </div>

    <!-- Chart 5: Market Hub Saturation & Stall Density (Polar Area) -->
    <div class="admin-chart-card anim-stagger-chart" id="cardMarketDensity" data-chart-index="5">
      <div class="admin-chart-card-header">
        <div>
          <h3 class="admin-chart-title">
            <i data-lucide="compass" style="color:#a78bfa;"></i>
            Market Hub Stall Density
          </h3>
          <p class="admin-chart-subtitle">Active grower stalls per weekend market</p>
        </div>
        <span class="admin-btn admin-btn-sm" style="pointer-events:none; font-size:0.7rem; padding:0.2rem 0.5rem; background:rgba(167,139,250,0.12); color:#a78bfa; border:1px solid rgba(167,139,250,0.3);">Density</span>
      </div>
      <div class="admin-chart-canvas-wrap" style="height: 280px; display:flex; align-items:center; justify-content:center;">
        <canvas id="marketDensityChart"></canvas>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($pendingFarmers)): ?>
  <!-- Pending Farmer Verification Queue Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="clock" style="color:var(--admin-amber);"></i>
          Pending Farmer Approvals (<?= count($pendingFarmers) ?>)
        </h3>
        <p class="admin-card-subtitle">Producer registration requests awaiting administrative approval</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-btn admin-btn-secondary admin-btn-sm">
        View All Pending
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Stall / Farm Name</th>
            <th>Contact Person</th>
            <th>Email & Phone</th>
            <th>Location Address</th>
            <th>Requested Date</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingFarmers as $f): ?>
            <tr id="farmer-row-<?= $f['farmer_id'] ?>">
              <td>
                <div style="font-weight:700; color:var(--admin-text-main);">
                  <?= htmlspecialchars($f['stall_name']) ?>
                </div>
                <span class="status-pill pending">Pending Review</span>
              </td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($f['contact_person']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">@<?= htmlspecialchars($f['username']) ?></div>
              </td>
              <td>
                <div style="font-size:0.8125rem;"><?= htmlspecialchars($f['business_email'] ?: $f['email']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($f['business_phone']) ?></div>
              </td>
              <td style="max-width:240px; font-size:0.8125rem; color:var(--admin-text-muted);">
                <?= htmlspecialchars($f['address']) ?>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle);">
                <?= date('M d, Y', strtotime($f['created_at'])) ?>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem;">
                  <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" onclick="handleFarmerAction(<?= $f['farmer_id'] ?>, 'approve')">
                    <i data-lucide="check"></i> Approve
                  </button>
                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="handleFarmerAction(<?= $f['farmer_id'] ?>, 'reject')">
                    <i data-lucide="x"></i> Reject
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- Recent Platform Pre-Orders & System Registrations -->
<div class="admin-dashboard-split">
  <!-- Recent Pre-Orders Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="package-check" style="color:var(--admin-emerald);"></i>
          Recent Platform Pre-Orders
        </h3>
        <p class="admin-card-subtitle">Live transactions across all pickup stalls</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/orders.php" class="admin-btn admin-btn-secondary admin-btn-sm">
        View All Orders
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Producer Stall</th>
            <th>Total</th>
            <th>Pickup Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">
                No customer orders placed yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td>
                  <strong style="color:var(--admin-accent); font-family:monospace;"><?= htmlspecialchars($o['order_number']) ?></strong>
                </td>
                <td style="font-weight:600;"><?= htmlspecialchars($o['customer_name']) ?></td>
                <td>
                  <div style="font-size:0.85rem;"><?= htmlspecialchars($o['farmer_stall']) ?></div>
                  <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($o['market_name']) ?></div>
                </td>
                <td style="font-weight:700; color:var(--admin-emerald);">$<?= number_format($o['total_amount'], 2) ?></td>
                <td style="font-size:0.8125rem; color:var(--admin-text-subtle);"><?= date('M d, Y', strtotime($o['pickup_date'])) ?></td>
                <td>
                  <span class="status-pill <?= htmlspecialchars($o['order_status']) ?>">
                    <?= ucwords(str_replace('_', ' ', $o['order_status'])) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent User Registrations -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="user-plus" style="color:var(--admin-cyan);"></i>
          New Users
        </h3>
        <p class="admin-card-subtitle">Latest accounts created</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/users.php" class="admin-btn admin-btn-secondary admin-btn-sm">
        All Users
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($u['username']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($u['email']) ?></div>
              </td>
              <td>
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:<?= $u['role'] === 'farmer' ? '#34d399' : ($u['role'] === 'admin' ? '#818cf8' : '#38bdf8') ?>;">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($u['status']) ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Chart & AJAX Interaction Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const isLight = () => document.documentElement.getAttribute('data-theme') === 'light';
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Common typography & theme colors
  const getThemePalette = () => {
    const light = isLight();
    return {
      textColor: light ? '#475569' : '#94a3b8',
      headingColor: light ? '#0f172a' : '#f8fafc',
      gridColor: light ? 'rgba(0, 0, 0, 0.06)' : 'rgba(255, 255, 255, 0.06)',
      tooltipBg: light ? 'rgba(255, 255, 255, 0.96)' : 'rgba(8, 27, 20, 0.95)',
      tooltipTitle: light ? '#0f172a' : '#f0fdf4',
      tooltipBody: light ? '#334155' : '#cbd5e1',
      tooltipBorder: light ? 'rgba(34, 197, 94, 0.3)' : 'rgba(74, 222, 128, 0.35)',
      cardBg: light ? '#ffffff' : '#081d2e'
    };
  };

  let palette = getThemePalette();

  // Helper to format currency
  const formatCurrency = (val) => 'Rs. ' + Number(val).toLocaleString();

  // Helper to animate numbers
  function animateCounter(el, target, duration = 1200, prefix = '', suffix = '') {
    if (prefersReduced) {
      el.textContent = prefix + Number(target).toLocaleString() + suffix;
      return;
    }
    const start = 0;
    const startTime = performance.now();
    const isFloat = String(target).includes('.');

    function updateCounter(now) {
      const elapsed = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - elapsed, 3); // cubic ease-out
      const current = start + (target - start) * eased;
      el.textContent = prefix + (isFloat ? current.toFixed(1) : Math.round(current).toLocaleString()) + suffix;
      if (elapsed < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        el.textContent = prefix + (isFloat ? target : Number(target).toLocaleString()) + suffix;
      }
    }
    requestAnimationFrame(updateCounter);
  }

  // Array to hold chart instances for responsive theme updates
  const chartInstances = [];

  // Data from PHP
  const trendDates = <?= json_encode(!empty($trendDates) ? $trendDates : ['No Data']) ?>;
  const trendRevs = <?= json_encode(!empty($trendRevenues) ? $trendRevenues : [0]) ?>;
  const trendOrders = <?= json_encode(!empty($trendOrderCounts) ? $trendOrderCounts : [0]) ?>;
  const rawStatusData = <?= json_encode($statusData) ?>;
  const sLabels = <?= json_encode(!empty($stallLabels) ? $stallLabels : ['No Producers']) ?>;
  const sRevenues = <?= json_encode(!empty($stallRevenues) ? $stallRevenues : [0]) ?>;
  const sOrders = <?= json_encode(!empty($stallOrders) ? $stallOrders : [0]) ?>;
  const cLabels = <?= json_encode(!empty($catLabels) ? $catLabels : ['None']) ?>;
  const cCounts = <?= json_encode(!empty($catCounts) ? $catCounts : [0]) ?>;
  const mLabels = <?= json_encode(!empty($marketLabels) ? $marketLabels : ['No Markets']) ?>;
  const mCounts = <?= json_encode(!empty($marketStallCounts) ? $marketStallCounts : [0]) ?>;
  const totalOrdersCount = <?= $totalOrders ?>;

  // Chart references
  let revChart = null;
  let statusChart = null;
  let stallsChart = null;
  let catChart = null;
  let marketChart = null;

  // =========================================================================
  // CHART BUILDERS (Called 1-by-1 in choreographed sequence)
  // =========================================================================

  // 1. REVENUE & PRE-ORDER TRAJECTORY SPLINE (STANDOUT ANIMATED LOAD)
  function initRevenueChart() {
    const revCanvas = document.getElementById('revenueGrowthChart');
    if (!revCanvas || revChart) return;
    const card = document.getElementById('cardRevenueGrowth');
    if (card) {
      card.classList.add('loaded');
      card.querySelector('.admin-chart-canvas-wrap')?.classList.add('chart-ready');
    }

    const ctx = revCanvas.getContext('2d');
    const revGradient = ctx.createLinearGradient(0, 0, 0, 260);
    revGradient.addColorStop(0, 'rgba(34, 197, 94, 0.42)');
    revGradient.addColorStop(0.65, 'rgba(34, 197, 94, 0.08)');
    revGradient.addColorStop(1, 'rgba(34, 197, 94, 0.0)');

    const orderGradient = ctx.createLinearGradient(0, 0, 0, 260);
    orderGradient.addColorStop(0, 'rgba(56, 189, 248, 0.25)');
    orderGradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

    revChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: trendDates,
        datasets: [
          {
            label: 'Gross Revenue (Rs.)',
            data: trendRevs,
            borderColor: '#22c55e',
            backgroundColor: revGradient,
            borderWidth: 3.5,
            fill: true,
            tension: 0.38,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointBackgroundColor: '#22c55e',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            yAxisID: 'yRevenue'
          },
          {
            label: 'Pre-Orders Count',
            data: trendOrders,
            borderColor: '#38bdf8',
            backgroundColor: orderGradient,
            borderWidth: 2.5,
            borderDash: [5, 5],
            fill: false,
            tension: 0.38,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#38bdf8',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            yAxisID: 'yOrders'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation: {
          duration: prefersReduced ? 0 : 1600,
          easing: 'easeOutQuart',
          delay: (context) => (context.type === 'data' && !prefersReduced ? context.dataIndex * 85 : 0)
        },
        animations: {
          y: {
            type: 'number',
            easing: 'easeOutCubic',
            duration: prefersReduced ? 0 : 1200,
            delay: (ctx) => (ctx.type === 'data' && !prefersReduced ? ctx.dataIndex * 80 : 0)
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: {
              color: palette.textColor,
              font: { family: 'Inter', size: 11, weight: '600' },
              boxWidth: 14,
              boxHeight: 14,
              usePointStyle: true,
              padding: 15
            }
          },
          tooltip: {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipTitle,
            bodyColor: palette.tooltipBody,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            padding: 12,
            cornerRadius: 10,
            usePointStyle: true,
            boxPadding: 6,
            callbacks: {
              label: function(context) {
                if (context.datasetIndex === 0) {
                  return ' Revenue: ' + formatCurrency(context.parsed.y);
                }
                return ' Pre-Orders: ' + context.parsed.y + ' orders';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: palette.gridColor, drawBorder: false },
            ticks: {
              color: palette.textColor,
              font: { family: 'Inter', size: 10, weight: '500' }
            }
          },
          yRevenue: {
            type: 'linear',
            position: 'left',
            grid: { color: palette.gridColor, drawBorder: false },
            ticks: {
              color: '#22c55e',
              font: { family: 'Inter', size: 10, weight: '600' },
              callback: (val) => 'Rs.' + (val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val)
            }
          },
          yOrders: {
            type: 'linear',
            position: 'right',
            grid: { display: false },
            ticks: {
              color: '#38bdf8',
              font: { family: 'Inter', size: 10, weight: '600' },
              precision: 0
            }
          }
        }
      }
    });
    chartInstances.push(revChart);

    // Toggle button handlers
    const toggleBtns = document.querySelectorAll('#revenueViewToggles .admin-chart-toggle-btn');
    toggleBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        toggleBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const view = this.getAttribute('data-view');
        if (view === 'revenue') {
          revChart.setDatasetVisibility(0, true);
          revChart.setDatasetVisibility(1, false);
          revChart.options.scales.yRevenue.display = true;
          revChart.options.scales.yOrders.display = false;
        } else if (view === 'orders') {
          revChart.setDatasetVisibility(0, false);
          revChart.setDatasetVisibility(1, true);
          revChart.options.scales.yRevenue.display = false;
          revChart.options.scales.yOrders.display = true;
        } else {
          revChart.setDatasetVisibility(0, true);
          revChart.setDatasetVisibility(1, true);
          revChart.options.scales.yRevenue.display = true;
          revChart.options.scales.yOrders.display = true;
        }
        revChart.update();
      });
    });
  }

  // 2. ORDER FULFILLMENT DOUGHNUT CHART WITH DYNAMIC CENTER COUNT
  function initOrderStatusChart() {
    const statusCanvas = document.getElementById('orderStatusChart');
    if (!statusCanvas || statusChart) return;
    const card = document.getElementById('cardOrderStatus');
    if (card) {
      card.classList.add('loaded');
      card.querySelector('.admin-chart-canvas-wrap')?.classList.add('chart-ready');
    }

    const labels = Object.keys(rawStatusData).map(s => s.replace(/_/g, ' ').toUpperCase());
    const dataVals = Object.values(rawStatusData);

    let displayOrdersCount = 0;
    const centerTextPlugin = {
      id: 'centerTextPlugin',
      beforeDraw(chart) {
        const { width, height, ctx } = chart;
        ctx.save();
        const light = isLight();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = '800 24px "Plus Jakarta Sans", sans-serif';
        ctx.fillStyle = light ? '#0f172a' : '#f8fafc';
        ctx.fillText(Math.round(displayOrdersCount), width / 2, height / 2 - 8);
        ctx.font = '700 10px "Inter", sans-serif';
        ctx.fillStyle = light ? '#64748b' : '#94a3b8';
        ctx.fillText('ORDERS', width / 2, height / 2 + 14);
        ctx.restore();
      }
    };

    statusChart = new Chart(statusCanvas, {
      type: 'doughnut',
      plugins: [centerTextPlugin],
      data: {
        labels: labels.length ? labels : ['NO ORDERS'],
        datasets: [{
          data: dataVals.length ? dataVals : [1],
          backgroundColor: [
            'rgba(245, 158, 11, 0.9)', // placed
            'rgba(6, 182, 212, 0.9)',  // accepted
            'rgba(34, 197, 94, 0.9)',  // ready_for_pickup
            'rgba(99, 102, 241, 0.9)', // completed
            'rgba(239, 68, 68, 0.85)', // cancelled
            'rgba(148, 163, 184, 0.6)' // declined
          ],
          hoverBackgroundColor: ['#fbbf24', '#22d3ee', '#4ade80', '#818cf8', '#f87171', '#cbd5e1'],
          borderWidth: 3,
          borderColor: isLight() ? '#ffffff' : '#081d2e',
          hoverOffset: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: prefersReduced ? 0 : 1200,
          easing: 'easeOutCubic'
        },
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: palette.textColor,
              font: { family: 'Inter', size: 10, weight: '600' },
              padding: 10,
              usePointStyle: true,
              boxWidth: 10
            }
          },
          tooltip: {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipTitle,
            bodyColor: palette.tooltipBody,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                const count = context.parsed;
                const pct = totalOrdersCount > 0 ? ((count / totalOrdersCount) * 100).toFixed(1) : 0;
                return ` ${context.label}: ${count} (${pct}%)`;
              }
            }
          }
        },
        cutout: '72%'
      }
    });
    chartInstances.push(statusChart);

    // Animate center text count
    if (!prefersReduced) {
      const startTime = performance.now();
      const duration = 1200;
      function stepCenter(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        displayOrdersCount = totalOrdersCount * (1 - Math.pow(1 - progress, 3));
        statusChart.render();
        if (progress < 1) requestAnimationFrame(stepCenter);
      }
      requestAnimationFrame(stepCenter);
    } else {
      displayOrdersCount = totalOrdersCount;
      statusChart.render();
    }
  }

  // 3. TOP PRODUCER STALLS HORIZONTAL BARS
  function initTopStallsChart() {
    const stallsCanvas = document.getElementById('topStallsChart');
    if (!stallsCanvas || stallsChart) return;
    const card = document.getElementById('cardTopStalls');
    if (card) {
      card.classList.add('loaded');
      card.querySelector('.admin-chart-canvas-wrap')?.classList.add('chart-ready');
    }

    const barColors = [
      'rgba(34, 197, 94, 0.85)',
      'rgba(245, 158, 11, 0.85)',
      'rgba(56, 189, 248, 0.85)',
      'rgba(167, 139, 250, 0.85)',
      'rgba(16, 185, 129, 0.85)',
      'rgba(244, 63, 94, 0.85)'
    ];

    stallsChart = new Chart(stallsCanvas, {
      type: 'bar',
      data: {
        labels: sLabels,
        datasets: [{
          label: 'Total Gross Sales',
          data: sRevenues,
          backgroundColor: barColors,
          borderRadius: 6,
          borderSkipped: false,
          barThickness: 16
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: prefersReduced ? 0 : 1000,
          easing: 'easeOutQuart',
          delay: (ctx) => (ctx.type === 'data' && !prefersReduced ? ctx.dataIndex * 110 : 0)
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipTitle,
            bodyColor: palette.tooltipBody,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function(ctx) {
                const idx = ctx.dataIndex;
                const rev = formatCurrency(ctx.parsed.x);
                const ord = sOrders[idx] ? `${sOrders[idx]} orders` : '';
                return ` Gross Sales: ${rev} (${ord})`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: palette.gridColor, drawBorder: false },
            ticks: {
              color: palette.textColor,
              font: { family: 'Inter', size: 9, weight: '500' },
              callback: (v) => 'Rs.' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : v)
            }
          },
          y: {
            grid: { display: false },
            ticks: {
              color: palette.headingColor,
              font: { family: 'Inter', size: 10, weight: '600' },
              callback: function(val) {
                const text = this.getLabelForValue(val);
                return text.length > 18 ? text.substring(0, 16) + '...' : text;
              }
            }
          }
        }
      }
    });
    chartInstances.push(stallsChart);
  }

  // 4. CROP CATEGORY SATURATION (VERTICAL COLUMNS)
  function initCategoryDistChart() {
    const catCanvas = document.getElementById('categoryDistChart');
    if (!catCanvas || catChart) return;
    const card = document.getElementById('cardCategoryDist');
    if (card) {
      card.classList.add('loaded');
      card.querySelector('.admin-chart-canvas-wrap')?.classList.add('chart-ready');
    }

    const columnColors = [
      'rgba(34, 197, 94, 0.85)',
      'rgba(16, 185, 129, 0.85)',
      'rgba(245, 158, 11, 0.85)',
      'rgba(234, 179, 8, 0.85)',
      'rgba(56, 189, 248, 0.85)',
      'rgba(168, 85, 247, 0.85)',
      'rgba(6, 182, 212, 0.85)'
    ];

    catChart = new Chart(catCanvas, {
      type: 'bar',
      data: {
        labels: cLabels,
        datasets: [{
          label: 'Active Produce Items',
          data: cCounts,
          backgroundColor: columnColors,
          borderRadius: 6,
          barThickness: 20
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: prefersReduced ? 0 : 1050,
          easing: 'easeOutBack',
          delay: (ctx) => (ctx.type === 'data' && !prefersReduced ? ctx.dataIndex * 90 : 0)
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipTitle,
            bodyColor: palette.tooltipBody,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: (ctx) => ` Active Products: ${ctx.parsed.y} listed`
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: palette.textColor,
              font: { family: 'Inter', size: 9, weight: '500' },
              callback: function(val) {
                const label = this.getLabelForValue(val);
                return label.length > 11 ? label.substring(0, 9) + '..' : label;
              }
            }
          },
          y: {
            grid: { color: palette.gridColor, drawBorder: false },
            ticks: {
              color: palette.textColor,
              font: { family: 'Inter', size: 9, weight: '500' },
              precision: 0
            }
          }
        }
      }
    });
    chartInstances.push(catChart);
  }

  // 5. MARKET HUB STALL DENSITY (POLAR AREA)
  function initMarketDensityChart() {
    const marketCanvas = document.getElementById('marketDensityChart');
    if (!marketCanvas || marketChart) return;
    const card = document.getElementById('cardMarketDensity');
    if (card) {
      card.classList.add('loaded');
      card.querySelector('.admin-chart-canvas-wrap')?.classList.add('chart-ready');
    }

    marketChart = new Chart(marketCanvas, {
      type: 'polarArea',
      data: {
        labels: mLabels,
        datasets: [{
          data: mCounts,
          backgroundColor: [
            'rgba(34, 197, 94, 0.72)',
            'rgba(56, 189, 248, 0.72)',
            'rgba(250, 204, 21, 0.72)',
            'rgba(167, 139, 250, 0.72)',
            'rgba(244, 63, 94, 0.72)',
            'rgba(20, 184, 166, 0.72)'
          ],
          borderColor: isLight() ? 'rgba(255, 255, 255, 0.9)' : 'rgba(8, 27, 20, 0.8)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: prefersReduced ? 0 : 1250,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: palette.textColor,
              font: { family: 'Inter', size: 9, weight: '600' },
              padding: 8,
              usePointStyle: true,
              boxWidth: 8
            }
          },
          tooltip: {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipTitle,
            bodyColor: palette.tooltipBody,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: (ctx) => ` Stalls: ${ctx.parsed.r} active producers`
            }
          }
        },
        scales: {
          r: {
            grid: { color: palette.gridColor },
            angleLines: { color: palette.gridColor },
            ticks: { display: false, backdropColor: 'transparent' }
          }
        }
      }
    });
    chartInstances.push(marketChart);
  }

  // =========================================================================
  // CHOREOGRAPHED 1-BY-1 SEQUENTIAL ACTIVATION
  // =========================================================================
  let sequenceStarted = false;
  function startSequentialLoading() {
    if (sequenceStarted) return;
    sequenceStarted = true;

    // 0. Micro-KPI cards reveal & counter animation
    const kpiCards = document.querySelectorAll('.analytics-micro-kpi.anim-stagger-kpi');
    kpiCards.forEach((kpi, idx) => {
      setTimeout(() => {
        kpi.classList.add('loaded');
        const valEl = kpi.querySelector('.analytics-micro-val');
        if (valEl && valEl.dataset.counterVal) {
          const target = parseFloat(valEl.dataset.counterVal);
          const prefix = valEl.dataset.counterPrefix || '';
          const suffix = valEl.dataset.counterSuffix || '';
          animateCounter(valEl, target, 1000, prefix, suffix);
        }
      }, prefersReduced ? 0 : idx * 75);
    });

    // Sequential timing per chart (1 by 1 load)
    const baseDelay = prefersReduced ? 0 : 120;
    const stepDelay = prefersReduced ? 0 : 420;

    // Chart 1: Revenue Trajectory (begins drawing line & area)
    setTimeout(initRevenueChart, baseDelay);

    // Chart 2: Order Fulfillment Doughnut
    setTimeout(initOrderStatusChart, baseDelay + stepDelay);

    // Chart 3: Top Producer Stalls
    setTimeout(initTopStallsChart, baseDelay + stepDelay * 2);

    // Chart 4: Crop Category Saturation
    setTimeout(initCategoryDistChart, baseDelay + stepDelay * 3);

    // Chart 5: Market Hub Density
    setTimeout(initMarketDensityChart, baseDelay + stepDelay * 4);
  }

  // IntersectionObserver to start sequence when section is visible
  const analyticsSection = document.querySelector('.admin-analytics-section');
  if (analyticsSection) {
    if (prefersReduced) {
      startSequentialLoading();
    } else {
      const obs = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          startSequentialLoading();
          obs.disconnect();
        }
      }, { threshold: 0.08 });
      obs.observe(analyticsSection);
    }
  } else {
    startSequentialLoading();
  }

  // =========================================================================
  // Theme Switching Live Observer
  // =========================================================================
  const observer = new MutationObserver(() => {
    palette = getThemePalette();
    chartInstances.forEach(c => {
      if (c && c.options) {
        if (c.options.scales) {
          Object.values(c.options.scales).forEach(scale => {
            if (scale.ticks) scale.ticks.color = palette.textColor;
            if (scale.grid) scale.grid.color = palette.gridColor;
          });
        }
        if (c.options.plugins?.legend?.labels) {
          c.options.plugins.legend.labels.color = palette.textColor;
        }
        c.update('none');
      }
    });
  });
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
});

// Farmer Action Handler (Approve / Reject)
async function handleFarmerAction(farmerId, action) {
  const confirmMsg = action === 'approve' 
    ? 'Are you sure you want to approve this farmer stall? They will be able to sell products immediately.'
    : 'Are you sure you want to reject this farmer registration?';

  if (!confirm(confirmMsg)) return;

  try {
    const formData = new FormData();
    formData.append('farmer_id', farmerId);
    formData.append('action', action);

    const res = await fetch('<?= BASE_URL ?>/admin/api/farmer_action.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      const row = document.getElementById(`farmer-row-${farmerId}`);
      if (row) {
        row.style.opacity = '0';
        row.style.transform = 'scale(0.95)';
        row.style.transition = 'all 0.3s ease';
        setTimeout(() => row.remove(), 300);
      }
    } else {
      showAdminToast(data.message || 'Operation failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network error while processing request', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
