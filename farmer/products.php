<?php
/**
 * MarketLink - Farmer Produce Catalog & Weekly Stock Manager
 * SRS Specification: Add, edit, view, delete products, weekly stock & templates
 */

$pageTitle = 'Produce Catalog & Weekly Stock';
$activePage = 'products';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Fetch Product Categories
$catStmt = $pdo->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY category_name ASC");
$categories = $catStmt->fetchAll();

// 2. Parse Filters
$selectedCat = (int)($_GET['category_id'] ?? 0);
$stockStatus = trim($_GET['stock_status'] ?? 'all');
$searchTerm = trim($_GET['search'] ?? '');
$editProductId = (int)($_GET['edit'] ?? 0);
$action = trim($_GET['action'] ?? '');

// 3. Build Products Query
$where = ["p.farmer_id = :fid"];
$params = [':fid' => $farmerId];

if ($selectedCat > 0) {
    $where[] = "p.category_id = :cid";
    $params[':cid'] = $selectedCat;
}

if (!empty($searchTerm)) {
    $where[] = "(p.product_name LIKE :term OR p.description LIKE :term)";
    $params[':term'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $where);

$prodStmt = $pdo->prepare("SELECT p.*, pc.category_name,
                                  COALESCE(wi.price, 120.00) as current_price,
                                  COALESCE(wi.stock_quantity, 40) as stock_quantity,
                                  COALESCE(wi.is_available, 1) as is_available,
                                  (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.product_id = p.product_id) as total_sold
                           FROM products p
                           JOIN product_categories pc ON p.category_id = pc.category_id
                           LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id
                           WHERE {$whereSql}
                           GROUP BY p.product_id
                           ORDER BY p.product_id DESC");
$prodStmt->execute($params);
$products = $prodStmt->fetchAll();

// Attach gallery images for each product
foreach ($products as &$p) {
    $gStmt = $pdo->prepare("SELECT image_url, is_primary, display_order FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, display_order ASC");
    $gStmt->execute([':pid' => $p['product_id']]);
    $p['gallery'] = $gStmt->fetchAll();
}
unset($p);

// Filter by stock status if requested
if ($stockStatus === 'in_stock') {
    $products = array_filter($products, fn($p) => (int)$p['is_available'] === 1 && (float)$p['stock_quantity'] > 0);
} elseif ($stockStatus === 'sold_out') {
    $products = array_filter($products, fn($p) => (int)$p['is_available'] === 0 || (float)$p['stock_quantity'] <= 0);
}

// Produce Stats
$totalCount = count($products);
$inStockCount = count(array_filter($products, fn($p) => (int)$p['is_available'] === 1));
$soldOutCount = $totalCount - $inStockCount;

// Edit product item data if editing
$editProduct = null;
if ($editProductId > 0) {
    $eStmt = $pdo->prepare("SELECT p.*, COALESCE(wi.price, 100) as price, COALESCE(wi.stock_quantity, 30) as stock_quantity 
                            FROM products p 
                            LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id 
                            WHERE p.product_id = :pid AND p.farmer_id = :fid LIMIT 1");
    $eStmt->execute([':pid' => $editProductId, ':fid' => $farmerId]);
    $editProduct = $eStmt->fetch();
}
?>

<div class="farmer-content">

  <!-- Header & Toolbar -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Produce Catalog &amp; Weekly Inventory
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        Add farm-fresh produce, adjust weekly quantities, set recurring templates, and manage availability.
      </p>
    </div>
    <div>
      <button type="button" class="farmer-btn-primary" onclick="openProductModal()">
        <i data-lucide="plus-circle" style="width: 17px; height: 17px;"></i>
        <span>Add New Produce Listing</span>
      </button>
    </div>
  </div>

  <!-- Produce Inventory Overview Row -->
  <div class="farmer-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
    <div class="farmer-stat-card" style="padding: 1.25rem;">
      <span class="farmer-stat-label">Total Produce Listed</span>
      <div class="farmer-stat-value" style="font-size: 1.75rem; margin: 0.35rem 0;"><?= $totalCount ?></div>
      <div style="font-size: 0.75rem; color: var(--slate-400);">Catalog items</div>
    </div>
    <div class="farmer-stat-card" style="padding: 1.25rem;">
      <span class="farmer-stat-label">Active In-Stock</span>
      <div class="farmer-stat-value" style="font-size: 1.75rem; margin: 0.35rem 0; color: var(--primary-400);"><?= $inStockCount ?></div>
      <div style="font-size: 0.75rem; color: var(--primary-500);">Open for pre-orders</div>
    </div>
    <div class="farmer-stat-card" style="padding: 1.25rem;">
      <span class="farmer-stat-label">Temporarily Sold Out</span>
      <div class="farmer-stat-value" style="font-size: 1.75rem; margin: 0.35rem 0; color: #f59e0b;"><?= $soldOutCount ?></div>
      <div style="font-size: 0.75rem; color: var(--slate-400);">Yield exhausted</div>
    </div>
    <div class="farmer-stat-card" style="padding: 1.25rem;">
      <span class="farmer-stat-label">Categories Utilized</span>
      <div class="farmer-stat-value" style="font-size: 1.75rem; margin: 0.35rem 0; color: var(--sky-400);"><?= count($categories) ?></div>
      <div style="font-size: 0.75rem; color: var(--slate-400);">From Vegetables to Honey</div>
    </div>
  </div>

  <!-- Search & Category Filters -->
  <div class="farmer-card" style="padding: 1.25rem; margin-bottom: 2rem;">
    <form method="GET" action="" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
      <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap; flex: 1;">
        <!-- Search Input -->
        <div style="position: relative; min-width: 260px; flex: 1;">
          <input type="text" name="search" class="farmer-form-input" placeholder="Search heirloom tomatoes, honey, carrots..." value="<?= htmlspecialchars($searchTerm) ?>" style="padding-left: 2.25rem;">
          <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--slate-400);">
            <i data-lucide="search" style="width: 15px; height: 15px;"></i>
          </span>
        </div>

        <!-- Category Dropdown -->
        <div style="min-width: 200px;">
          <select name="category_id" class="farmer-form-select" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['category_id'] ?>" <?= ($selectedCat === (int)$cat['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Stock Status Dropdown -->
        <div style="min-width: 160px;">
          <select name="stock_status" class="farmer-form-select" onchange="this.form.submit()">
            <option value="all" <?= ($stockStatus === 'all') ? 'selected' : '' ?>>All Inventory</option>
            <option value="in_stock" <?= ($stockStatus === 'in_stock') ? 'selected' : '' ?>>In Stock</option>
            <option value="sold_out" <?= ($stockStatus === 'sold_out') ? 'selected' : '' ?>>Sold Out</option>
          </select>
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <button type="submit" class="farmer-btn-primary" style="padding: 0.65rem 1.15rem;">
          <i data-lucide="filter" style="width: 15px; height: 15px;"></i>
          <span>Filter</span>
        </button>
        <?php if (!empty($searchTerm) || $selectedCat > 0 || $stockStatus !== 'all'): ?>
          <a href="<?= BASE_URL ?>/farmer/products.php" class="farmer-btn-secondary" style="padding: 0.65rem 0.85rem;" title="Clear Filters">
            ✕
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Produce Cards Grid -->
  <?php if (empty($products)): ?>
    <div class="farmer-card" style="text-align: center; padding: 4rem 1.5rem; color: var(--slate-400);">
      <i data-lucide="sprout" style="width: 48px; height: 48px; margin-bottom: 0.75rem; color: var(--slate-500);"></i>
      <h4 style="color: var(--text-primary); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.35rem;">No Produce Listings Found</h4>
      <p style="font-size: 0.875rem; max-width: 450px; margin: 0 auto 1.5rem;">
        List your crops and artisanal farm goods to start receiving pre-orders from local families.
      </p>
      <button type="button" class="farmer-btn-primary" onclick="openProductModal()">
        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
        <span>Create First Produce Listing</span>
      </button>
    </div>
  <?php else: ?>
    <div class="farmer-products-grid">
      <?php foreach ($products as $p): ?>
        <div class="farmer-product-card" id="prodCard_<?= $p['product_id'] ?>">
          <!-- Media Thumbnail -->
          <div class="farmer-product-media">
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image_url'] ?: 'assets/images/cat-vegetables.svg') ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="farmer-product-img">
            <span class="farmer-product-category-tag">
              <?= htmlspecialchars($p['category_name']) ?>
            </span>
            <?php if (!empty($p['is_recurring_template'])): ?>
              <span style="position: absolute; top: 1rem; right: 1rem; background: rgba(34, 197, 94, 0.85); backdrop-filter: blur(8px); color: #ffffff; font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: var(--radius-full);" title="Recurring weekly harvest template">
                🔄 Recurring
              </span>
            <?php endif; ?>
          </div>

          <!-- Body Info -->
          <div class="farmer-product-body">
            <div>
              <h3 class="farmer-product-name"><?= htmlspecialchars($p['product_name']) ?></h3>
              <p class="farmer-product-desc">
                <?= htmlspecialchars($p['description'] ?: 'Freshly harvested organic produce directly from our sustainable farm soil.') ?>
              </p>
            </div>

            <div>
              <!-- Metrics Row -->
              <div class="farmer-product-metrics-row">
                <div>
                  <div class="farmer-product-price">Rs. <?= number_format($p['current_price'], 2) ?></div>
                  <div class="farmer-product-unit">per <?= htmlspecialchars($p['unit']) ?></div>
                </div>

                <div style="text-align: right;">
                  <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">
                    <?= (float)$p['stock_quantity'] ?> <?= htmlspecialchars($p['unit']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--slate-400);">weekly stock</div>
                </div>
              </div>

              <!-- Availability Toggle & Action Buttons -->
              <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding-top: 0.85rem; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                  <label class="farmer-toggle-switch">
                    <input type="checkbox" <?= $p['is_available'] ? 'checked' : '' ?> onchange="
                      const checked = this.checked;
                      FarmerApp.toggleStock(<?= $p['product_id'] ?>, checked);
                    ">
                    <span class="farmer-toggle-slider"></span>
                  </label>
                  <span style="font-size: 0.75rem; color: <?= $p['is_available'] ? 'var(--primary-400)' : 'var(--slate-400)' ?>; font-weight: 600;">
                    <?= $p['is_available'] ? 'In Stock' : 'Sold Out' ?>
                  </span>
                </div>

                <div class="farmer-action-group">
                  <button type="button" class="farmer-btn-action btn-action-view" onclick="openProductModal(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit Produce Details">
                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                    <span>Edit</span>
                  </button>
                  <button type="button" class="farmer-btn-action btn-action-decline" onclick="deleteProduct(<?= $p['product_id'] ?>, '<?= htmlspecialchars(addslashes($p['product_name'])) ?>')" title="Delete Produce Listing">
                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<!-- Modal: Add / Edit Produce Listing -->
<div class="farmer-modal-backdrop" id="productModal">
  <div class="farmer-modal">
    <div class="farmer-modal-header">
      <h4 class="farmer-modal-title" id="prodModalTitle">
        <i data-lucide="package-plus" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
        <span>Add Harvest Produce Item</span>
      </h4>
      <button type="button" class="farmer-modal-close-btn" onclick="closeProductModal()">✕</button>
    </div>
    <form id="productForm" onsubmit="submitProductForm(event)" enctype="multipart/form-data">
      <div class="farmer-modal-body">
        <input type="hidden" name="product_id" id="formProdId" value="0">
        <input type="hidden" name="existing_image" id="formExistingImage" value="">

        <!-- Produce Title -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="formProdName">Produce Name <span style="color:#ef4444;">*</span></label>
          <input type="text" class="farmer-form-input" id="formProdName" name="product_name" placeholder="e.g. Organic Heirloom Tomatoes, Raw Wildflower Honey" required>
        </div>

        <!-- Category & Unit -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="farmer-form-group">
            <label class="farmer-form-label" for="formCatId">Category <span style="color:#ef4444;">*</span></label>
            <select class="farmer-form-select" id="formCatId" name="category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="farmer-form-group">
            <label class="farmer-form-label" for="formUnit">Produce Unit <span style="color:#ef4444;">*</span></label>
            <select class="farmer-form-select" id="formUnit" name="unit" required>
              <option value="kg">kg (Kilogram)</option>
              <option value="bunch">bunch (Greens / Carrots)</option>
              <option value="dozen">dozen (Farm Eggs)</option>
              <option value="pack">pack (Butter / Berries)</option>
              <option value="jar">jar (Honey / Jam)</option>
              <option value="loaf">loaf (Artisanal Bread)</option>
              <option value="bundle">bundle (Culinary Herbs)</option>
              <option value="crate">crate (Bulk Wholesale)</option>
            </select>
          </div>
        </div>

        <!-- Price & Weekly Stock Quantity -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="farmer-form-group">
            <label class="farmer-form-label" for="formPrice">Base Price (PKR) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="0.01" min="1" class="farmer-form-input" id="formPrice" name="price" placeholder="150.00" required>
          </div>

          <div class="farmer-form-group">
            <label class="farmer-form-label" for="formQty">Weekly Stock Yield <span style="color:#ef4444;">*</span></label>
            <input type="number" step="0.5" min="1" class="farmer-form-input" id="formQty" name="stock_quantity" placeholder="50" required>
          </div>
        </div>

        <!-- Description -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="formDesc">Crop Description &amp; Farming Notes</label>
          <textarea class="farmer-form-textarea" id="formDesc" name="description" rows="3" placeholder="Grown pesticide-free, harvested at dawn of market day, non-GMO heirloom seeds..."></textarea>
        </div>

        <!-- Product Photos (5 Photos Allowance: 1 Compulsory + 4 Optional) -->
        <div class="farmer-form-group">
          <label class="farmer-form-label">
            <i data-lucide="camera" style="width:14px;height:14px;vertical-align:middle;color:var(--primary-500);"></i>
            Harvest Photos (1 Primary Required + Up to 4 Optional Gallery Shots)
          </label>

          <!-- Primary Photo (Compulsory) -->
          <div style="background:var(--bg-surface-elevated); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1rem; margin-bottom:1rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
              <span style="font-size:0.8125rem; font-weight:700; color:var(--text-primary);">
                ⭐ 1. Cover Photo <span id="photoRequiredLabel" style="color:#ef4444;">(Compulsory *)</span>
                <span id="photoOptionalLabel" style="color:var(--slate-400);font-weight:400;font-size:0.75rem;display:none;">(keep current or replace)</span>
              </span>
              <span style="font-size:0.72rem; color:var(--primary-400); font-weight:600;">Main Marketplace Showcase</span>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
              <div id="productImgPreviewWrap" style="width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; border: 2px dashed var(--border-color); background: var(--bg-surface); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <img id="productImgPreview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                <i id="productImgPlaceholder" data-lucide="image" style="width:28px;height:28px;color:var(--slate-500);"></i>
              </div>

              <div style="flex:1;min-width:180px;">
                <div onclick="document.getElementById('productPhotoInput').click()" style="border: 2px dashed var(--border-color); border-radius: var(--radius-md); padding: 0.85rem; text-align: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary-500)'" onmouseout="this.style.borderColor='var(--border-color)'">
                  <i data-lucide="upload" style="width:18px;height:18px;color:var(--primary-500);margin-bottom:0.25rem;"></i>
                  <div style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);">Select Cover Photo</div>
                  <div style="font-size:0.7rem;color:var(--slate-400);">JPG, PNG, WEBP &bull; Max 8MB</div>
                </div>
                <input type="file" id="productPhotoInput" name="product_photo" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;" onchange="previewProductPhoto(this)">
                <div id="productPhotoName" style="font-size:0.72rem;color:var(--primary-400);margin-top:0.35rem;display:none;"></div>
              </div>
            </div>
          </div>

          <!-- 4 Optional Additional Photos -->
          <div>
            <div style="font-size:0.8125rem; font-weight:700; color:var(--text-secondary); margin-bottom:0.5rem;">
              📸 Additional Gallery Photos <span style="font-weight:400; font-size:0.75rem; color:var(--slate-400);">(Optional &bull; Up to 4 extra angles / packaging / farm soil shots)</span>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(110px, 1fr)); gap:0.65rem;">
              <?php for ($slot = 2; $slot <= 5; $slot++): ?>
                <div style="background:var(--bg-surface-elevated); border:1px dashed var(--border-color); border-radius:var(--radius-md); padding:0.6rem; text-align:center; position:relative;">
                  <div id="extraPreviewWrap_<?= $slot ?>" style="width:100%; aspect-ratio:1; border-radius:6px; overflow:hidden; background:var(--bg-surface); display:flex; align-items:center; justify-content:center; margin-bottom:0.4rem;">
                    <img id="extraPreviewImg_<?= $slot ?>" src="" alt="" style="width:100%; height:100%; object-fit:cover; display:none;">
                    <span id="extraPlaceholder_<?= $slot ?>" style="font-size:0.75rem; color:var(--slate-500); font-weight:600;">+ Slot <?= $slot ?></span>
                  </div>
                  <button type="button" onclick="document.getElementById('extraInput_<?= $slot ?>').click()" class="farmer-btn-secondary" style="width:100%; padding:0.3rem 0.4rem; font-size:0.7rem;">
                    Upload <?= $slot ?>
                  </button>
                  <input type="file" id="extraInput_<?= $slot ?>" name="photo_<?= $slot ?>" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;" onchange="previewExtraPhoto(this, <?= $slot ?>)">
                </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>

        <!-- Recurring Weekly Template Checkbox -->
        <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.75rem;">
          <input type="checkbox" id="formRecurring" name="is_recurring_template" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary-500);">
          <label for="formRecurring" style="font-size: 0.84375rem; color: var(--text-primary); cursor: pointer; user-select: none;">
            <strong>Enable Recurring Weekly Template:</strong> Automatically restore this stock allocation for every scheduled weekend market day.
          </label>
        </div>
      </div>

      <div class="farmer-modal-footer">
        <button type="button" class="farmer-btn-secondary" onclick="closeProductModal()">Cancel</button>
        <button type="submit" class="farmer-btn-primary" id="prodSubmitBtn">
          <span id="prodSubmitBtnText">Save Produce Listing</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Product image live preview
function previewProductPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = document.getElementById('productImgPreview');
    const placeholder = document.getElementById('productImgPlaceholder');
    img.src = e.target.result;
    img.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);

  // Show filename
  const nameEl = document.getElementById('productPhotoName');
  if (nameEl) {
    nameEl.textContent = '📎 ' + file.name;
    nameEl.style.display = 'block';
  }
}

// Extra gallery photos live preview (Slots 2 to 5)
function previewExtraPhoto(input, slot) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = document.getElementById(`extraPreviewImg_${slot}`);
    const placeholder = document.getElementById(`extraPlaceholder_${slot}`);
    if (img) {
      img.src = e.target.result;
      img.style.display = 'block';
    }
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

// Open Add / Edit Modal
function openProductModal(prod = null) {
  const modal = document.getElementById('productModal');
  const title = document.getElementById('prodModalTitle');
  const btnText = document.getElementById('prodSubmitBtnText');
  const reqLabel = document.getElementById('photoRequiredLabel');
  const optLabel = document.getElementById('photoOptionalLabel');
  const imgPreview = document.getElementById('productImgPreview');
  const imgPlaceholder = document.getElementById('productImgPlaceholder');
  const photoNameEl = document.getElementById('productPhotoName');

  // Reset photo field and name display
  document.getElementById('productPhotoInput').value = '';
  if (photoNameEl) { photoNameEl.textContent = ''; photoNameEl.style.display = 'none'; }

  // Reset extra photo slots
  for (let s = 2; s <= 5; s++) {
    const extraIn = document.getElementById(`extraInput_${s}`);
    if (extraIn) extraIn.value = '';
    const extraImg = document.getElementById(`extraPreviewImg_${s}`);
    if (extraImg) { extraImg.src = ''; extraImg.style.display = 'none'; }
    const extraPh = document.getElementById(`extraPlaceholder_${s}`);
    if (extraPh) extraPh.style.display = 'block';
  }

  if (prod) {
    document.getElementById('formProdId').value = prod.product_id;
    document.getElementById('formExistingImage').value = prod.image_url || '';
    document.getElementById('formProdName').value = prod.product_name;
    document.getElementById('formCatId').value = prod.category_id;
    document.getElementById('formUnit').value = prod.unit;
    document.getElementById('formPrice').value = prod.current_price || prod.price;
    document.getElementById('formQty').value = prod.stock_quantity;
    document.getElementById('formDesc').value = prod.description || '';
    document.getElementById('formRecurring').checked = (parseInt(prod.is_recurring_template) === 1);

    // Show current product image in preview
    if (prod.image_url) {
      imgPreview.src = window.BASE_URL + '/' + prod.image_url;
      imgPreview.style.display = 'block';
      if (imgPlaceholder) imgPlaceholder.style.display = 'none';
    } else {
      imgPreview.style.display = 'none';
      if (imgPlaceholder) imgPlaceholder.style.display = '';
    }

    // Populate existing extra gallery photos
    if (prod.gallery && prod.gallery.length > 0) {
      let slotIdx = 2;
      prod.gallery.forEach(g => {
        if (!parseInt(g.is_primary) && slotIdx <= 5) {
          const exImg = document.getElementById(`extraPreviewImg_${slotIdx}`);
          const exPh = document.getElementById(`extraPlaceholder_${slotIdx}`);
          if (exImg) {
            exImg.src = window.BASE_URL + '/' + g.image_url;
            exImg.style.display = 'block';
          }
          if (exPh) exPh.style.display = 'none';
          slotIdx++;
        }
      });
    }

    // Photo is optional when editing (existing image preserved)
    if (reqLabel) reqLabel.style.display = 'none';
    if (optLabel) optLabel.style.display = 'inline';
    document.getElementById('productPhotoInput').removeAttribute('required');

    title.innerHTML = '<i data-lucide="edit" style="width:20px;height:20px;color:var(--primary-500);"></i> <span>Edit Produce Listing</span>';
    btnText.textContent = 'Update Produce Listing';
  } else {
    document.getElementById('productForm').reset();
    document.getElementById('formProdId').value = '0';
    document.getElementById('formExistingImage').value = '';

    // Reset image preview
    imgPreview.src = '';
    imgPreview.style.display = 'none';
    if (imgPlaceholder) imgPlaceholder.style.display = '';

    // Photo required for new product
    if (reqLabel) reqLabel.style.display = 'inline';
    if (optLabel) optLabel.style.display = 'none';

    title.innerHTML = '<i data-lucide="package-plus" style="width:20px;height:20px;color:var(--primary-500);"></i> <span>Add Harvest Produce Item</span>';
    btnText.textContent = 'Publish Produce Listing';
  }

  if (typeof lucide !== 'undefined') lucide.createIcons();
  modal.classList.add('active');
}

function closeProductModal() {
  document.getElementById('productModal').classList.remove('active');
}

// Submit Product Form via AJAX (multipart for file upload)
function submitProductForm(e) {
  e.preventDefault();
  const form = document.getElementById('productForm');
  const btn = document.getElementById('prodSubmitBtn');
  btn.disabled = true;

  const prodId = parseInt(document.getElementById('formProdId').value);
  const photoInput = document.getElementById('productPhotoInput');
  const hasNewPhoto = photoInput.files && photoInput.files[0];
  const hasExisting = document.getElementById('formExistingImage').value;

  // Validate: new product must have photo
  if (prodId === 0 && !hasNewPhoto) {
    FarmerApp.showToast('error', 'Photo Required', 'Please upload a product photo so customers can see what they\'re ordering.');
    btn.disabled = false;
    return;
  }

  const formData = new FormData(form);

  fetch(window.BASE_URL + '/farmer/api/save_product.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Catalog Updated', data.message);
      closeProductModal();
      setTimeout(() => location.reload(), 700);
    } else {
      FarmerApp.showToast('error', 'Validation Error', data.message || 'Could not save produce.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Server Error', 'Please check connection.');
  });
}

// Delete Product
function deleteProduct(prodId, prodName) {
  if (!confirm(`Are you sure you want to permanently delete "${prodName}" from your produce listings?`)) {
    return;
  }

  const formData = new FormData();
  formData.append('product_id', prodId);

  fetch(window.BASE_URL + '/farmer/api/delete_product.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Produce Deleted', data.message);
      const card = document.getElementById('prodCard_' + prodId);
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(() => card.remove(), 300);
      } else {
        location.reload();
      }
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Cannot delete produce listing.');
    }
  })
  .catch(err => {
    FarmerApp.showToast('error', 'Error', 'Failed to communicate with server.');
  });
}

// Check URL param if action=new
document.addEventListener('DOMContentLoaded', function() {
  <?php if ($action === 'new'): ?>
    openProductModal();
  <?php elseif ($editProduct): ?>
    openProductModal(<?= json_encode($editProduct) ?>);
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
