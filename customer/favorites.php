<?php
/**
 * MarketLink - Saved Favorites (Farmers & Products)
 * Implements SRS Section 1.6: Bookmark preferred growers and seasonal items with restock status.
 */

$pageTitle = 'Saved Favorites';
$activePage = 'favorites';
require_once __DIR__ . '/includes/customer_header.php';

$currentTab = $_GET['tab'] ?? 'products';

// 1. Fetch Favorite Products
$favProdStmt = $pdo->prepare("SELECT fp_fav.favorite_id, fp_fav.created_at as saved_at,
                                     p.product_id, p.product_name, p.unit, p.image_url,
                                     pc.category_name,
                                     fp.stall_name,
                                     wi.stall_id, wi.price, wi.stock_quantity, wi.is_available
                              FROM favorite_products fp_fav
                              JOIN products p ON fp_fav.product_id = p.product_id
                              JOIN product_categories pc ON p.category_id = pc.category_id
                              JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                              LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id
                              WHERE fp_fav.customer_id = :uid AND fp.approval_status = 'approved'
                              GROUP BY p.product_id
                              ORDER BY fp_fav.favorite_id DESC");
$favProdStmt->execute([':uid' => $currentUserId]);
$favProducts = $favProdStmt->fetchAll();

// 2. Fetch Favorite Farmers
$favFarmStmt = $pdo->prepare("SELECT ff.favorite_id, ff.created_at as saved_at,
                                     fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, fp.description,
                                     m.market_name, m.operating_days, m.operating_hours,
                                     fms.stall_number_location, fms.market_id
                              FROM favorite_farmers ff
                              JOIN farmer_profiles fp ON ff.farmer_id = fp.farmer_id
                              LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id
                              LEFT JOIN markets m ON fms.market_id = m.market_id
                              WHERE ff.customer_id = :uid AND fp.approval_status = 'approved'
                              GROUP BY fp.farmer_id
                              ORDER BY ff.favorite_id DESC");
$favFarmStmt->execute([':uid' => $currentUserId]);
$favFarmers = $favFarmStmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>❤️</span> Saved Favorites
    </h1>
    <p class="page-subheading">
      Quick access to your preferred local farmers and bookmarked organic harvest
    </p>
  </div>
</div>

<!-- Tabs Navigation -->
<div class="filter-pills-row" style="margin-bottom:2rem;">
  <a href="<?= BASE_URL ?>/customer/favorites.php?tab=products" class="filter-pill <?= ($currentTab === 'products') ? 'active' : '' ?>">
    🥬 Favorite Produce (<?= count($favProducts) ?>)
  </a>
  <a href="<?= BASE_URL ?>/customer/favorites.php?tab=farmers" class="filter-pill <?= ($currentTab === 'farmers') ? 'active' : '' ?>">
    🏡 Favorite Farmers (<?= count($favFarmers) ?>)
  </a>
</div>

<?php if ($currentTab === 'products'): ?>
  <!-- Tab 1: Favorite Products -->
  <?php if (!empty($favProducts)): ?>
    <div class="products-catalog-grid">
      <?php foreach ($favProducts as $prod): ?>
        <div class="product-item-card" id="fav-prod-card-<?= $prod['product_id'] ?>">
          <div class="product-thumb-container">
            <img src="<?= htmlspecialchars(resolveImageUrl($prod['image_url'])) ?>" alt="" class="product-thumb-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
            
            <button type="button" class="product-fav-btn js-fav-toggle is-favorite" 
                    data-type="product" data-id="<?= $prod['product_id'] ?>" title="Remove from favorites" aria-label="Remove favorite">
              ❤️
            </button>

            <span class="product-stock-tag">
              ● <?= (int)$prod['stock_quantity'] ?> <?= htmlspecialchars($prod['unit']) ?> in stock
            </span>
          </div>

          <div class="product-card-body">
            <div class="product-category-label"><?= htmlspecialchars($prod['category_name']) ?></div>
            <h4 class="product-card-title"><?= htmlspecialchars($prod['product_name']) ?></h4>
            <div class="product-farmer-meta">
              <span>🏡</span> <span><?= htmlspecialchars($prod['stall_name']) ?></span>
            </div>

            <div class="product-card-price-row">
              <div>
                <span class="product-price-val">Rs. <?= number_format($prod['price'], 2) ?></span>
                <span class="product-unit-val">/ <?= htmlspecialchars($prod['unit']) ?></span>
              </div>
              <button type="button" class="btn-add-cart" onclick="addToPreOrder(<?= $prod['product_id'] ?>, <?= $prod['stall_id'] ?? 1 ?>, 1)">
                <span>+</span> Pre-Order
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="portal-card" style="text-align:center; padding:4rem 1.5rem;">
      <div style="font-size:3.5rem; margin-bottom:1rem;">❤️</div>
      <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">No Favorite Produce Saved</h3>
      <p style="color:var(--text-muted); font-size:0.95rem; max-width:460px; margin:0 auto 1.5rem;">
        Click the heart icon on any fresh fruit, vegetable, or farm bakery item to bookmark it for quick weekly pre-ordering.
      </p>
      <a href="<?= BASE_URL ?>/customer/products.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding:0.65rem 1.75rem;">
        Explore Fresh Harvest
      </a>
    </div>
  <?php endif; ?>

<?php else: ?>
  <!-- Tab 2: Favorite Farmers -->
  <?php if (!empty($favFarmers)): ?>
    <div class="market-cards-grid">
      <?php foreach ($favFarmers as $farmer): ?>
        <div class="market-info-card" id="fav-farmer-card-<?= $farmer['farmer_id'] ?>">
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.85rem;">
            <div>
              <span style="font-size:0.7rem; font-weight:700; color:var(--emerald-400); text-transform:uppercase;">Verified Producer</span>
              <h3 style="font-family:var(--font-heading); font-size:1.2rem; font-weight:800; color:var(--text-primary); margin:0.25rem 0;">
                <?= htmlspecialchars($farmer['stall_name']) ?>
              </h3>
              <div style="font-size:0.8rem; color:var(--text-muted);">
                Farmer: <?= htmlspecialchars($farmer['contact_person']) ?> • 📞 <?= htmlspecialchars($farmer['business_phone']) ?>
              </div>
            </div>
            
            <button type="button" class="product-fav-btn js-fav-toggle is-favorite" 
                    data-type="farmer" data-id="<?= $farmer['farmer_id'] ?>" style="position:static;" title="Remove from favorites">
              ❤️
            </button>
          </div>

          <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.45; margin-bottom:1rem; flex:1;">
            <?= htmlspecialchars($farmer['description'] ?? 'Local family producer committed to sustainable farming and direct fresh harvests.') ?>
          </p>

          <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.8rem;">
            <div><strong>Market:</strong> <?= htmlspecialchars($farmer['market_name'] ?? 'Local Farmers Market') ?></div>
            <div style="margin-top:0.25rem; color:var(--emerald-400); font-weight:600;">
              🗓️ <?= htmlspecialchars($farmer['operating_days'] ?? 'Weekends') ?> (<?= htmlspecialchars($farmer['operating_hours'] ?? 'Morning') ?>)
            </div>
          </div>

          <a href="<?= BASE_URL ?>/customer/products.php?market_id=<?= $farmer['market_id'] ?? 0 ?>" class="btn-primary" style="text-align:center; text-decoration:none; padding:0.6rem; font-size:0.85rem;">
            <span>🥬</span> View Stall Products
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="portal-card" style="text-align:center; padding:4rem 1.5rem;">
      <div style="font-size:3.5rem; margin-bottom:1rem;">🏡</div>
      <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">No Favorite Farmers Saved</h3>
      <p style="color:var(--text-muted); font-size:0.95rem; max-width:460px; margin:0 auto 1.5rem;">
        Bookmark local family growers to receive direct restock alerts and quick access to their weekly stall listings.
      </p>
      <a href="<?= BASE_URL ?>/customer/markets.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding:0.65rem 1.75rem;">
        Discover Nearby Farmers
      </a>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
