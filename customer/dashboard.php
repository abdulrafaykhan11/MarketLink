<?php
/**
 * MarketLink - Customer Dashboard Portal Overview
 * Aligned with SRS specifications, DB schema, and modern emerald UI theme.
 */

$pageTitle = 'Dashboard Overview';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/customer_header.php';

// 1. Fetch KPI metrics for this customer
// Active Orders Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :uid AND order_status IN ('placed', 'accepted', 'ready_for_pickup')");
$stmt->execute([':uid' => $currentUserId]);
$activeOrdersCount = (int)$stmt->fetchColumn();

// Total Orders Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :uid");
$stmt->execute([':uid' => $currentUserId]);
$totalOrdersCount = (int)$stmt->fetchColumn();

// Favorites Count (Farmers + Products)
$stmt = $pdo->prepare("SELECT (SELECT COUNT(*) FROM favorite_farmers WHERE customer_id = :uid1) + 
                              (SELECT COUNT(*) FROM favorite_products WHERE customer_id = :uid2)");
$stmt->execute([':uid1' => $currentUserId, ':uid2' => $currentUserId]);
$totalFavorites = (int)$stmt->fetchColumn();

// Next Upcoming Pickup Date
$stmt = $pdo->prepare("SELECT o.order_number, o.pickup_date, ps.start_time, ps.end_time, fp.stall_name, m.market_name 
                       FROM orders o
                       JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                       JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                       JOIN markets m ON o.market_id = m.market_id
                       WHERE o.customer_id = :uid AND o.order_status IN ('placed', 'accepted', 'ready_for_pickup')
                       ORDER BY o.pickup_date ASC, ps.start_time ASC LIMIT 1");
$stmt->execute([':uid' => $currentUserId]);
$nextPickup = $stmt->fetch();

// 2. Fetch Latest Active Order for Stepper Display
$stmt = $pdo->prepare("SELECT o.*, fp.stall_name, fp.contact_person, fp.business_phone, m.market_name, m.address as market_address,
                              ps.start_time, ps.end_time
                       FROM orders o
                       JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                       JOIN markets m ON o.market_id = m.market_id
                       JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                       WHERE o.customer_id = :uid 
                       ORDER BY o.order_id DESC LIMIT 1");
$stmt->execute([':uid' => $currentUserId]);
$latestOrder = $stmt->fetch();

// If latest order exists, fetch its items
$orderItems = [];
if ($latestOrder) {
    $itemStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.unit, p.image_url 
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.product_id
                               WHERE oi.order_id = :oid");
    $itemStmt->execute([':oid' => $latestOrder['order_id']]);
    $orderItems = $itemStmt->fetchAll();
}

// 3. Fetch Featured Fresh Harvest Products (with weekly inventory prices)
$featuredStmt = $pdo->query("SELECT p.product_id, p.product_name, p.unit, p.image_url, 
                                    pc.category_name, fp.stall_name, wi.stall_id, wi.price, wi.stock_quantity,
                                    (SELECT COUNT(*) FROM favorite_products fp WHERE fp.customer_id = {$currentUserId} AND fp.product_id = p.product_id) as is_fav
                             FROM products p
                             JOIN product_categories pc ON p.category_id = pc.category_id
                             JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                             JOIN weekly_inventory wi ON p.product_id = wi.product_id
                             WHERE wi.is_available = 1 AND fp.approval_status = 'approved'
                             GROUP BY p.product_id
                             ORDER BY p.product_id ASC LIMIT 4");
$featuredProducts = $featuredStmt->fetchAll();

// 4. Fetch Active Markets List
$marketsStmt = $pdo->query("SELECT market_id, market_name, address, operating_days, operating_hours FROM markets WHERE status = 'active' ORDER BY market_id ASC LIMIT 3");
$upcomingMarkets = $marketsStmt->fetchAll();
?>

<!-- Welcome Banner -->
<div class="customer-welcome-banner">
  <div class="banner-content">
    <div class="banner-badge">
      <span>🌿</span> Farm-Fresh Just A Click Away
    </div>
    <h1 class="banner-title">Welcome back, <?= htmlspecialchars($displayName) ?>!</h1>
    <p class="banner-desc">
      Connecting you directly with local sustainable growers. Pre-order your weekly seasonal harvest, reserve market pickup slots, and skip middleman markups.
    </p>
    <div class="banner-actions">
      <a href="<?= BASE_URL ?>/customer/products.php" class="banner-btn-primary">
        <span>🥬</span> Browse Fresh Harvest
      </a>
      <a href="<?= BASE_URL ?>/customer/markets.php" class="banner-btn-secondary">
        <span>📍</span> View Market Map
      </a>
      <a href="<?= BASE_URL ?>/customer/orders.php" class="banner-btn-secondary">
        <span>📦</span> Track Pre-Orders
      </a>
    </div>
  </div>
  <div class="banner-art">🚜</div>
</div>

<!-- 4 Key Stats Metrics -->
<div class="stats-grid">
  <!-- Active Orders -->
  <div class="stat-card">
    <div class="stat-icon-wrapper stat-icon-green">
      📦
    </div>
    <div>
      <div class="stat-label">Active Pre-Orders</div>
      <div class="stat-value"><?= $activeOrdersCount ?></div>
      <div class="stat-trend"><?= $activeOrdersCount > 0 ? 'Harvest ready / processing' : 'No pending orders' ?></div>
    </div>
  </div>

  <!-- Next Pickup -->
  <div class="stat-card">
    <div class="stat-icon-wrapper stat-icon-blue">
      🗓️
    </div>
    <div>
      <div class="stat-label">Next Scheduled Pickup</div>
      <div class="stat-value" style="font-size: 1.25rem;">
        <?= $nextPickup ? date('M d, Y', strtotime($nextPickup['pickup_date'])) : 'None Set' ?>
      </div>
      <div class="stat-trend">
        <?= $nextPickup ? htmlspecialchars($nextPickup['stall_name']) : 'Reserve slot at checkout' ?>
      </div>
    </div>
  </div>

  <!-- Saved Favorites -->
  <div class="stat-card">
    <div class="stat-icon-wrapper stat-icon-amber">
      ❤️
    </div>
    <div>
      <div class="stat-label">Saved Favorites</div>
      <div class="stat-value"><?= $totalFavorites ?></div>
      <div class="stat-trend">Farmers & Produce bookmarked</div>
    </div>
  </div>

  <!-- Cart Items -->
  <div class="stat-card">
    <div class="stat-icon-wrapper stat-icon-purple">
      🛒
    </div>
    <div>
      <div class="stat-label">Pre-Order Basket</div>
      <div class="stat-value cart-count-badge"><?= $cartItemCount ?></div>
      <div class="stat-trend"><?= $cartItemCount > 0 ? 'Ready for slot reservation' : 'Basket currently empty' ?></div>
    </div>
  </div>
</div>

<!-- 2-Column Dashboard Grid: Active Order Status + Upcoming Markets -->
<div class="dashboard-grid-2">

  <!-- Left: Live Order Status Stepper -->
  <div class="portal-card">
    <div class="portal-card-header">
      <h3 class="portal-card-title">
        <span>🌱</span> Current Order Pipeline
      </h3>
      <?php if ($latestOrder): ?>
        <a href="<?= BASE_URL ?>/customer/orders.php" class="portal-card-link">View All Orders (<?= $totalOrdersCount ?>) ➔</a>
      <?php endif; ?>
    </div>

    <?php if ($latestOrder): ?>
      <?php
        $status = $latestOrder['order_status'];
        $steps = ['placed', 'accepted', 'ready_for_pickup', 'completed'];
        $currIndex = array_search($status, $steps);
        if ($currIndex === false) $currIndex = -1; // cancelled or declined
      ?>

      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; margin-bottom:1rem;">
        <div>
          <span style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Order Number</span>
          <div style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; color:var(--text-primary);">
            #<?= htmlspecialchars($latestOrder['order_number']) ?>
          </div>
        </div>
        <div>
          <span class="order-status-badge status-<?= htmlspecialchars($status) ?>">
            ● <?= ucwords(str_replace('_', ' ', $status)) ?>
          </span>
        </div>
      </div>

      <!-- Visual 4-Step Stepper -->
      <div class="order-stepper">
        <div class="order-step <?= ($currIndex >= 0) ? ($currIndex > 0 ? 'completed' : 'active') : '' ?>">
          <div class="order-step-bubble"><?= ($currIndex > 0) ? '✔' : '1' ?></div>
          <span class="order-step-title">Order Placed</span>
        </div>

        <div class="order-step <?= ($currIndex >= 1) ? ($currIndex > 1 ? 'completed' : 'active') : '' ?>">
          <div class="order-step-bubble"><?= ($currIndex > 1) ? '✔' : '2' ?></div>
          <span class="order-step-title">Farmer Accepted</span>
        </div>

        <div class="order-step <?= ($currIndex >= 2) ? ($currIndex > 2 ? 'completed' : 'active') : '' ?>">
          <div class="order-step-bubble"><?= ($currIndex > 2) ? '✔' : '3' ?></div>
          <span class="order-step-title">Ready For Pickup</span>
        </div>

        <div class="order-step <?= ($currIndex >= 3) ? 'completed' : '' ?>">
          <div class="order-step-bubble">4</div>
          <span class="order-step-title">Picked Up & Settled</span>
        </div>
      </div>

      <!-- Order Details Summary -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.25rem; margin-top:1.5rem;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; font-size:0.85rem;">
          <div>
            <span style="color:var(--text-muted); display:block; font-size:0.75rem;">FARMER STALL</span>
            <strong style="color:var(--text-primary);"><?= htmlspecialchars($latestOrder['stall_name']) ?></strong>
          </div>
          <div>
            <span style="color:var(--text-muted); display:block; font-size:0.75rem;">PICKUP LOCATION</span>
            <strong style="color:var(--text-primary);"><?= htmlspecialchars($latestOrder['market_name']) ?></strong>
          </div>
          <div>
            <span style="color:var(--text-muted); display:block; font-size:0.75rem;">SCHEDULED TIME</span>
            <strong style="color:var(--text-primary);"><?= date('D, M d', strtotime($latestOrder['pickup_date'])) ?> (<?= date('h:i A', strtotime($latestOrder['start_time'])) ?>)</strong>
          </div>
          <div>
            <span style="color:var(--text-muted); display:block; font-size:0.75rem;">TOTAL (PAY AT STALL)</span>
            <strong style="color:var(--emerald-400); font-size:1.05rem;">Rs. <?= number_format($latestOrder['total_amount'], 2) ?></strong>
          </div>
        </div>

        <!-- Items preview -->
        <?php if (!empty($orderItems)): ?>
          <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px dashed var(--border-color); display:flex; gap:0.5rem; flex-wrap:wrap;">
            <?php foreach ($orderItems as $it): ?>
              <span style="background:var(--bg-surface); border:1px solid var(--border-color); font-size:0.75rem; padding:0.25rem 0.6rem; border-radius:var(--radius-md); color:var(--text-secondary);">
                <?= htmlspecialchars($it['product_name']) ?> × <?= $it['quantity'] ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div style="text-align:center; padding:2.5rem 1rem;">
        <div style="font-size:3rem; margin-bottom:1rem;">🧺</div>
        <h4 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">No Pre-Orders Placed Yet</h4>
        <p style="color:var(--text-muted); font-size:0.875rem; max-width:400px; margin:0 auto 1.5rem;">
          Explore freshly listed organic vegetables, orchard fruits, raw honey, and farm dairy directly from local stalls.
        </p>
        <a href="<?= BASE_URL ?>/customer/products.php" class="btn-primary" style="text-decoration:none; padding:0.65rem 1.5rem; display:inline-block;">
          Browse Fresh Stalls
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right: Upcoming Weekend Markets Schedule -->
  <div class="portal-card">
    <div class="portal-card-header">
      <h3 class="portal-card-title">
        <span>📍</span> Farmers Markets
      </h3>
      <a href="<?= BASE_URL ?>/customer/markets.php" class="portal-card-link">Interactive Map ➔</a>
    </div>

    <div style="display:flex; flex-direction:column; gap:1rem;">
      <?php foreach ($upcomingMarkets as $m): ?>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1rem; transition:transform var(--transition-fast);">
          <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary); margin-bottom:0.25rem;">
            <?= htmlspecialchars($m['market_name']) ?>
          </div>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.5rem;">
            📍 <?= htmlspecialchars($m['address']) ?>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.72rem; color:var(--emerald-400); font-weight:700; background:rgba(16,185,129,0.1); padding:0.2rem 0.5rem; border-radius:var(--radius-sm);">
              🕒 <?= htmlspecialchars($m['operating_days']) ?>
            </span>
            <a href="<?= BASE_URL ?>/customer/products.php?market_id=<?= $m['market_id'] ?>" style="font-size:0.75rem; color:var(--sky-400); font-weight:700; text-decoration:none;">
              View Stalls ➔
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Fresh Harvest Arrivals Section -->
<div style="margin-top:2.5rem;">
  <div class="page-header-row" style="margin-bottom:1.25rem;">
    <div>
      <h2 class="page-heading" style="font-size:1.4rem;">
        <span>🥬</span> Fresh Harvest Arrivals
      </h2>
      <p class="page-subheading">Picked fresh this week and available for direct pickup reservation</p>
    </div>
    <a href="<?= BASE_URL ?>/customer/products.php" class="portal-card-link" style="font-size:0.95rem;">
      View All Produce ➔
    </a>
  </div>

  <div class="products-catalog-grid">
    <?php foreach ($featuredProducts as $prod): ?>
      <div class="product-item-card">
        <div class="product-thumb-container" onclick="window.location.href='<?= BASE_URL ?>/customer/product_detail.php?id=<?= $prod['product_id'] ?>'" style="cursor:pointer;">
          <img src="<?= htmlspecialchars(resolveImageUrl($prod['image_url'])) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>" class="product-thumb-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
          
          <button type="button" class="product-fav-btn js-fav-toggle <?= $prod['is_fav'] ? 'is-favorite' : '' ?>" 
                  data-type="product" data-id="<?= $prod['product_id'] ?>" title="Save to Favorites" aria-label="Save to Favorites"
                  onclick="event.stopPropagation();">
            <?= $prod['is_fav'] ? '❤️' : '🤍' ?>
          </button>

          <span class="product-stock-tag">
            ● <?= (int)$prod['stock_quantity'] ?> <?= htmlspecialchars($prod['unit']) ?> in stock
          </span>
        </div>

        <div class="product-card-body">
          <div class="product-category-label"><?= htmlspecialchars($prod['category_name']) ?></div>
          <h4 class="product-card-title">
            <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $prod['product_id'] ?>" style="color:inherit; text-decoration:none;">
              <?= htmlspecialchars($prod['product_name']) ?>
            </a>
          </h4>
          <div class="product-farmer-meta">
            <span>🏡</span> 
            <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $prod['product_id'] ?>" style="color:inherit; text-decoration:none;">
              <span><?= htmlspecialchars($prod['stall_name']) ?></span>
            </a>
          </div>

          <div class="product-card-price-row">
            <div>
              <span class="product-price-val">Rs. <?= number_format($prod['price'], 2) ?></span>
              <span class="product-unit-val">/ <?= htmlspecialchars($prod['unit']) ?></span>
            </div>
            <button type="button" class="btn-add-cart" onclick="addToPreOrder(<?= $prod['product_id'] ?>, <?= $prod['stall_id'] ?>, 1)">
              <span>+</span> Pre-Order
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
