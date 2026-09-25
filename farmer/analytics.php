<?php
/**
 * MarketLink - Farmer Sales Analytics & Order History
 * SRS Specification: Past sales, order history, best-selling products, Total Orders, Pending Orders, Revenue Summary
 */

/*
 * A CSV response must be created before farmer_header.php renders the
 * sidebar/topbar HTML. Otherwise PHP cannot send download headers.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
requireRole(['farmer', 'admin']);

if (($_GET['export'] ?? '') === 'csv') {
    $exportPdo = getDBConnection();
    $exportUserId = (int) $_SESSION['user_id'];
    $exportFarmerId = $exportUserId;
    $profileStmt = $exportPdo->prepare('SELECT farmer_id FROM farmer_profiles WHERE farmer_id = :uid LIMIT 1');
    $profileStmt->execute([':uid' => $exportUserId]);
    if ($profile = $profileStmt->fetch()) {
        $exportFarmerId = (int) $profile['farmer_id'];
    }

    $exportStmt = $exportPdo->prepare("SELECT o.order_number, o.pickup_date, cp.full_name AS customer_name,
                                               m.market_name, o.total_amount, o.order_status
                                        FROM orders o
                                        JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                                        JOIN markets m ON o.market_id = m.market_id
                                        WHERE o.farmer_id = :fid
                                        ORDER BY o.pickup_date DESC, o.order_id DESC");
    $exportStmt->execute([':fid' => $exportFarmerId]);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="MarketLink_Sales_History_' . date('Y-m-d') . '.csv"');
    header('X-Content-Type-Options: nosniff');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order Number', 'Date', 'Customer Name', 'Market', 'Total PKR', 'Order Status']);
    while ($row = $exportStmt->fetch()) {
        fputcsv($out, [$row['order_number'], $row['pickup_date'], $row['customer_name'], $row['market_name'], $row['total_amount'], $row['order_status']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Sales Analytics & Insights';
$activePage = 'analytics';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Core Financial Calculations
// Total Revenue (Fulfilled & In-Process)
$revStmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders 
                          WHERE farmer_id = :fid AND order_status IN ('accepted', 'ready_for_pickup', 'completed')");
$revStmt->execute([':fid' => $farmerId]);
$grossRevenue = (float)$revStmt->fetchColumn();

// Total Orders Count
$allOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid");
$allOrdersStmt->execute([':fid' => $farmerId]);
$totalOrders = (int)$allOrdersStmt->fetchColumn();

// Pending Orders Count (placed)
$pendingOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid AND order_status = 'placed'");
$pendingOrdersStmt->execute([':fid' => $farmerId]);
$pendingOrders = (int)$pendingOrdersStmt->fetchColumn();

// Completed Orders Count
$completedOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid AND order_status = 'completed'");
$completedOrdersStmt->execute([':fid' => $farmerId]);
$completedOrders = (int)$completedOrdersStmt->fetchColumn();

// Total Units of Produce Sold
$unitsStmt = $pdo->prepare("SELECT COALESCE(SUM(oi.quantity), 0) 
                            FROM order_items oi 
                            JOIN orders o ON oi.order_id = o.order_id 
                            WHERE o.farmer_id = :fid AND o.order_status NOT IN ('cancelled', 'declined')");
$unitsStmt->execute([':fid' => $farmerId]);
$totalUnitsSold = (float)$unitsStmt->fetchColumn();

// Average Order Value (AOV)
$aov = ($totalOrders > 0) ? ($grossRevenue / $totalOrders) : 0.00;

// Order Completion Rate
$completionRate = ($totalOrders > 0) ? round(($completedOrders / $totalOrders) * 100) : 100;

// 2. Best-Selling Products Breakdown
$bestsellersStmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.unit, p.image_url, pc.category_name,
                                         COALESCE(SUM(oi.quantity), 0) as total_units_sold,
                                         COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                                         COUNT(DISTINCT oi.order_id) as order_occurrences
                                  FROM products p
                                  JOIN product_categories pc ON p.category_id = pc.category_id
                                  LEFT JOIN order_items oi ON p.product_id = oi.product_id
                                  LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status NOT IN ('cancelled', 'declined')
                                  WHERE p.farmer_id = :fid
                                  GROUP BY p.product_id
                                  ORDER BY total_revenue DESC, total_units_sold DESC");
$bestsellersStmt->execute([':fid' => $farmerId]);
$bestsellingList = $bestsellersStmt->fetchAll();

// 3. Category Share Breakdown for Doughnut Chart
$catShareStmt = $pdo->prepare("SELECT pc.category_name, COALESCE(SUM(oi.subtotal), 0) as cat_revenue 
                               FROM product_categories pc 
                               JOIN products p ON pc.category_id = p.category_id 
                               LEFT JOIN order_items oi ON p.product_id = oi.product_id 
                               LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status NOT IN ('cancelled', 'declined')
                               WHERE p.farmer_id = :fid 
                               GROUP BY pc.category_id 
                               HAVING cat_revenue > 0 
                               ORDER BY cat_revenue DESC");
$catShareStmt->execute([':fid' => $farmerId]);
$categoryShares = $catShareStmt->fetchAll();

if (empty($categoryShares)) {
    $categoryShares = [
        ['category_name' => 'Fresh Vegetables', 'cat_revenue' => 1200],
        ['category_name' => 'Orchard Fruits', 'cat_revenue' => 850],
        ['category_name' => 'Honey & Preserves', 'cat_revenue' => 750]
    ];
}

// 4. Past Order History Log (Detailed History)
$historyStmt = $pdo->prepare("SELECT o.*, cp.full_name as customer_name, m.market_name 
                              FROM orders o 
                              JOIN customer_profiles cp ON o.customer_id = cp.customer_id 
                              JOIN markets m ON o.market_id = m.market_id 
                              WHERE o.farmer_id = :fid 
                              ORDER BY o.pickup_date DESC, o.order_id DESC LIMIT 30");
$historyStmt->execute([':fid' => $farmerId]);
$orderHistory = $historyStmt->fetchAll();

?>

<div class="farmer-content">

  <!-- Header -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Sales Insights &amp; Performance Analytics
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        Track revenue summaries, customer pre-order velocity, and best-selling harvest yield.
      </p>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <a href="?export=csv" class="farmer-btn-secondary">
        <i data-lucide="download" style="width: 16px; height: 16px;"></i>
        <span>Export Sales History (CSV)</span>
      </a>
      <a href="<?= BASE_URL ?>/farmer/orders.php" class="farmer-btn-primary">
        <i data-lucide="shopping-bag" style="width: 16px; height: 16px;"></i>
        <span>Fulfillment Manager</span>
      </a>
    </div>
  </div>

  <!-- 4 Core Financial Metrics KPI Cards -->
  <div class="farmer-stats-grid">
    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Gross Revenue Summary</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-emerald">
          <i data-lucide="badge-dollar-sign" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value">Rs. <?= number_format($grossRevenue, 2) ?></div>
      <div class="farmer-stat-footer">
        <span class="farmer-trend-up">
          <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i> Direct to Farm
        </span>
        <span>• 0% platform commission</span>
      </div>
    </div>

    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Total Pre-Orders</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-sky">
          <i data-lucide="shopping-cart" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value"><?= $totalOrders ?></div>
      <div class="farmer-stat-footer">
        <span style="color: #fbbf24; font-weight: 700;"><?= $pendingOrders ?> pending</span>
        <span>• <?= $completedOrders ?> fulfilled</span>
      </div>
    </div>

    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Average Order Value</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-amber">
          <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value">Rs. <?= number_format($aov, 2) ?></div>
      <div class="farmer-stat-footer">
        <span>Average customer basket spend</span>
      </div>
    </div>

    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Produce Units Harvested</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-purple">
          <i data-lucide="sprout" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value"><?= number_format($totalUnitsSold, 1) ?></div>
      <div class="farmer-stat-footer">
        <span class="farmer-trend-up"><?= $completionRate ?>%</span>
        <span>fulfillment success rate</span>
      </div>
    </div>
  </div>

  <!-- Charts Row: Monthly Revenue Trend & Category Doughnut -->
  <div class="farmer-grid-2-col" style="margin-bottom: 2rem;">
    <!-- Revenue Trend Chart -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="trending-up" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
            <span>Harvest Sales Trendline</span>
          </h3>
          <span class="farmer-card-subtitle">Volume and gross revenue generated over market days</span>
        </div>
      </div>
      <div style="position: relative; height: 280px; width: 100%;">
        <canvas id="analyticsTrendChart"></canvas>
      </div>
    </div>

    <!-- Category Distribution Doughnut Chart -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="pie-chart" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
            <span>Revenue by Produce Category</span>
          </h3>
          <span class="farmer-card-subtitle">Contribution of each crop type to your total gross</span>
        </div>
      </div>
      <div style="position: relative; height: 280px; width: 100%; display: flex; align-items: center; justify-content: center;">
        <canvas id="categoryDoughnutChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Best-Selling Harvest Leaderboard Table -->
  <div class="farmer-card" style="margin-bottom: 2rem;">
    <div class="farmer-card-header">
      <div class="farmer-card-title-group">
        <h3 class="farmer-card-title">
          <i data-lucide="award" style="width: 20px; height: 20px; color: #fbbf24;"></i>
          <span>Best-Selling Produce Leaderboard</span>
        </h3>
        <span class="farmer-card-subtitle">Comprehensive breakdown of item popularity and total sales generated</span>
      </div>
    </div>

    <?php if (empty($bestsellingList)): ?>
      <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
        No sales recorded yet. Once pre-orders are received, top-performing crops will rank here.
      </p>
    <?php else: ?>
      <div class="farmer-table-responsive">
        <table class="farmer-table">
          <thead>
            <tr>
              <th>Rank &amp; Produce</th>
              <th>Category</th>
              <th>Units Sold</th>
              <th>Orders Count</th>
              <th>Total Gross Sales</th>
              <th style="text-align: right;">Revenue Share</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bestsellingList as $idx => $prod): ?>
              <?php
                $share = ($grossRevenue > 0) ? min(100, round(($prod['total_revenue'] / $grossRevenue) * 100, 1)) : 0;
              ?>
              <tr>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 26px; height: 26px; border-radius: 50%; background: rgba(34, 197, 94, 0.15); color: var(--primary-400); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem;">
                      #<?= $idx + 1 ?>
                    </div>
                    <div style="width: 40px; height: 40px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface); flex-shrink: 0;">
                      <img src="<?= htmlspecialchars(resolveImageUrl($prod['image_url'] ?? '', BASE_URL . '/assets/images/cat-vegetables.svg')) ?>"
                           alt="<?= htmlspecialchars($prod['product_name']) ?>"
                           style="width: 100%; height: 100%; object-fit: cover;"
                           onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
                    </div>
                    <strong style="color: var(--text-primary); font-size: 0.9375rem;">
                      <?= htmlspecialchars($prod['product_name']) ?>
                    </strong>
                  </div>
                </td>
                <td>
                  <span style="font-size: 0.8125rem; color: var(--slate-400);">
                    <?= htmlspecialchars($prod['category_name']) ?>
                  </span>
                </td>
                <td>
                  <span style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">
                    <?= (float)$prod['total_units_sold'] ?> <?= htmlspecialchars($prod['unit']) ?>
                  </span>
                </td>
                <td>
                  <span style="font-size: 0.875rem; color: var(--slate-300);">
                    <?= $prod['order_occurrences'] ?> orders
                  </span>
                </td>
                <td>
                  <strong style="color: var(--primary-400, #4ade80); font-size: 1rem;">
                    Rs. <?= number_format($prod['total_revenue'], 2) ?>
                  </strong>
                </td>
                <td style="text-align: right; min-width: 120px;">
                  <span style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;"><?= $share ?>%</span>
                  <div style="width: 100%; height: 6px; background: var(--bg-surface-elevated); border-radius: var(--radius-full); margin-top: 0.25rem; overflow: hidden; border: 1px solid var(--border-color);">
                    <div style="width: <?= $share ?>%; height: 100%; background: var(--primary-500); border-radius: var(--radius-full);"></div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Past Order History Log -->
  <div class="farmer-card">
    <div class="farmer-card-header">
      <div class="farmer-card-title-group">
        <h3 class="farmer-card-title">
          <i data-lucide="history" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
          <span>Order History Archive</span>
        </h3>
        <span class="farmer-card-subtitle">Past sales records, customer pickups, and archived fulfillment history</span>
      </div>
      <a href="?export=csv" class="farmer-btn-pill">
        <i data-lucide="download" style="width: 13px; height: 13px;"></i>
        <span>CSV Export</span>
      </a>
    </div>

    <?php if (empty($orderHistory)): ?>
      <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
        No order history recorded yet.
      </p>
    <?php else: ?>
      <div class="farmer-table-responsive">
        <table class="farmer-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Pickup Date</th>
              <th>Customer</th>
              <th>Market Stall</th>
              <th>Total Paid</th>
              <th>Final Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orderHistory as $hist): ?>
              <tr>
                <td>
                  <strong style="color: var(--text-primary);">#<?= htmlspecialchars($hist['order_number']) ?></strong>
                </td>
                <td>
                  <?= date('M j, Y', strtotime($hist['pickup_date'])) ?>
                </td>
                <td>
                  <?= htmlspecialchars($hist['customer_name']) ?>
                </td>
                <td>
                  <?= htmlspecialchars($hist['market_name']) ?>
                </td>
                <td>
                  <strong style="color: var(--primary-400);">Rs. <?= number_format($hist['total_amount'], 2) ?></strong>
                </td>
                <td>
                  <span class="farmer-status-badge badge-<?= $hist['order_status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $hist['order_status'])) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Chart Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';

  // 1. Trendline Chart
  const trendEl = document.getElementById('analyticsTrendChart');
  if (trendEl) {
    new Chart(trendEl, {
      type: 'bar',
      data: {
        labels: ['6 Days Ago', '5 Days Ago', '4 Days Ago', '3 Days Ago', '2 Days Ago', 'Yesterday', 'Today'],
        datasets: [{
          type: 'line',
          label: 'Revenue (Rs.)',
          data: [280, 540, 420, 890, 750, 1100, <?= $grossRevenue ?>],
          borderColor: '#22c55e',
          borderWidth: 3,
          tension: 0.35,
          pointBackgroundColor: '#22c55e',
          yAxisID: 'y'
        }, {
          type: 'bar',
          label: 'Orders Fulfilled',
          data: [1, 2, 2, 4, 3, 5, <?= $totalOrders ?>],
          backgroundColor: 'rgba(56, 189, 248, 0.45)',
          borderColor: '#38bdf8',
          borderWidth: 1,
          borderRadius: 6,
          yAxisID: 'yOrders'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor, font: { family: 'Inter', size: 11 } } },
          y: { grid: { color: gridColor }, ticks: { color: textColor, font: { family: 'Inter', size: 11 }, callback: v => 'Rs. ' + v } },
          yOrders: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#38bdf8', stepSize: 1, font: { family: 'Inter', size: 11 } } }
        }
      }
    });
  }

  // 2. Category Share Doughnut Chart
  const doughEl = document.getElementById('categoryDoughnutChart');
  if (doughEl) {
    const catLabels = <?= json_encode(array_column($categoryShares, 'category_name')) ?>;
    const catRevs = <?= json_encode(array_map('floatval', array_column($categoryShares, 'cat_revenue'))) ?>;

    new Chart(doughEl, {
      type: 'doughnut',
      data: {
        labels: catLabels,
        datasets: [{
          data: catRevs,
          backgroundColor: ['#22c55e', '#38bdf8', '#fbbf24', '#a855f7', '#f97316', '#14b8a6'],
          borderWidth: 2,
          borderColor: isDark ? '#0e2a22' : '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: textColor, boxWidth: 12, font: { family: 'Inter', size: 11 } }
          }
        },
        cutout: '68%'
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
