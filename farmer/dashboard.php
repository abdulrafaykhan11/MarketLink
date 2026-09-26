<?php
/**
 * MarketLink - Farmer / Seller Master Dashboard Console
 * Senior Full-Stack & UI/UX Silicon Valley Architecture
 */

$pageTitle = 'Dashboard Overview';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Fetch Core Performance KPI Metrics
// Total Revenue (Completed & Active Orders)
$revStmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders 
                          WHERE farmer_id = :fid AND order_status IN ('accepted', 'ready_for_pickup', 'completed')");
$revStmt->execute([':fid' => $farmerId]);
$totalRevenue = (float)$revStmt->fetchColumn();

// Total Fulfilled Orders Count
$compStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE farmer_id = :fid AND order_status = 'completed'");
$compStmt->execute([':fid' => $farmerId]);
$completedOrdersCount = (int)$compStmt->fetchColumn();

// Average Stall Rating & Review Count
$rateStmt = $pdo->prepare("SELECT COALESCE(AVG(rating), 5.0) as avg_rating, COUNT(*) as review_count 
                           FROM farmer_reviews WHERE farmer_id = :fid");
$rateStmt->execute([':fid' => $farmerId]);
$ratingData = $rateStmt->fetch();
$avgRating = round((float)$ratingData['avg_rating'], 1);
$reviewCount = (int)$ratingData['review_count'];

// Total Products Listed
$prodCountStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE farmer_id = :fid");
$prodCountStmt->execute([':fid' => $farmerId]);
$totalProductsCount = (int)$prodCountStmt->fetchColumn();

// 2. Fetch Recent Pre-Orders (Latest 6)
$ordersStmt = $pdo->prepare("SELECT o.*, cp.full_name as customer_name, u.phone_number as customer_phone,
                                    m.market_name, ps.start_time, ps.end_time,
                                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
                             FROM orders o
                             JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                             JOIN users u ON cp.customer_id = u.user_id
                             JOIN markets m ON o.market_id = m.market_id
                             LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                             WHERE o.farmer_id = :fid
                             ORDER BY o.order_id DESC LIMIT 6");
$ordersStmt->execute([':fid' => $farmerId]);
$recentOrders = $ordersStmt->fetchAll();

// 3. Fetch Best-Selling Products for this Farmer
$bestsellersStmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.unit, p.image_url, 
                                         pc.category_name,
                                         COALESCE(SUM(oi.quantity), 0) as total_sold,
                                         COALESCE(SUM(oi.subtotal), 0) as total_sales,
                                         COALESCE(wi.stock_quantity, 40) as stock_qty,
                                         COALESCE(wi.is_available, 1) as is_avail
                                  FROM products p
                                  JOIN product_categories pc ON p.category_id = pc.category_id
                                  LEFT JOIN order_items oi ON p.product_id = oi.product_id
                                  LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id
                                  WHERE p.farmer_id = :fid
                                  GROUP BY p.product_id
                                  ORDER BY total_sold DESC, p.product_id ASC LIMIT 4");
$bestsellersStmt->execute([':fid' => $farmerId]);
$bestsellingProducts = $bestsellersStmt->fetchAll();

// 4. Fetch Products for Quick Stock Switcher
$quickStockStmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.unit, p.image_url, 
                                        COALESCE(wi.price, 120.00) as price,
                                        COALESCE(wi.stock_quantity, 30) as stock_quantity,
                                        COALESCE(wi.is_available, 1) as is_available
                                 FROM products p
                                 LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id
                                 WHERE p.farmer_id = :fid
                                 GROUP BY p.product_id
                                 ORDER BY p.product_id ASC LIMIT 5");
$quickStockStmt->execute([':fid' => $farmerId]);
$quickStockProducts = $quickStockStmt->fetchAll();

// 5. Fetch Recent Customer Reviews
$reviewsStmt = $pdo->prepare("SELECT fr.*, cp.full_name as customer_name, o.order_number 
                              FROM farmer_reviews fr
                              JOIN customer_profiles cp ON fr.customer_id = cp.customer_id
                              JOIN orders o ON fr.order_id = o.order_id
                              WHERE fr.farmer_id = :fid
                              ORDER BY fr.created_at DESC LIMIT 3");
$reviewsStmt->execute([':fid' => $farmerId]);
$recentReviews = $reviewsStmt->fetchAll();

// 6. Fetch Platform Announcements for Farmers
$announceStmt = $pdo->query("SELECT * FROM platform_announcements WHERE target_role IN ('all', 'farmer') AND is_active = 1 ORDER BY announcement_id DESC LIMIT 2");
$announcements = $announceStmt->fetchAll();

// 7. Generate 7-Day Chart Data for Chart.js
$chartDates = [];
$chartRevenue = [];
$chartOrders = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDates[] = date('D, M j', strtotime($d));
    
    // Revenue on this date
    $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as rev, COUNT(*) as cnt 
                        FROM orders WHERE farmer_id = :fid AND DATE(pickup_date) = :d");
    $s->execute([':fid' => $farmerId, ':d' => $d]);
    $res = $s->fetch();
    $chartRevenue[] = (float)$res['rev'];
    $chartOrders[] = (int)$res['cnt'];
}
?>

<div class="farmer-content">

  <!-- Verification Alert Status Banner -->
  <?php if ($approvalStatus === 'pending'): ?>
    <div class="farmer-alert-banner farmer-alert-pending">
      <div style="display: flex; align-items: flex-start; gap: 1rem;">
        <div class="farmer-alert-icon-box">
          <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
        </div>
        <div>
          <div class="farmer-alert-title">Stall Registration Under Platform Review</div>
          <div class="farmer-alert-desc">
            Your stall profile for <strong><?= htmlspecialchars($stallName) ?></strong> has been received. MarketLink administrators are verifying your business documents. You can configure your products and weekly inventory now so your stall goes live immediately upon verification.
          </div>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/farmer/profile.php" class="farmer-btn-secondary" style="white-space: nowrap; font-size: 0.8125rem;">
        <span>Check Profile Details</span>
        <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
      </a>
    </div>
  <?php else: ?>
    <div class="farmer-alert-banner farmer-alert-verified">
      <div style="display: flex; align-items: flex-start; gap: 1rem;">
        <div class="farmer-alert-icon-box">
          <i data-lucide="badge-check" style="width: 24px; height: 24px;"></i>
        </div>
        <div>
          <div class="farmer-alert-title">Stall Verified &amp; Active on MarketLink Directory</div>
          <div class="farmer-alert-desc">
            Your producer account is in good standing. Pre-orders are open for upcoming market pickup slots. Remember to fulfill orders before your daily cutoff time at <strong><?= $cutoffFormatted ?></strong>.
          </div>
        </div>
      </div>
      <span class="farmer-badge-role" style="background: rgba(34, 197, 94, 0.2); color: var(--primary-400);">
        <i data-lucide="check" style="width: 12px; height: 12px;"></i> Verified Producer
      </span>
    </div>
  <?php endif; ?>

  <!-- Master Welcome Hero Banner -->
  <div class="farmer-hero-banner">
    <div class="farmer-hero-content">
      <div class="farmer-hero-badge">
        <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
        <span>Live Producer Console</span>
      </div>
      <h1 class="farmer-hero-title"><?= htmlspecialchars($stallName) ?></h1>
      <p class="farmer-hero-desc">
        Welcome back, <strong><?= htmlspecialchars($displayName) ?></strong>. Manage incoming customer pre-orders, allocate weekly harvest stock across market bays, and track pickup fulfilment in real-time.
      </p>
      <div class="farmer-hero-actions">
        <a href="<?= BASE_URL ?>/farmer/products.php?action=new" class="farmer-btn-primary">
          <i data-lucide="plus-circle" style="width: 17px; height: 17px;"></i>
          <span>Add New Harvest Item</span>
        </a>
        <a href="<?= BASE_URL ?>/farmer/orders.php" class="farmer-btn-secondary">
          <i data-lucide="shopping-bag" style="width: 17px; height: 17px;"></i>
          <span>View Pre-Orders (<?= $activeOrdersCount ?> Active)</span>
        </a>
        <a href="<?= BASE_URL ?>/farmer/pickup-slots.php" class="farmer-btn-secondary">
          <i data-lucide="calendar" style="width: 17px; height: 17px;"></i>
          <span>Manage Pickup Slots</span>
        </a>
      </div>
    </div>
    <div class="farmer-hero-illustration">
      <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="90" fill="url(#hero-circle-grad)" fill-opacity="0.15"/>
        <path d="M60 140L100 70L140 140H60Z" fill="url(#hero-mountain-grad)" fill-opacity="0.3"/>
        <circle cx="145" cy="65" r="22" fill="#fbbf24" fill-opacity="0.85"/>
        <path d="M40 155C70 145 130 145 160 155" stroke="var(--primary-400)" stroke-width="6" stroke-linecap="round"/>
        <path d="M30 170C80 155 120 155 170 170" stroke="var(--primary-500)" stroke-width="8" stroke-linecap="round"/>
        <defs>
          <linearGradient id="hero-circle-grad" x1="10" y1="10" x2="190" y2="190" gradientUnits="userSpaceOnUse">
            <stop stop-color="#38bdf8"/>
            <stop offset="1" stop-color="#22c55e"/>
          </linearGradient>
          <linearGradient id="hero-mountain-grad" x1="60" y1="70" x2="140" y2="140" gradientUnits="userSpaceOnUse">
            <stop stop-color="#22c55e"/>
            <stop offset="1" stop-color="#0284c7"/>
          </linearGradient>
        </defs>
      </svg>
    </div>
  </div>

  <!-- 4 Key Performance Metric KPI Cards -->
  <div class="farmer-stats-grid">
    <!-- 1. Total Revenue -->
    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Total Stall Revenue</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-emerald">
          <i data-lucide="dollar-sign" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value">Rs. <?= number_format($totalRevenue, 2) ?></div>
      <div class="farmer-stat-footer">
        <span class="farmer-trend-up">
          <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i> Active
        </span>
        <span>• Paid in-person at stall</span>
      </div>
    </div>

    <!-- 2. Active Pre-Orders -->
    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Active Pre-Orders</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-sky">
          <i data-lucide="shopping-bag" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value"><?= $activeOrdersCount ?></div>
      <div class="farmer-stat-footer">
        <?php if ($placedOrdersCount > 0): ?>
          <span style="color: #fbbf24; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
            <span class="pulse-dot-amber"></span> <?= $placedOrdersCount ?> awaiting acceptance
          </span>
        <?php else: ?>
          <span class="farmer-trend-neutral">All orders confirmed</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- 3. Fulfilled Orders -->
    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Completed Pickups</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-purple">
          <i data-lucide="package-check" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value"><?= $completedOrdersCount ?></div>
      <div class="farmer-stat-footer">
        <span class="farmer-trend-up">
          <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i> 100%
        </span>
        <span>Customer collection rate</span>
      </div>
    </div>

    <!-- 4. Stall Rating -->
    <div class="farmer-stat-card">
      <div class="farmer-stat-header">
        <span class="farmer-stat-label">Stall Customer Rating</span>
        <div class="farmer-stat-icon-wrapper farmer-icon-amber">
          <i data-lucide="star" style="width: 22px; height: 22px;"></i>
        </div>
      </div>
      <div class="farmer-stat-value" style="display: flex; align-items: baseline; gap: 0.4rem;">
        <?= $avgRating ?>
        <span style="font-size: 1.1rem; color: #fbbf24;">★</span>
        <span style="font-size: 0.875rem; color: var(--slate-400); font-weight: 500;">/ 5.0</span>
      </div>
      <div class="farmer-stat-footer">
        <span>Based on <?= $reviewCount ?> verified customer review<?= $reviewCount !== 1 ? 's' : '' ?></span>
      </div>
    </div>
  </div>

  <!-- Section: Sales Analytics Chart & Next Upcoming Market Pickup Window -->
  <div class="farmer-grid-2-col">
    <!-- Chart Card -->
    <div class="farmer-card farmer-analytics-chart">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="bar-chart-3" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
            <span>Pre-Order &amp; Sales Velocity</span>
          </h3>
          <span class="farmer-card-subtitle">Daily harvest demand and revenue trends over the past 7 days</span>
        </div>
        <div class="farmer-card-actions">
          <span class="farmer-btn-pill active" id="chartToggleWeekly">Last 7 Days</span>
        </div>
      </div>
      <div style="position: relative; height: 290px; width: 100%;">
        <canvas id="farmerSalesChart"></canvas>
      </div>
    </div>

    <!-- Next Upcoming Market Window & Stall Schedule Card -->
    <div class="farmer-card" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div class="farmer-card-header" style="margin-bottom: 1rem;">
          <div class="farmer-card-title-group">
            <h3 class="farmer-card-title">
              <i data-lucide="map-pin" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
              <span>Active Market Stall</span>
            </h3>
            <span class="farmer-card-subtitle">Your allocated physical pickup bays</span>
          </div>
        </div>

        <?php if (!empty($assignedStalls)): ?>
          <?php foreach ($assignedStalls as $idx => $st): ?>
            <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.15rem; margin-bottom: 0.85rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <strong style="color: var(--text-primary); font-size: 0.9375rem;"><?= htmlspecialchars($st['market_name']) ?></strong>
                <span class="farmer-badge-role" style="font-size: 0.65rem;">Active</span>
              </div>
              <div style="font-size: 0.8125rem; color: var(--slate-400); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                <i data-lucide="navigation" style="width: 13px; height: 13px; color: var(--primary-500);"></i>
                <span><?= htmlspecialchars($st['stall_number_location'] ?: 'Main Produce Aisle') ?></span>
              </div>
              <div style="font-size: 0.78125rem; color: var(--primary-400); font-weight: 600;">
                📅 Days: <?= htmlspecialchars($st['operating_days'] ?: 'Saturday, Sunday') ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem 1rem; color: var(--slate-400);">
            <i data-lucide="alert-circle" style="width: 36px; height: 36px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
            <p style="font-size: 0.875rem;">No markets assigned yet. MarketLink admin will assign stall bays upon verification.</p>
          </div>
        <?php endif; ?>
      </div>

      <div style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
        <a href="<?= BASE_URL ?>/farmer/profile.php" class="farmer-btn-secondary" style="width: 100%; justify-content: center;">
          <i data-lucide="settings" style="width: 15px; height: 15px;"></i>
          <span>Configure Stall Details &amp; Map Pin</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Live Pre-Orders Fulfillment Pipeline (Today & Upcoming) -->
  <div class="farmer-card" style="margin-bottom: 2rem;">
    <div class="farmer-card-header">
      <div class="farmer-card-title-group">
        <h3 class="farmer-card-title">
          <i data-lucide="inbox" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
          <span>Live Pre-Orders Fulfillment Queue</span>
        </h3>
        <span class="farmer-card-subtitle">Incoming and active pre-orders scheduled for stall pickup</span>
      </div>
      <div class="farmer-card-actions">
        <a href="<?= BASE_URL ?>/farmer/orders.php" class="farmer-btn-pill">
          <span>View All Orders (<?= $activeOrdersCount ?>)</span>
          <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
      </div>
    </div>

    <?php if (empty($recentOrders)): ?>
      <div style="text-align: center; padding: 3rem 1.5rem; color: var(--slate-400);">
        <i data-lucide="package-open" style="width: 48px; height: 48px; margin-bottom: 0.75rem; color: var(--slate-500);"></i>
        <h4 style="color: var(--text-primary); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem;">No Active Orders Yet</h4>
        <p style="font-size: 0.875rem; max-width: 450px; margin: 0 auto 1.25rem;">
          When local customers browse your products and reserve a pickup window, their pre-orders will arrive here immediately.
        </p>
        <a href="<?= BASE_URL ?>/farmer/products.php?action=new" class="farmer-btn-primary">
          <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
          <span>Post Produce to Public Catalog</span>
        </a>
      </div>
    <?php else: ?>
      <!-- Responsive 2-column card grid — no horizontal scrollbar on any screen -->
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 460px), 1fr)); gap: 1rem;">
        <?php foreach ($recentOrders as $o):
          $statusClass   = 'badge-' . $o['order_status'];
          $displayStatus = ucfirst(str_replace('_', ' ', $o['order_status']));
          $statusColor   = match($o['order_status']) {
            'placed'           => 'rgba(251,191,36,0.12)',
            'accepted'         => 'rgba(56,189,248,0.10)',
            'ready_for_pickup' => 'rgba(168,85,247,0.12)',
            'completed'        => 'rgba(34,197,94,0.10)',
            default            => 'var(--bg-surface-elevated)'
          };
        ?>
          <!-- Order Card -->
          <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.1rem 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">

            <!-- Row 1: Order ref + status + amount -->
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
              <div>
                <div style="font-family: var(--font-heading); font-weight: 800; color: var(--text-primary); font-size: 1rem;">
                  #<?= htmlspecialchars($o['order_number']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--slate-400); margin-top: 0.15rem;">
                  <?= date('M j, Y · g:i A', strtotime($o['created_at'])) ?>
                </div>
              </div>
              <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                <span class="farmer-status-badge <?= $statusClass ?>">
                  <?php if ($o['order_status'] === 'placed'): ?>
                    <span class="pulse-dot-amber"></span>
                  <?php elseif ($o['order_status'] === 'accepted'): ?>
                    <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                  <?php elseif ($o['order_status'] === 'ready_for_pickup'): ?>
                    <i data-lucide="package" style="width: 12px; height: 12px;"></i>
                  <?php elseif ($o['order_status'] === 'completed'): ?>
                    <i data-lucide="check-check" style="width: 12px; height: 12px;"></i>
                  <?php endif; ?>
                  <?= $displayStatus ?>
                </span>
                <strong style="color: var(--primary-400); font-size: 1rem; font-family: var(--font-heading);">
                  Rs. <?= number_format($o['total_amount'], 2) ?>
                </strong>
              </div>
            </div>

            <!-- Row 2: Customer + Pickup + Market + Items -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 1rem; padding: 0.75rem; background: <?= $statusColor ?>; border-radius: var(--radius-md); border: 1px solid var(--border-subtle, rgba(255,255,255,0.05));">
              <!-- Customer -->
              <div>
                <div style="font-size: 0.6875rem; color: var(--slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.2rem;">Customer</div>
                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;"><?= htmlspecialchars($o['customer_name']) ?></div>
                <div style="font-size: 0.72rem; color: var(--slate-400); display: flex; align-items: center; gap: 0.25rem;">
                  <i data-lucide="phone" style="width: 10px; height: 10px;"></i>
                  <?= htmlspecialchars($o['customer_phone'] ?: 'N/A') ?>
                </div>
              </div>

              <!-- Market -->
              <div>
                <div style="font-size: 0.6875rem; color: var(--slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.2rem;">Market</div>
                <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem; display: flex; align-items: center; gap: 0.3rem;">
                  <i data-lucide="map-pin" style="width: 12px; height: 12px; color: var(--primary-500); flex-shrink: 0;"></i>
                  <?= htmlspecialchars($o['market_name']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--slate-400);">
                  <span style="background: var(--bg-surface); border: 1px solid var(--border-color); padding: 0.1rem 0.45rem; border-radius: var(--radius-md); font-weight: 600;"><?= $o['items_count'] ?> item<?= $o['items_count'] !== 1 ? 's' : '' ?></span>
                </div>
              </div>

              <!-- Pickup Date (spans full width) -->
              <div style="grid-column: 1 / -1; padding-top: 0.5rem; border-top: 1px solid var(--border-subtle, rgba(255,255,255,0.05)); display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <i data-lucide="calendar-clock" style="width: 14px; height: 14px; color: var(--primary-500); flex-shrink: 0;"></i>
                <span style="font-weight: 700; color: var(--text-primary); font-size: 0.84375rem;"><?= date('D, M j, Y', strtotime($o['pickup_date'])) ?></span>
                <?php if (!empty($o['start_time'])): ?>
                  <span style="font-size: 0.75rem; color: var(--slate-400);">· <?= date('g:i A', strtotime($o['start_time'])) ?> – <?= date('g:i A', strtotime($o['end_time'])) ?></span>
                <?php endif; ?>
                <span style="font-size: 0.68rem; color: var(--slate-500); margin-left: auto;">Pay at stall</span>
              </div>
            </div>

            <!-- Row 3: Quick Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; padding-top: 0.25rem;">
              <?php if ($o['order_status'] === 'placed'): ?>
                <button type="button" class="farmer-btn-action btn-action-accept" onclick="FarmerApp.updateOrderStatus(<?= $o['order_id'] ?>, 'accepted')" title="Accept this pre-order">
                  <i data-lucide="check" style="width: 13px; height: 13px;"></i>
                  <span>Accept</span>
                </button>
                <button type="button" class="farmer-btn-action btn-action-decline" onclick="if(confirm('Decline this order?')) FarmerApp.updateOrderStatus(<?= $o['order_id'] ?>, 'declined', 'Out of stock')" title="Decline order">
                  <i data-lucide="x" style="width: 13px; height: 13px;"></i>
                  <span>Decline</span>
                </button>
              <?php elseif ($o['order_status'] === 'accepted'): ?>
                <button type="button" class="farmer-btn-action btn-action-ready" onclick="FarmerApp.updateOrderStatus(<?= $o['order_id'] ?>, 'ready_for_pickup')" title="Mark freshly packed and ready">
                  <i data-lucide="package" style="width: 13px; height: 13px;"></i>
                  <span>Mark Ready</span>
                </button>
              <?php elseif ($o['order_status'] === 'ready_for_pickup'): ?>
                <button type="button" class="farmer-btn-action btn-action-complete" onclick="FarmerApp.updateOrderStatus(<?= $o['order_id'] ?>, 'completed')" title="Customer collected and paid">
                  <i data-lucide="check-check" style="width: 13px; height: 13px;"></i>
                  <span>Mark Fulfilled</span>
                </button>
              <?php endif; ?>
              <a href="<?= BASE_URL ?>/farmer/orders.php?view_order=<?= $o['order_id'] ?>" class="farmer-btn-action btn-action-view" title="View Full Order">
                <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                <span>Details</span>
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Dual Grid: Best Selling Produce & Quick Inventory Availability Switcher -->
  <div class="farmer-grid-equal-2">
    <!-- Best-Selling Produce Leaderboard -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="award" style="width: 20px; height: 20px; color: #fbbf24;"></i>
            <span>Top Harvest Leaderboard</span>
          </h3>
          <span class="farmer-card-subtitle">Your most demanded produce ranked by customer pre-orders</span>
        </div>
        <a href="<?= BASE_URL ?>/farmer/products.php" class="farmer-btn-pill">
          <span>Manage All</span>
        </a>
      </div>

      <?php if (empty($bestsellingProducts)): ?>
        <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
          No sales recorded yet. Once pre-orders are placed, your top produce will rank here.
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
          <?php foreach ($bestsellingProducts as $idx => $prod): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
              <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(34, 197, 94, 0.15); color: var(--primary-400); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8125rem;">
                  #<?= $idx + 1 ?>
                </div>
                <div style="width: 44px; height: 44px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface); flex-shrink: 0;">
                  <img src="<?= htmlspecialchars(resolveImageUrl($prod['image_url'] ?? '', BASE_URL . '/assets/images/cat-vegetables.svg')) ?>"
                       alt="<?= htmlspecialchars($prod['product_name']) ?>"
                       style="width: 100%; height: 100%; object-fit: cover;"
                       onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
                </div>
                <div>
                  <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;"><?= htmlspecialchars($prod['product_name']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--slate-400);"><?= htmlspecialchars($prod['category_name']) ?> • <?= $prod['unit'] ?></div>
                </div>
              </div>
              <div style="text-align: right;">
                <div style="font-weight: 800; color: var(--primary-400); font-size: 0.9375rem;">
                  Rs. <?= number_format($prod['total_sales'], 2) ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--slate-400);">
                  <?= $prod['total_sold'] ?> units reserved
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Stock Availability Switcher -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="toggle-left" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
            <span>Instant Stock Availability</span>
          </h3>
          <span class="farmer-card-subtitle">Toggle products in-stock or sold-out with zero page reload</span>
        </div>
        <a href="<?= BASE_URL ?>/farmer/products.php" class="farmer-btn-pill">
          <span>Catalog (<?= $totalProductsCount ?>)</span>
        </a>
      </div>

      <?php if (empty($quickStockProducts)): ?>
        <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
          No produce items added yet. Click "+ Add Produce" to get started!
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
          <?php foreach ($quickStockProducts as $qp): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface); flex-shrink: 0;">
                  <img src="<?= htmlspecialchars(resolveImageUrl($qp['image_url'] ?? '', BASE_URL . '/assets/images/cat-vegetables.svg')) ?>"
                       alt="<?= htmlspecialchars($qp['product_name']) ?>"
                       style="width: 100%; height: 100%; object-fit: cover;"
                       onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
                </div>
                <div>
                  <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;"><?= htmlspecialchars($qp['product_name']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--slate-400);">
                    Rs. <?= number_format($qp['price'], 2) ?> / <?= htmlspecialchars($qp['unit']) ?> • <?= (float)$qp['stock_quantity'] ?> <?= htmlspecialchars($qp['unit']) ?> weekly
                  </div>
                </div>
              </div>

              <!-- iOS Style Switch -->
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; color: <?= $qp['is_available'] ? 'var(--primary-400)' : 'var(--slate-400)' ?>; font-weight: 600;" id="stockLabel_<?= $qp['product_id'] ?>">
                  <?= $qp['is_available'] ? 'Available' : 'Sold Out' ?>
                </span>
                <label class="farmer-toggle-switch">
                  <input type="checkbox" <?= $qp['is_available'] ? 'checked' : '' ?> onchange="
                    const checked = this.checked;
                    document.getElementById('stockLabel_<?= $qp['product_id'] ?>').textContent = checked ? 'Available' : 'Sold Out';
                    document.getElementById('stockLabel_<?= $qp['product_id'] ?>').style.color = checked ? 'var(--primary-400)' : 'var(--slate-400)';
                    FarmerApp.toggleStock(<?= $qp['product_id'] ?>, checked);
                  ">
                  <span class="farmer-toggle-slider"></span>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Customer Feedback & Announcements Section -->
  <div class="farmer-grid-2-col" id="announcements">
    <!-- Customer Reviews -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="message-square" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
            <span>Recent Shopper Reviews</span>
          </h3>
          <span class="farmer-card-subtitle">Verified customer feedback left on your stall</span>
        </div>
        <a href="<?= BASE_URL ?>/farmer/reviews.php" class="farmer-btn-pill">
          <span>View All (<?= $reviewCount ?>)</span>
        </a>
      </div>

      <?php if (empty($recentReviews)): ?>
        <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
          No customer reviews left yet. Provide fresh harvest and great pickup service to earn 5-star ratings!
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
          <?php foreach ($recentReviews as $rev): ?>
            <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.15rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <strong style="color: var(--text-primary); font-size: 0.875rem;"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                  <span style="font-size: 0.75rem; color: #fbbf24;">
                    <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                  </span>
                </div>
                <span style="font-size: 0.75rem; color: var(--slate-400);"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
              </div>
              <p style="font-size: 0.8125rem; color: var(--slate-300); line-height: 1.5; margin-bottom: 0.5rem;">
                "<?= htmlspecialchars($rev['review_comment']) ?>"
              </p>

              <?php if (!empty($rev['farmer_response'])): ?>
                <div style="background: rgba(34, 197, 94, 0.08); border-left: 3px solid var(--primary-500); padding: 0.5rem 0.75rem; border-radius: 0 var(--radius-md) var(--radius-md) 0; font-size: 0.78125rem; color: var(--primary-300);">
                  <strong>Your Reply:</strong> <?= htmlspecialchars($rev['farmer_response']) ?>
                </div>
              <?php else: ?>
                <button type="button" class="farmer-btn-action btn-action-view" onclick="openReplyModal(<?= $rev['review_id'] ?>, '<?= htmlspecialchars(addslashes($rev['customer_name'])) ?>', '<?= htmlspecialchars(addslashes($rev['review_comment'])) ?>')" style="font-size: 0.75rem;">
                  <i data-lucide="corner-down-right" style="width: 13px; height: 13px;"></i>
                  <span>Reply to Shopper</span>
                </button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Platform Announcements Banner from Super Admin -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="megaphone" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
            <span>MarketLink Bulletins</span>
          </h3>
          <span class="farmer-card-subtitle">Official administrative updates for verified growers</span>
        </div>
      </div>

      <?php if (empty($announcements)): ?>
        <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
          No active platform announcements at this time.
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
          <?php foreach ($announcements as $ann): ?>
            <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.15rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                <strong style="color: var(--text-primary); font-size: 0.9375rem;"><?= htmlspecialchars($ann['title']) ?></strong>
                <span style="font-size: 0.7rem; color: var(--slate-400);"><?= date('M j', strtotime($ann['created_at'])) ?></span>
              </div>
              <p style="font-size: 0.8125rem; color: var(--slate-300); line-height: 1.5; margin: 0;">
                <?= nl2br(htmlspecialchars($ann['content'])) ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Modal: Reply to Customer Review -->
<div class="farmer-modal-backdrop" id="reviewReplyModal">
  <div class="farmer-modal">
    <div class="farmer-modal-header">
      <h4 class="farmer-modal-title">
        <i data-lucide="message-circle" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
        <span>Reply to Customer Review</span>
      </h4>
      <button type="button" class="farmer-modal-close-btn" onclick="closeReplyModal()">✕</button>
    </div>
    <form id="replyForm" onsubmit="submitFarmerReply(event)">
      <div class="farmer-modal-body">
        <input type="hidden" name="review_id" id="replyReviewId">
        <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary);" id="replyCustomerName"></div>
          <div style="font-size: 0.8125rem; color: var(--slate-300); margin-top: 0.25rem;" id="replyCustomerComment"></div>
        </div>

        <div class="farmer-form-group">
          <label class="farmer-form-label" for="replyText">Your Producer Response <span style="color:#ef4444;">*</span></label>
          <textarea class="farmer-form-textarea" id="replyText" name="farmer_response" rows="4" placeholder="Thank the customer or address their note with courteous professionalism..." required></textarea>
        </div>
      </div>
      <div class="farmer-modal-footer">
        <button type="button" class="farmer-btn-secondary" onclick="closeReplyModal()">Cancel</button>
        <button type="submit" class="farmer-btn-primary" id="replySubmitBtn">
          <span>Publish Official Reply</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Chart.js Setup Script & Interactive Handlers -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Chart.js Setup for Sales & Pre-Orders Velocity
  const ctx = document.getElementById('farmerSalesChart');
  if (ctx) {
    const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(34, 197, 94, 0.35)');
    gradient.addColorStop(1, 'rgba(34, 197, 94, 0.0)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($chartDates) ?>,
        datasets: [{
          label: 'Revenue (Rs.)',
          data: <?= json_encode($chartRevenue) ?>,
          borderColor: '#22c55e',
          borderWidth: 3,
          backgroundColor: gradient,
          fill: true,
          tension: 0.38,
          pointBackgroundColor: '#22c55e',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6
        }, {
          label: 'Orders Count',
          data: <?= json_encode($chartOrders) ?>,
          borderColor: '#38bdf8',
          borderWidth: 2,
          borderDash: [5, 5],
          backgroundColor: 'transparent',
          fill: false,
          tension: 0.3,
          pointBackgroundColor: '#38bdf8',
          pointRadius: 3,
          yAxisID: 'yOrders'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: textColor,
              boxWidth: 12,
              font: { family: 'Inter', size: 12 }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#ffffff',
            bodyColor: '#cbd5e1',
            padding: 12,
            borderRadius: 8,
            boxPadding: 4
          }
        },
        scales: {
          x: {
            grid: { color: gridColor },
            ticks: { color: textColor, font: { family: 'Inter', size: 11 } }
          },
          y: {
            grid: { color: gridColor },
            ticks: {
              color: textColor,
              font: { family: 'Inter', size: 11 },
              callback: function(val) { return 'Rs. ' + val; }
            }
          },
          yOrders: {
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: {
              color: '#38bdf8',
              stepSize: 1,
              font: { family: 'Inter', size: 11 }
            }
          }
        }
      }
    });
  }
});

// Modal Logic for Review Reply
function openReplyModal(reviewId, customerName, comment) {
  document.getElementById('replyReviewId').value = reviewId;
  document.getElementById('replyCustomerName').textContent = customerName;
  document.getElementById('replyCustomerComment').textContent = `"${comment}"`;
  document.getElementById('reviewReplyModal').classList.add('active');
}

function closeReplyModal() {
  document.getElementById('reviewReplyModal').classList.remove('active');
}

function submitFarmerReply(e) {
  e.preventDefault();
  const form = document.getElementById('replyForm');
  const btn = document.getElementById('replySubmitBtn');
  btn.disabled = true;

  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/farmer/api/save_reply.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Reply Saved', data.message);
      closeReplyModal();
      setTimeout(() => location.reload(), 800);
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Could not post reply.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Network Error', 'Please check server connection.');
  });
}
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
