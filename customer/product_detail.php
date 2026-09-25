<?php
/**
 * MarketLink - High-End Product & Store Detail Page
 * Features:
 * - Comprehensive product view with per kg/unit pricing & real-time stock
 * - Full Dark & Light theme integration using official design system tokens
 * - Multi-image gallery carousel (supports 5 product photos: 1 primary + 4 optional)
 * - Stall & Farmer producer profile showcase with market pickup details
 * - Interactive pre-order quantity calculator & basket addition
 * - Direct "Chat with Seller" if active order exists
 * - Farmer & Product customer reviews with verified purchase ratings
 * - "More from this Store" & "Related Harvest" recommendations
 */

$pageTitle = 'Product Details';
$activePage = 'products';
require_once __DIR__ . '/includes/customer_header.php';

$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) {
    header("Location: " . BASE_URL . "/customer/products.php");
    exit;
}

// 1. Fetch Product Details with Farmer, Inventory, and Market info
$query = "SELECT p.*, pc.category_name, 
                 fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, fp.business_email,
                 fp.address as farmer_address, fp.order_cutoff_time, fp.approval_status,
                 wi.inventory_id, wi.stall_id, wi.day_of_week, wi.stock_quantity, wi.price, wi.is_available,
                 fms.stall_number_location, fms.operating_days,
                 m.market_id, m.market_name, m.address as market_address, m.city as market_city,
                 (SELECT COUNT(*) FROM favorite_products fav WHERE fav.customer_id = :uid1 AND fav.product_id = p.product_id) as is_fav_product,
                 (SELECT COUNT(*) FROM favorite_farmers favf WHERE favf.customer_id = :uid2 AND favf.farmer_id = fp.farmer_id) as is_fav_farmer
          FROM products p
          JOIN product_categories pc ON p.category_id = pc.category_id
          JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
          LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id AND wi.is_available = 1
          LEFT JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
          LEFT JOIN markets m ON fms.market_id = m.market_id
          WHERE p.product_id = :pid AND fp.approval_status = 'approved'
          LIMIT 1";

$stmt = $pdo->prepare($query);
$stmt->execute([':pid' => $productId, ':uid1' => $currentUserId, ':uid2' => $currentUserId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div style="padding:4rem 2rem; text-align:center;">
            <h2 style="color:var(--text-primary);">Produce Item Not Found</h2>
            <p style="color:var(--text-secondary);">The requested harvest produce is either no longer available or awaiting farmer verification.</p>
            <a href="' . BASE_URL . '/customer/products.php" class="btn-primary" style="display:inline-block; margin-top:1rem;">Back to Harvest Catalog</a>
          </div>';
    require_once __DIR__ . '/includes/customer_footer.php';
    exit;
}

$farmerId = (int)$product['farmer_id'];
$categoryId = (int)$product['category_id'];
$unit = htmlspecialchars($product['unit'] ?: 'kg');
$price = (float)($product['price'] ?? 0);
$stock = (float)($product['stock_quantity'] ?? 0);

// 2. Fetch Multi-Images for this product (1 Primary + up to 4 Optional)
$imgStmt = $pdo->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, display_order ASC, image_id ASC");
$imgStmt->execute([':pid' => $productId]);
$galleryImages = $imgStmt->fetchAll();

// If product_images table doesn't have records yet for this product, fallback to product.image_url
if (empty($galleryImages) && !empty($product['image_url'])) {
    $galleryImages = [['image_url' => $product['image_url'], 'is_primary' => 1]];
}

$mainHeroImage = !empty($galleryImages) ? $galleryImages[0]['image_url'] : ($product['image_url'] ?: 'assets/images/cat-vegetables.svg');

// 3. Check if customer has any active order with this farmer (for direct chat link)
$activeOrderStmt = $pdo->prepare("SELECT order_id, order_number, order_status 
                                  FROM orders 
                                  WHERE customer_id = :cid AND farmer_id = :fid 
                                    AND order_status IN ('placed', 'accepted', 'ready_for_pickup')
                                  ORDER BY order_id DESC LIMIT 1");
$activeOrderStmt->execute([':cid' => $currentUserId, ':fid' => $farmerId]);
$activeOrder = $activeOrderStmt->fetch();

// 4. Fetch Farmer & Product Ratings / Reviews
$revStmt = $pdo->prepare("SELECT fr.*, u.username, cp.full_name, u.profile_image
                          FROM farmer_reviews fr
                          JOIN users u ON fr.customer_id = u.user_id
                          LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id
                          WHERE fr.farmer_id = :fid AND fr.is_moderated = 1
                          ORDER BY fr.created_at DESC LIMIT 5");
$revStmt->execute([':fid' => $farmerId]);
$reviews = $revStmt->fetchAll();

// Calculate average rating
$ratingStmt = $pdo->prepare("SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 5.0) as avg_rating 
                             FROM farmer_reviews 
                             WHERE farmer_id = :fid AND is_moderated = 1");
$ratingStmt->execute([':fid' => $farmerId]);
$ratingInfo = $ratingStmt->fetch();
$avgRating = round((float)$ratingInfo['avg_rating'], 1);
$totalReviews = (int)$ratingInfo['total_reviews'];

// 5. Fetch "More from this Store" (Other products from this farmer)
$moreFromStoreStmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.unit, p.image_url, 
                                           wi.price, wi.stock_quantity, wi.stall_id
                                    FROM products p
                                    JOIN weekly_inventory wi ON p.product_id = wi.product_id
                                    WHERE p.farmer_id = :fid AND p.product_id != :pid AND wi.is_available = 1
                                    GROUP BY p.product_id
                                    LIMIT 4");
$moreFromStoreStmt->execute([':fid' => $farmerId, ':pid' => $productId]);
$moreFromStore = $moreFromStoreStmt->fetchAll();

// 6. Fetch "Related Harvest" (Same Category)
$relatedStmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.unit, p.image_url, 
                                     fp.stall_name, wi.price, wi.stock_quantity, wi.stall_id
                              FROM products p
                              JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                              JOIN weekly_inventory wi ON p.product_id = wi.product_id
                              WHERE p.category_id = :cid AND p.product_id != :pid 
                                AND wi.is_available = 1 AND fp.approval_status = 'approved'
                              GROUP BY p.product_id
                              LIMIT 4");
$relatedStmt->execute([':cid' => $categoryId, ':pid' => $productId]);
$relatedProducts = $relatedStmt->fetchAll();
?>

<!-- Breadcrumbs -->
<div style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem; flex-wrap:wrap;">
  <a href="<?= BASE_URL ?>/customer/dashboard.php" style="color:var(--text-muted); text-decoration:none;">Dashboard</a>
  <span>&rsaquo;</span>
  <a href="<?= BASE_URL ?>/customer/products.php" style="color:var(--text-muted); text-decoration:none;">Browse Harvest</a>
  <span>&rsaquo;</span>
  <a href="<?= BASE_URL ?>/customer/products.php?category=<?= $categoryId ?>" style="color:var(--text-muted); text-decoration:none;"><?= htmlspecialchars($product['category_name']) ?></a>
  <span>&rsaquo;</span>
  <span style="color:var(--primary-400); font-weight:600;"><?= htmlspecialchars($product['product_name']) ?></span>
</div>

<!-- Main Product Hero Section (Rich Theme Compatible) -->
<div class="portal-card" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:2.5rem; padding:2rem; margin-bottom:3rem; border:1px solid var(--border-color); background:var(--bg-surface);">
  
  <!-- Left: Media / Multi-Image Gallery Column -->
  <div>
    <div style="position:relative; width:100%; aspect-ratio:4/3; border-radius:var(--radius-lg); overflow:hidden; background:var(--bg-secondary); border:1px solid var(--border-color);">
      <img id="mainHeroImg" src="<?= htmlspecialchars(resolveImageUrl($mainHeroImage)) ?>" 
           alt="<?= htmlspecialchars($product['product_name']) ?>" 
           onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';"
           style="width:100%; height:100%; object-fit:cover; display:block; transition:all 0.25s ease;">

      <!-- Badges overlay -->
      <div style="position:absolute; top:1rem; left:1rem; display:flex; flex-direction:column; gap:0.5rem;">
        <span style="background:rgba(5, 150, 105, 0.95); color:#ffffff; font-weight:700; font-size:0.75rem; padding:0.35rem 0.75rem; border-radius:999px; backdrop-filter:blur(4px); box-shadow:0 2px 6px rgba(0,0,0,0.25);">
          🌿 Farm Fresh Local
        </span>
        <?php if ($stock > 0): ?>
          <span style="background:rgba(6, 21, 36, 0.88); border:1px solid var(--border-color); color:var(--text-primary); font-size:0.75rem; font-weight:600; padding:0.35rem 0.75rem; border-radius:999px; backdrop-filter:blur(4px);">
            ● <?= (float)$stock ?> <?= $unit ?> Available
          </span>
        <?php else: ?>
          <span style="background:rgba(239, 68, 68, 0.9); color:#ffffff; font-size:0.75rem; font-weight:700; padding:0.35rem 0.75rem; border-radius:999px;">
            Out of Stock
          </span>
        <?php endif; ?>
      </div>

      <button type="button" class="product-fav-btn js-fav-toggle <?= $product['is_fav_product'] ? 'is-favorite' : '' ?>" 
              data-type="product" data-id="<?= $product['product_id'] ?>" 
              title="Save to Favorites"
              style="position:absolute; top:1rem; right:1rem; width:40px; height:40px; border-radius:50%; background:var(--bg-surface); border:1px solid var(--border-color); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
        <?= $product['is_fav_product'] ? '❤️' : '🤍' ?>
      </button>
    </div>

    <!-- Multi-Image 5-Slot Thumbnail Strip -->
    <?php if (count($galleryImages) > 1): ?>
      <div style="display:flex; align-items:center; gap:0.65rem; margin-top:0.85rem; overflow-x:auto; padding-bottom:4px;">
        <?php foreach ($galleryImages as $idx => $gImg): ?>
          <button type="button" onclick="switchGalleryPhoto('<?= htmlspecialchars(resolveImageUrl($gImg['image_url'])) ?>', this)" 
                  class="gallery-thumb-btn <?= $idx === 0 ? 'active' : '' ?>" 
                  style="width:68px; height:68px; border-radius:var(--radius-md); overflow:hidden; border:2px solid <?= $idx === 0 ? 'var(--primary-500)' : 'var(--border-color)' ?>; background:var(--bg-secondary); padding:0; cursor:pointer; flex-shrink:0; transition:all 0.2s;">
            <img src="<?= htmlspecialchars(resolveImageUrl($gImg['image_url'])) ?>" 
                 onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';"
                 style="width:100%; height:100%; object-fit:cover; display:block;">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Pickup Market & Cutoff Highlights (Theme Safe for Dark & Light Mode) -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.85rem; margin-top:1.25rem;">
      <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0.9rem; text-align:center;">
        <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Pickup Market</div>
        <div style="font-size:0.9375rem; font-weight:800; color:var(--text-primary); margin-top:4px;">
          <?= htmlspecialchars($product['market_name'] ?: 'Community Bazaar') ?>
        </div>
      </div>
      <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0.9rem; text-align:center;">
        <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Order Cut-Off</div>
        <div style="font-size:0.9375rem; font-weight:800; color:var(--emerald-400); margin-top:4px;">
          <?= date('h:i A', strtotime($product['order_cutoff_time'])) ?> Prior
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Pricing & Order Action Column -->
  <div style="display:flex; flex-direction:column; justify-content:space-between;">
    <div>
      <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:0.6rem;">
        <span style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--emerald-400); background:rgba(16, 185, 129, 0.15); border:1px solid rgba(16, 185, 129, 0.3); padding:0.25rem 0.75rem; border-radius:999px;">
          <?= htmlspecialchars($product['category_name']) ?>
        </span>
        <div style="display:flex; align-items:center; gap:0.35rem; font-size:0.875rem; font-weight:700; color:#fbbf24;">
          <span>★</span> <span><?= $avgRating ?></span>
          <span style="color:var(--text-muted); font-weight:500; font-size:0.8rem;">(<?= $totalReviews ?> reviews)</span>
        </div>
      </div>

      <h1 style="font-size:1.85rem; font-weight:800; color:var(--text-primary); margin:0 0 0.75rem 0; line-height:1.2;">
        <?= htmlspecialchars($product['product_name']) ?>
      </h1>

      <!-- Rate Display Banner -->
      <div style="background:linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(56, 189, 248, 0.12) 100%); border:1px solid rgba(16, 185, 129, 0.3); border-radius:var(--radius-lg); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:baseline; gap:0.5rem;">
        <span style="font-size:2rem; font-weight:800; color:var(--emerald-400); line-height:1;">
          Rs. <?= number_format($price, 2) ?>
        </span>
        <span style="font-size:1rem; font-weight:600; color:var(--text-secondary);">
          per <?= $unit ?>
        </span>
        <span style="margin-left:auto; font-size:0.75rem; font-weight:700; color:var(--text-muted); text-align:right;">
          ✓ Fixed Farmgate Rate
        </span>
      </div>

      <!-- Description -->
      <div style="font-size:0.9375rem; color:var(--text-secondary); line-height:1.6; margin-bottom:1.5rem;">
        <?= nl2br(htmlspecialchars($product['description'] ?: 'Freshly harvested produce cultivated using sustainable, natural practices. Hand-sorted and packaged for convenient weekend farmers market pickup.')) ?>
      </div>

      <!-- Farm / Stall Quick Card (Theme Safe) -->
      <div class="product-stall-summary" style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div style="display:flex; align-items:center; gap:0.875rem;">
          <div style="width:44px; height:44px; border-radius:50%; background:var(--emerald-600); color:#ffffff; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
            <?= strtoupper(substr($product['stall_name'], 0, 1)) ?>
          </div>
          <div>
            <div style="font-weight:700; color:var(--text-primary); font-size:0.95rem;">
              <?= htmlspecialchars($product['stall_name']) ?>
            </div>
            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
              Producer: <?= htmlspecialchars($product['contact_person']) ?> &bull; Stall #<?= htmlspecialchars($product['stall_number_location'] ?: 'A-1') ?>
            </div>
          </div>
        </div>

        <a href="<?= BASE_URL ?>/stall.php?id=<?= $farmerId ?>" class="product-stall-link">
          View Stall Profile <span aria-hidden="true">&rarr;</span>
        </a>

        <?php if ($activeOrder): ?>
          <a href="<?= BASE_URL ?>/customer/chat.php?order_id=<?= $activeOrder['order_id'] ?>" 
             class="btn-secondary" 
             style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 0.85rem; font-size:0.8rem; text-decoration:none; border-color:var(--emerald-400); color:var(--emerald-400);">
            <span>💬</span> Chat with Farmer
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pre-Order Quantity & Action Bar -->
    <div style="border-top:1px solid var(--border-color); padding-top:1.5rem;">
      <?php if ($stock > 0): ?>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
          <div style="font-size:0.875rem; font-weight:600; color:var(--text-secondary);">Select Quantity:</div>
          <div style="display:flex; align-items:center; gap:0.5rem;">
            <button type="button" id="btnQtyMinus" 
                    style="width:36px; height:36px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-secondary); font-size:1.25rem; font-weight:700; cursor:pointer; color:var(--text-primary);">
              -
            </button>
            <input type="number" id="detailQtyInput" value="1" min="1" max="<?= (int)$stock ?>" 
                   style="width:60px; height:36px; text-align:center; font-weight:700; font-size:1rem; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-secondary); color:var(--text-primary);">
            <button type="button" id="btnQtyPlus" 
                    style="width:36px; height:36px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-secondary); font-size:1.25rem; font-weight:700; cursor:pointer; color:var(--text-primary);">
              +
            </button>
            <span style="font-size:0.875rem; color:var(--text-muted); font-weight:600; margin-left:4px;"><?= $unit ?></span>
          </div>
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
          <span style="font-size:0.875rem; color:var(--text-muted);">Estimated Total:</span>
          <span style="font-size:1.25rem; font-weight:800; color:var(--text-primary);" id="calculatedTotalPrice">
            Rs. <?= number_format($price, 2) ?>
          </span>
        </div>

        <div style="display:grid; grid-template-columns:1fr; gap:0.75rem;">
          <button type="button" id="btnAddToCartDetail" 
                  class="btn-primary" 
                  style="width:100%; padding:0.9rem 1.5rem; font-size:1rem; font-weight:700; display:flex; align-items:center; justify-content:center; gap:0.6rem; box-shadow:0 4px 14px rgba(5, 150, 105, 0.3);">
            <span>🛒</span> Add to Pre-Order Basket
          </button>
        </div>
      <?php else: ?>
        <div style="padding:1rem; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); border-radius:var(--radius-md); text-align:center; color:var(--rose-400); font-weight:600;">
          ⚠️ This item is currently out of stock for this week's harvest cycle.
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Section: More Produce from this Store -->
<?php if (!empty($moreFromStore)): ?>
  <div style="margin-bottom:3rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
      <h2 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin:0;">
        <span>🏡</span> More Fresh Harvest from <?= htmlspecialchars($product['stall_name']) ?>
      </h2>
      <a href="<?= BASE_URL ?>/customer/products.php?search=<?= urlencode($product['stall_name']) ?>" style="font-size:0.875rem; color:var(--emerald-400); font-weight:600; text-decoration:none;">
        View Stall Catalog &rarr;
      </a>
    </div>

    <div class="products-catalog-grid" style="grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));">
      <?php foreach ($moreFromStore as $mItem): ?>
        <div class="product-item-card">
          <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $mItem['product_id'] ?>" style="text-decoration:none; color:inherit; display:block;">
            <div class="product-thumb-container" style="aspect-ratio:4/3;">
              <img src="<?= htmlspecialchars(resolveImageUrl($mItem['image_url'])) ?>" alt="<?= htmlspecialchars($mItem['product_name']) ?>" class="product-thumb-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
              <span class="product-stock-tag">
                ● <?= (float)$mItem['stock_quantity'] ?> in stock
              </span>
            </div>
            <div class="product-card-body" style="padding:1rem;">
              <h3 class="product-card-title" style="font-size:1rem; margin-bottom:0.5rem;"><?= htmlspecialchars($mItem['product_name']) ?></h3>
              <div class="product-card-price-row">
                <span class="product-price-val" style="font-size:1.05rem;">Rs. <?= number_format($mItem['price'], 2) ?></span>
                <span style="font-size:0.75rem; color:var(--emerald-400); font-weight:700;">View &rarr;</span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Section: Related Produce in Same Category -->
<?php if (!empty($relatedProducts)): ?>
  <div style="margin-bottom:3rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
      <h2 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin:0;">
        <span>🥬</span> Related in <?= htmlspecialchars($product['category_name']) ?>
      </h2>
      <a href="<?= BASE_URL ?>/customer/products.php?category=<?= $categoryId ?>" style="font-size:0.875rem; color:var(--emerald-400); font-weight:600; text-decoration:none;">
        Explore Category &rarr;
      </a>
    </div>

    <div class="products-catalog-grid" style="grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));">
      <?php foreach ($relatedProducts as $rItem): ?>
        <div class="product-item-card">
          <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $rItem['product_id'] ?>" style="text-decoration:none; color:inherit; display:block;">
            <div class="product-thumb-container" style="aspect-ratio:4/3;">
              <img src="<?= htmlspecialchars(resolveImageUrl($rItem['image_url'])) ?>" alt="<?= htmlspecialchars($rItem['product_name']) ?>" class="product-thumb-img" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
              <span class="product-stock-tag">
                ● <?= (float)$rItem['stock_quantity'] ?> in stock
              </span>
            </div>
            <div class="product-card-body" style="padding:1rem;">
              <div class="product-farmer-meta" style="font-size:0.75rem; margin-bottom:0.25rem;">
                🏡 <?= htmlspecialchars($rItem['stall_name']) ?>
              </div>
              <h3 class="product-card-title" style="font-size:1rem; margin-bottom:0.5rem;"><?= htmlspecialchars($rItem['product_name']) ?></h3>
              <div class="product-card-price-row">
                <span class="product-price-val" style="font-size:1.05rem;">Rs. <?= number_format($rItem['price'], 2) ?></span>
                <span style="font-size:0.75rem; color:var(--emerald-400); font-weight:700;">View &rarr;</span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Section: Customer Reviews & Producer Trust -->
<div class="portal-card" style="padding:2rem; margin-bottom:2rem; background:var(--bg-surface); border:1px solid var(--border-color);">
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
      <h2 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin:0 0 0.25rem 0;">
        <span>⭐</span> Customer Reviews for <?= htmlspecialchars($product['stall_name']) ?>
      </h2>
      <p style="font-size:0.875rem; color:var(--text-secondary); margin:0;">
        Verified ratings from customers who picked up fresh orders from this stall
      </p>
    </div>

    <div style="display:flex; align-items:center; gap:0.75rem; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:0.6rem 1.25rem;">
      <span style="font-size:1.75rem; font-weight:800; color:#fbbf24; line-height:1;"><?= $avgRating ?></span>
      <div>
        <div style="color:#fbbf24; font-size:0.9rem;">
          <?= str_repeat('★', (int)round($avgRating)) . str_repeat('☆', 5 - (int)round($avgRating)) ?>
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Based on <?= $totalReviews ?> reviews</div>
      </div>
    </div>
  </div>

  <?php if (!empty($reviews)): ?>
    <div style="display:flex; flex-direction:column; gap:1.25rem;">
      <?php foreach ($reviews as $rev): ?>
        <div style="border-bottom:1px solid var(--border-subtle); padding-bottom:1.25rem;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.4rem;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
              <div style="width:34px; height:34px; border-radius:50%; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; color:var(--text-primary); border:1px solid var(--border-color);">
                <?= strtoupper(substr($rev['full_name'] ?: $rev['username'], 0, 1)) ?>
              </div>
              <div>
                <span style="font-weight:700; font-size:0.9rem; color:var(--text-primary);"><?= htmlspecialchars($rev['full_name'] ?: $rev['username']) ?></span>
                <span style="font-size:0.75rem; color:var(--emerald-400); margin-left:6px; font-weight:600;">✓ Verified Pickup</span>
              </div>
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted);">
              <?= date('d M Y', strtotime($rev['created_at'])) ?>
            </div>
          </div>

          <div style="color:#fbbf24; font-size:0.85rem; margin-bottom:0.4rem;">
            <?= str_repeat('★', (int)$rev['rating']) . str_repeat('☆', 5 - (int)$rev['rating']) ?>
          </div>

          <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.5; margin:0 0 0.5rem 0;">
            <?= htmlspecialchars($rev['review_comment'] ?: 'Great fresh harvest quality and pleasant stall service.') ?>
          </p>

          <?php if (!empty($rev['farmer_response'])): ?>
            <div style="background:var(--bg-secondary); border-left:3px solid var(--emerald-500); padding:0.6rem 0.85rem; border-radius:4px; font-size:0.8125rem; color:var(--text-secondary); margin-top:0.5rem;">
              <strong style="color:var(--text-primary);">Farmer Response:</strong> <?= htmlspecialchars($rev['farmer_response']) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.9rem;">
      🌱 No customer reviews published for this stall yet. Place a pre-order to be the first to review!
    </div>
  <?php endif; ?>
</div>

<script>
// Switch main gallery photo
function switchGalleryPhoto(src, btn) {
  const mainImg = document.getElementById('mainHeroImg');
  if (mainImg) {
    mainImg.style.opacity = '0.4';
    setTimeout(() => {
      mainImg.src = src;
      mainImg.style.opacity = '1';
    }, 150);
  }
  document.querySelectorAll('.gallery-thumb-btn').forEach(b => {
    b.style.borderColor = 'var(--border-color)';
    b.classList.remove('active');
  });
  if (btn) {
    btn.style.borderColor = 'var(--primary-500)';
    btn.classList.add('active');
  }
}

// Dynamic quantity & total calculator
const unitPrice = <?= $price ?>;
const maxStock = <?= (int)$stock ?>;
const stallId = <?= (int)($product['stall_id'] ?? 0) ?>;
const productId = <?= $productId ?>;

const qtyInput = document.getElementById('detailQtyInput');
const btnMinus = document.getElementById('btnQtyMinus');
const btnPlus = document.getElementById('btnQtyPlus');
const totalDisplay = document.getElementById('calculatedTotalPrice');
const btnAddToCart = document.getElementById('btnAddToCartDetail');

function updateTotal() {
  if (!qtyInput) return;
  let val = parseInt(qtyInput.value) || 1;
  if (val < 1) val = 1;
  if (val > maxStock) val = maxStock;
  qtyInput.value = val;
  const tot = (val * unitPrice).toFixed(2);
  if (totalDisplay) {
    totalDisplay.innerText = 'Rs. ' + Number(tot).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }
}

if (btnMinus && qtyInput) {
  btnMinus.addEventListener('click', () => {
    let cur = parseInt(qtyInput.value) || 1;
    if (cur > 1) {
      qtyInput.value = cur - 1;
      updateTotal();
    }
  });
}

if (btnPlus && qtyInput) {
  btnPlus.addEventListener('click', () => {
    let cur = parseInt(qtyInput.value) || 1;
    if (cur < maxStock) {
      qtyInput.value = cur + 1;
      updateTotal();
    }
  });
}

if (qtyInput) {
  qtyInput.addEventListener('change', updateTotal);
}

if (btnAddToCart) {
  btnAddToCart.addEventListener('click', async () => {
    const qty = parseInt(qtyInput.value) || 1;
    btnAddToCart.disabled = true;
    btnAddToCart.innerHTML = '<span>⏳</span> Adding to Basket...';

    try {
      const fd = new FormData();
      fd.append('action', 'add');
      fd.append('product_id', productId);
      fd.append('stall_id', stallId);
      fd.append('quantity', qty);

      const res = await fetch('<?= BASE_URL ?>/customer/api/cart.php', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();

      if (data.status === 'success') {
        Toast.success('Added to Basket!', `${qty} unit(s) of fresh harvest added to your pre-order basket.`);
        
        // Update sidebar cart badge
        document.querySelectorAll('.cart-count-badge').forEach(b => {
          b.innerText = data.total_items;
          b.style.display = data.total_items > 0 ? 'inline-block' : 'none';
        });

        btnAddToCart.innerHTML = '<span>✔</span> Added! View Basket &rarr;';
        btnAddToCart.classList.remove('btn-primary');
        btnAddToCart.classList.add('btn-secondary');
        btnAddToCart.onclick = () => window.location.href = '<?= BASE_URL ?>/customer/cart.php';
      } else {
        Toast.error('Could Not Add', data.message || 'Error updating basket.');
        btnAddToCart.disabled = false;
        btnAddToCart.innerHTML = '<span>🛒</span> Add to Pre-Order Basket';
      }
    } catch (e) {
      Toast.error('Network Error', 'Could not communicate with the server.');
      btnAddToCart.disabled = false;
      btnAddToCart.innerHTML = '<span>🛒</span> Add to Pre-Order Basket';
    }
  });
}
</script>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
