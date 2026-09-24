<?php
/**
 * MarketLink - Admin Products Catalog & Content Moderation
 * SRS Section 1.6: Admin can view and remove inappropriate product listings that violate platform guidelines.
 */

$pageTitle = 'Products Catalog';
$activeNav = 'products';
require_once __DIR__ . '/includes/admin_header.php';

$filterCategory = (int)($_GET['category'] ?? 0);
$filterFarmer   = (int)($_GET['farmer'] ?? 0);
$searchQuery    = trim($_GET['search'] ?? '');

$whereClauses = [];
$params = [];

if ($filterCategory > 0) {
    $whereClauses[] = "p.category_id = :cat";
    $params[':cat'] = $filterCategory;
}

if ($filterFarmer > 0) {
    $whereClauses[] = "p.farmer_id = :fid";
    $params[':fid'] = $filterFarmer;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(p.product_name LIKE :q OR p.description LIKE :q OR fp.stall_name LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT p.*, pc.category_name, fp.stall_name, fp.contact_person
        FROM products p
        JOIN product_categories pc ON p.category_id = pc.category_id
        JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
        {$whereSql}
        ORDER BY p.product_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Categories & Farmers for dropdowns
$categories = $pdo->query("SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC")->fetchAll();
$farmersList = $pdo->query("SELECT farmer_id, stall_name FROM farmer_profiles ORDER BY stall_name ASC")->fetchAll();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="package" style="color:var(--admin-accent);"></i>
      Platform Products &amp; Content Moderation
    </h1>
    <p>Monitor all agricultural produce, inspect listings, and moderate or remove products violating guidelines.</p>
  </div>
</div>

<div class="admin-card">
  <!-- Toolbar: Search & Dropdown Filters -->
  <div class="admin-toolbar">
    <form method="GET" style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; width:100%;">
      <div class="admin-search-wrapper" style="flex:1; min-width:240px;">
        <span class="admin-search-icon"><i data-lucide="search" style="width:16px; height:16px;"></i></span>
        <input type="text" name="search" class="admin-search-input" placeholder="Search product name, description, stall..." value="<?= htmlspecialchars($searchQuery) ?>">
      </div>

      <select name="category" class="admin-form-control" style="width:auto; min-width:160px;" onchange="this.form.submit()">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['category_id'] ?>" <?= ($filterCategory === (int)$c['category_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="farmer" class="admin-form-control" style="width:auto; min-width:180px;" onchange="this.form.submit()">
        <option value="0">All Producer Stalls</option>
        <?php foreach ($farmersList as $f): ?>
          <option value="<?= $f['farmer_id'] ?>" <?= ($filterFarmer === (int)$f['farmer_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($f['stall_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <?php if (!empty($searchQuery) || $filterCategory > 0 || $filterFarmer > 0): ?>
        <a href="<?= BASE_URL ?>/admin/products.php" class="admin-btn admin-btn-secondary admin-btn-sm">
          Reset Filters
        </a>
      <?php endif; ?>
    </form>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Product Item</th>
          <th>Category</th>
          <th>Farmer Stall</th>
          <th>Unit / Measure</th>
          <th>Template Type</th>
          <th>Listed Date</th>
          <th style="text-align:right;">Content Moderation</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No products found matching the specified criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr id="prod-row-<?= $p['product_id'] ?>">
              <td>
                <div style="display:flex; align-items:center; gap:0.85rem;">
                  <div style="width:44px; height:44px; border-radius:var(--radius-md); background:var(--admin-input-bg); border:1px solid var(--admin-card-border); overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <?php if (!empty($p['image_url'])): ?>
                      <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/logo.svg';">
                    <?php else: ?>
                      <i data-lucide="package" style="color:var(--admin-text-subtle); width:20px; height:20px;"></i>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div style="font-weight:700; color:var(--admin-text-main); font-size:0.95rem;">
                      <?= htmlspecialchars($p['product_name']) ?>
                    </div>
                    <div style="font-size:0.75rem; color:var(--admin-text-subtle); max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                      <?= htmlspecialchars($p['description'] ?: 'No description provided') ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-size:0.75rem; font-weight:700; padding:0.2rem 0.55rem; border-radius:var(--radius-full); background:rgba(99,102,241,0.15); color:#818cf8;">
                  <?= htmlspecialchars($p['category_name']) ?>
                </span>
              </td>
              <td>
                <div style="font-weight:600; font-size:0.85rem; color:var(--admin-emerald);"><?= htmlspecialchars($p['stall_name']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($p['contact_person']) ?></div>
              </td>
              <td style="font-size:0.8125rem; font-weight:600; color:var(--admin-text-main);">
                <?= htmlspecialchars($p['unit']) ?>
              </td>
              <td>
                <?php if ($p['is_recurring_template']): ?>
                  <span style="font-size:0.75rem; color:var(--admin-emerald); font-weight:600;">✓ Weekly Recurring</span>
                <?php else: ?>
                  <span style="font-size:0.75rem; color:var(--admin-text-subtle);">One-time harvest</span>
                <?php endif; ?>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle);">
                <?= date('M d, Y', strtotime($p['created_at'])) ?>
              </td>
              <td style="text-align:right;">
                <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                        title="Remove product for platform guideline violation"
                        onclick="moderateProduct(<?= $p['product_id'] ?>, '<?= htmlspecialchars(addslashes($p['product_name'])) ?>')">
                  <i data-lucide="trash-2"></i> Remove Listing
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function moderateProduct(productId, productName) {
  const reason = prompt(`Reason for removing "${productName}" from the platform catalog (sent to farmer):`, 'Violates platform product quality guidelines');
  if (reason === null) return; // user cancelled

  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('product_id', productId);
    fd.append('reason', reason);

    const res = await fetch('<?= BASE_URL ?>/admin/api/product_action.php', {
      method: 'POST',
      body: fd
    });

    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      const row = document.getElementById(`prod-row-${productId}`);
      if (row) {
        row.style.opacity = '0';
        row.style.transform = 'scale(0.95)';
        row.style.transition = 'all 0.3s ease';
        setTimeout(() => row.remove(), 300);
      }
    } else {
      showAdminToast(data.message || 'Moderation failed', 'error');
    }
  } catch (e) {
    showAdminToast('Network connection error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
