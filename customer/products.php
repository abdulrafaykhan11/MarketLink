<?php
/**
 * MarketLink - Browse & Search Harvest Products
 * Implements SRS Category filters, market filters, price filtering, and pre-orders.
 */

$pageTitle = 'Browse Harvest & Produce';
$activePage = 'products';
require_once __DIR__ . '/includes/customer_header.php';

// Filter parameters
$selectedCat = (int)($_GET['category'] ?? 0);
$selectedMarket = (int)($_GET['market_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

// 1. Fetch Categories with counts
$catStmt = $pdo->query("SELECT pc.category_id, pc.category_name, pc.category_image, COUNT(p.product_id) as total_products
                        FROM product_categories pc
                        LEFT JOIN products p ON pc.category_id = p.category_id
                        WHERE pc.is_active = 1
                        GROUP BY pc.category_id
                        ORDER BY pc.category_id ASC");
$categories = $catStmt->fetchAll();

// 2. Fetch Markets for dropdown
$marketsStmt = $pdo->query("SELECT market_id, market_name FROM markets WHERE status = 'active' ORDER BY market_name ASC");
$allMarkets = $marketsStmt->fetchAll();

// 3. Build Products Query
$where = ["wi.is_available = 1"];
$params = [];

if ($selectedCat > 0) {
    $where[] = "p.category_id = :cat_id";
    $params[':cat_id'] = $selectedCat;
}

if ($selectedMarket > 0) {
    $where[] = "fms.market_id = :market_id";
    $params[':market_id'] = $selectedMarket;
}

if (!empty($search)) {
    $where[] = "(p.product_name LIKE :search OR p.description LIKE :search OR fp.stall_name LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($minPrice > 0) {
    $where[] = "wi.price >= :min_price";
    $params[':min_price'] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "wi.price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

$orderBy = "p.product_id DESC";
if ($sort === 'price_asc') {
    $orderBy = "wi.price ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "wi.price DESC";
} elseif ($sort === 'name_asc') {
    $orderBy = "p.product_name ASC";
}

$sql = "SELECT p.product_id, p.product_name, p.description, p.unit, p.image_url, 
               pc.category_name, fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone,
               m.market_name, m.address as market_address,
               wi.stall_id, wi.price, wi.stock_quantity, wi.day_of_week,
               (SELECT COUNT(*) FROM favorite_products fav WHERE fav.customer_id = {$currentUserId} AND fav.product_id = p.product_id) as is_fav
        FROM products p
        JOIN product_categories pc ON p.category_id = pc.category_id
        JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
        JOIN weekly_inventory wi ON p.product_id = wi.product_id
        JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
        JOIN markets m ON fms.market_id = m.market_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY p.product_id
        ORDER BY {$orderBy}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Page Header & Filters -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>🥬</span> Browse Harvest & Produce
    </h1>
    <p class="page-subheading">
      Showing <?= count($products) ?> fresh item<?= count($products) === 1 ? '' : 's' ?> directly from local farmers
    </p>
  </div>

  <!-- Filter Controls Form -->
  <form action="<?= BASE_URL ?>/customer/products.php" method="GET" style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
    <?php if ($selectedCat): ?>
      <input type="hidden" name="category" value="<?= $selectedCat ?>">
    <?php endif; ?>
    <?php if ($search): ?>
      <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
    <?php endif; ?>

    <!-- Market Selector -->
    <select name="market_id" class="topbar-search-input" style="width:auto; padding:0.5rem 1rem; border-radius:var(--radius-lg);" onchange="this.form.submit()">
      <option value="0">All Farmers Markets</option>
      <?php foreach ($allMarkets as $m): ?>
        <option value="<?= $m['market_id'] ?>" <?= ($selectedMarket == $m['market_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($m['market_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Sort Order -->
    <select name="sort" class="topbar-search-input" style="width:auto; padding:0.5rem 1rem; border-radius:var(--radius-lg);" onchange="this.form.submit()">
      <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Newest Harvest</option>
      <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
      <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>Alphabetical (A-Z)</option>
    </select>
  </form>
</div>

<!-- Category Pills Carousel -->
<div class="filter-pills-row">
  <a href="<?= BASE_URL ?>/customer/products.php<?= $selectedMarket ? '?market_id='.$selectedMarket : '' ?>" 
     class="filter-pill <?= ($selectedCat === 0) ? 'active' : '' ?>">
    <span>🌱</span> All Categories
  </a>
  <?php foreach ($categories as $cat): ?>
    <a href="<?= BASE_URL ?>/customer/products.php?category=<?= $cat['category_id'] ?><?= $selectedMarket ? '&market_id='.$selectedMarket : '' ?>" 
       class="filter-pill <?= ($selectedCat == $cat['category_id']) ? 'active' : '' ?>">
      <span><?= htmlspecialchars($cat['category_name']) ?></span>
      <span style="font-size:0.75rem; opacity:0.75;">(<?= $cat['total_products'] ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<!-- Product Grid -->
<?php if (!empty($products)): ?>
  <div class="products-catalog-grid">
    <?php foreach ($products as $prod): ?>
      <div class="product-item-card">
        <div class="product-thumb-container">
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>" class="product-thumb-img">
          
          <button type="button" class="product-fav-btn js-fav-toggle <?= $prod['is_fav'] ? 'is-favorite' : '' ?>" 
                  data-type="product" data-id="<?= $prod['product_id'] ?>" title="Save to Favorites" aria-label="Save to Favorites">
            <?= $prod['is_fav'] ? '❤️' : '🤍' ?>
          </button>

          <span class="product-stock-tag">
            ● <?= (int)$prod['stock_quantity'] ?> <?= htmlspecialchars($prod['unit']) ?> in stock
          </span>
        </div>

        <div class="product-card-body">
          <div class="product-category-label"><?= htmlspecialchars($prod['category_name']) ?></div>
          <h3 class="product-card-title"><?= htmlspecialchars($prod['product_name']) ?></h3>
          
          <p style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; margin-bottom:0.75rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
            <?= htmlspecialchars($prod['description'] ?? 'Harvested fresh for local market pickup.') ?>
          </p>

          <div class="product-farmer-meta">
            <span>🏡</span> 
            <strong><?= htmlspecialchars($prod['stall_name']) ?></strong>
          </div>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.85rem;">
            📍 <?= htmlspecialchars($prod['market_name']) ?>
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
<?php else: ?>
  <div class="portal-card" style="text-align:center; padding:3.5rem 1.5rem; margin-top:1.5rem;">
    <div style="font-size:3.5rem; margin-bottom:1rem;">🧺</div>
    <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">No Produce Found</h3>
    <p style="color:var(--text-muted); font-size:0.95rem; max-width:480px; margin:0 auto 1.5rem;">
      No harvest items matched your search or filters. Try choosing another category or clearing your search term.
    </p>
    <a href="<?= BASE_URL ?>/customer/products.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding:0.65rem 1.5rem;">
      Clear All Filters
    </a>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
