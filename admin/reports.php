<?php
/**
 * MarketLink - Admin Platform Reports & Executive Analytics
 * SRS Section 1.6: Admin can view platform-wide reports covering total orders, 
 * revenue summary across markets, and the most active Farmers.
 */

$pageTitle = 'Reports & Analytics';
$activeNav = 'reports';
require_once __DIR__ . '/includes/admin_header.php';

$range = trim($_GET['range'] ?? '30'); // '7', '30', '90', 'all'

$dateCondition = "";
if ($range === '7') {
    $dateCondition = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($range === '30') {
    $dateCondition = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($range === '90') {
    $dateCondition = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
}

// 1. Overall Aggregates for period
$aggSql = "SELECT COUNT(*) as period_orders,
                  COALESCE(SUM(total_amount), 0) as period_revenue,
                  COALESCE(AVG(total_amount), 0) as period_aov
           FROM orders o
           WHERE o.order_status NOT IN ('cancelled', 'declined') {$dateCondition}";
$periodStats = $pdo->query($aggSql)->fetch();

// 2. Revenue Summary across Markets
$marketSql = "SELECT m.market_id, m.market_name, m.city,
                     COUNT(o.order_id) as total_orders,
                     COALESCE(SUM(o.total_amount), 0) as total_revenue,
                     COALESCE(AVG(o.total_amount), 0) as avg_order_val
              FROM markets m
              LEFT JOIN orders o ON m.market_id = o.market_id AND o.order_status NOT IN ('cancelled', 'declined') {$dateCondition}
              GROUP BY m.market_id
              ORDER BY total_revenue DESC";
$marketReports = $pdo->query($marketSql)->fetchAll();

// 3. Top Active Farmers / Producers
$farmerSql = "SELECT fp.farmer_id, fp.stall_name, fp.contact_person, u.email,
                     COUNT(o.order_id) as orders_count,
                     COALESCE(SUM(o.total_amount), 0) as revenue_generated,
                     (SELECT COALESCE(AVG(rating), 5.0) FROM farmer_reviews fr WHERE fr.farmer_id = fp.farmer_id) as avg_rating
              FROM farmer_profiles fp
              JOIN users u ON fp.farmer_id = u.user_id
              LEFT JOIN orders o ON fp.farmer_id = o.farmer_id AND o.order_status NOT IN ('cancelled', 'declined') {$dateCondition}
              GROUP BY fp.farmer_id
              ORDER BY revenue_generated DESC, orders_count DESC";
$farmerReports = $pdo->query($farmerSql)->fetchAll();

// 4. Produce Category Breakdown
$catSql = "SELECT pc.category_name,
                  COUNT(oi.order_item_id) as items_ordered,
                  COALESCE(SUM(oi.quantity), 0) as units_sold,
                  COALESCE(SUM(oi.subtotal), 0) as category_volume
           FROM product_categories pc
           LEFT JOIN products p ON pc.category_id = p.category_id
           LEFT JOIN order_items oi ON p.product_id = oi.product_id
           LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status NOT IN ('cancelled', 'declined') {$dateCondition}
           GROUP BY pc.category_id
           ORDER BY category_volume DESC";
$categoryReports = $pdo->query($catSql)->fetchAll();
?>

<!-- Print Style Sheet -->
<style>
@media print {
  body { background: #ffffff !important; color: #000000 !important; }
  .admin-sidebar, .admin-topbar, .admin-header-actions, .admin-toolbar, .no-print { display: none !important; }
  .admin-main { margin-left: 0 !important; }
  .admin-content { padding: 0 !important; }
  .admin-card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 1.5rem !important; }
  .admin-table th, .admin-table td { color: #000 !important; border-bottom: 1px solid #ccc !important; }
}
</style>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="bar-chart-3" style="color:var(--admin-accent);"></i>
      Platform Reports &amp; Financial Analytics
    </h1>
    <p>Comprehensive audit breakdown of gross volumes, revenue across pickup markets, and top performing producers.</p>
  </div>

  <div class="admin-header-actions no-print">
    <div class="admin-filter-tabs">
      <a href="?range=7" class="admin-filter-tab <?= ($range === '7') ? 'active' : '' ?>">Last 7 Days</a>
      <a href="?range=30" class="admin-filter-tab <?= ($range === '30') ? 'active' : '' ?>">Last 30 Days</a>
      <a href="?range=90" class="admin-filter-tab <?= ($range === '90') ? 'active' : '' ?>">Quarter (90d)</a>
      <a href="?range=all" class="admin-filter-tab <?= ($range === 'all') ? 'active' : '' ?>">All Time</a>
    </div>

    <button type="button" class="admin-btn admin-btn-secondary" onclick="window.print()">
      <i data-lucide="printer"></i>
      <span>Print / PDF Audit</span>
    </button>
  </div>
</div>

<!-- Key Performance Indicators for Selected Period -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Period Gross Revenue</span>
      <div class="admin-stat-icon-wrapper icon-emerald">
        <i data-lucide="dollar-sign"></i>
      </div>
    </div>
    <div class="admin-stat-value">$<?= number_format($periodStats['period_revenue'], 2) ?></div>
    <div class="admin-stat-footer">
      <span>Completed &amp; active pre-orders</span>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Period Pre-Orders</span>
      <div class="admin-stat-icon-wrapper icon-purple">
        <i data-lucide="shopping-bag"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($periodStats['period_orders']) ?></div>
    <div class="admin-stat-footer">
      <span>Total consumer transactions</span>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Average Order Basket</span>
      <div class="admin-stat-icon-wrapper icon-cyan">
        <i data-lucide="shopping-cart"></i>
      </div>
    </div>
    <div class="admin-stat-value">$<?= number_format($periodStats['period_aov'], 2) ?></div>
    <div class="admin-stat-footer">
      <span>Average spending per reservation</span>
    </div>
  </div>
</div>

<!-- Revenue Summary across Markets -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="map-pin" style="color:var(--admin-cyan);"></i>
        Revenue Performance Across Farmers Markets
      </h3>
      <p class="admin-card-subtitle">Aggregated pickup turnover by physical community location</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Market Location</th>
          <th>City</th>
          <th>Pre-Orders Handled</th>
          <th>Average Order Value</th>
          <th style="text-align:right;">Total Gross Volume</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($marketReports as $mr): ?>
          <tr>
            <td style="font-weight:700; color:var(--admin-text-main); font-size:0.95rem;">
              <?= htmlspecialchars($mr['market_name']) ?>
            </td>
            <td style="font-size:0.85rem; color:var(--admin-text-subtle);">
              <?= htmlspecialchars($mr['city']) ?>
            </td>
            <td>
              <span style="font-weight:700; font-size:0.9rem;">
                <?= number_format($mr['total_orders']) ?>
              </span>
            </td>
            <td style="font-size:0.85rem; color:var(--admin-text-muted);">
              $<?= number_format($mr['avg_order_val'], 2) ?>
            </td>
            <td style="text-align:right; font-family:var(--font-heading); font-size:1.05rem; font-weight:800; color:var(--admin-emerald);">
              $<?= number_format($mr['total_revenue'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Top Performing Farmers / Producer Stalls -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="award" style="color:var(--admin-amber);"></i>
        Producer Stall Activity &amp; Top Revenue Leaders
      </h3>
      <p class="admin-card-subtitle">Top agricultural producers ranked by platform pre-order volume</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Producer Stall</th>
          <th>Contact Farmer</th>
          <th>Average Rating</th>
          <th>Fulfillment Volume</th>
          <th style="text-align:right;">Stall Gross Turnover</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($farmerReports as $fr): ?>
          <tr>
            <td>
              <div style="font-weight:700; color:var(--admin-text-main); font-size:0.95rem;">
                <?= htmlspecialchars($fr['stall_name']) ?>
              </div>
              <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                <?= htmlspecialchars($fr['email']) ?>
              </div>
            </td>
            <td style="font-weight:600; color:var(--admin-text-muted);">
              <?= htmlspecialchars($fr['contact_person']) ?>
            </td>
            <td>
              <span style="color:#f59e0b; font-weight:700; font-size:0.9rem;">
                ★ <?= number_format($fr['avg_rating'], 1) ?>
              </span>
            </td>
            <td>
              <span style="font-weight:700; color:var(--admin-accent);">
                <?= number_format($fr['orders_count']) ?> pre-orders
              </span>
            </td>
            <td style="text-align:right; font-family:var(--font-heading); font-size:1.05rem; font-weight:800; color:var(--admin-emerald);">
              $<?= number_format($fr['revenue_generated'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Produce Category Breakdown -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="pie-chart" style="color:var(--admin-purple);"></i>
        Product Category Sales Volume
      </h3>
      <p class="admin-card-subtitle">Customer consumption breakdown by agricultural produce category</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Category</th>
          <th>Items Ordered</th>
          <th>Units Dispensed</th>
          <th style="text-align:right;">Category Turnover</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categoryReports as $cr): ?>
          <tr>
            <td style="font-weight:700; color:var(--admin-text-main);">
              <?= htmlspecialchars($cr['category_name']) ?>
            </td>
            <td><?= number_format($cr['items_ordered']) ?> times</td>
            <td style="font-weight:600; color:var(--admin-cyan);"><?= number_format($cr['units_sold']) ?> units</td>
            <td style="text-align:right; font-family:var(--font-heading); font-weight:800; color:var(--admin-emerald);">
              $<?= number_format($cr['category_volume'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
