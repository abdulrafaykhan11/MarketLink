<?php
/**
 * MarketLink - Admin Master Product Categories Management
 * SRS Section 1.6: Admin can manage master data such as product categories.
 */

$pageTitle = 'Master Categories';
$activeNav = 'categories';
require_once __DIR__ . '/includes/admin_header.php';

$sql = "SELECT pc.*, 
               COUNT(p.product_id) as total_products
        FROM product_categories pc
        LEFT JOIN products p ON pc.category_id = p.category_id
        GROUP BY pc.category_id
        ORDER BY pc.category_name ASC";
$categories = $pdo->query($sql)->fetchAll();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="tags" style="color:var(--admin-accent);"></i>
      Master Product Categories
    </h1>
    <p>Define produce taxonomy, manage grocery departments, and organize marketplace navigation.</p>
  </div>

  <div class="admin-header-actions">
    <button type="button" class="admin-btn admin-btn-primary" onclick="openCategoryModal()">
      <i data-lucide="plus-circle"></i>
      <span>Add New Category</span>
    </button>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="list" style="color:var(--admin-emerald);"></i>
        Configured Produce Categories (<?= count($categories) ?>)
      </h3>
      <p class="admin-card-subtitle">Master taxonomy used across customer discovery and farmer inventories</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Description</th>
          <th>Total Listings</th>
          <th>Created Date</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td>
              <div style="font-weight:700; color:var(--admin-text-main); font-size:0.95rem;">
                <?= htmlspecialchars($c['category_name']) ?>
              </div>
              <div style="font-size:0.75rem; color:var(--admin-text-subtle);">ID #<?= $c['category_id'] ?></div>
            </td>
            <td style="max-width:320px; font-size:0.85rem; color:var(--admin-text-muted);">
              <?= htmlspecialchars($c['description'] ?: 'No category description provided.') ?>
            </td>
            <td>
              <span style="font-weight:700; font-size:0.9rem; color:var(--admin-accent);">
                📦 <?= $c['total_products'] ?> <?= $c['total_products'] == 1 ? 'item' : 'items' ?>
              </span>
            </td>
            <td style="font-size:0.8125rem; color:var(--admin-text-subtle);">
              <?= date('M d, Y', strtotime($c['created_at'])) ?>
            </td>
            <td>
              <span class="status-pill <?= $c['is_active'] ? 'active' : 'inactive' ?>">
                <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                        onclick='editCategory(<?= json_encode($c) ?>)'>
                  <i data-lucide="edit-3"></i> Edit
                </button>
                <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                        onclick="deleteCategory(<?= $c['category_id'] ?>)">
                  <i data-lucide="trash-2"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add / Edit Category -->
<div class="admin-modal-backdrop" id="categoryModalBackdrop">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <h3 id="categoryModalTitle">Add Product Category</h3>
      <button type="button" class="admin-modal-close" onclick="closeCategoryModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form onsubmit="saveCategory(event)">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="category_id" id="cat_id" value="0">

      <div class="admin-modal-body">
        <div class="admin-form-group">
          <label class="admin-form-label">Category Name *</label>
          <input type="text" name="category_name" id="cat_name" class="admin-form-control" required placeholder="e.g. Organic Root Vegetables">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Category Description</label>
          <textarea name="description" id="cat_desc" rows="3" class="admin-form-control" placeholder="Short description for customer browsing..."></textarea>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
          <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
            <input type="checkbox" name="is_active" id="cat_active" value="1" checked style="accent-color:var(--admin-accent); width:18px; height:18px;">
            <span style="font-size:0.875rem; font-weight:600; color:var(--admin-text-main);">
              Category is Active (visible in marketplace filters)
            </span>
          </label>
        </div>
      </div>

      <div class="admin-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeCategoryModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i data-lucide="save"></i> Save Category
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCategoryModal() {
  document.getElementById('categoryModalTitle').textContent = 'Add Product Category';
  document.getElementById('cat_id').value = '0';
  document.getElementById('cat_name').value = '';
  document.getElementById('cat_desc').value = '';
  document.getElementById('cat_active').checked = true;
  document.getElementById('categoryModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function editCategory(c) {
  document.getElementById('categoryModalTitle').textContent = 'Edit Product Category';
  document.getElementById('cat_id').value = c.category_id;
  document.getElementById('cat_name').value = c.category_name;
  document.getElementById('cat_desc').value = c.description || '';
  document.getElementById('cat_active').checked = parseInt(c.is_active) === 1;
  document.getElementById('categoryModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeCategoryModal() {
  document.getElementById('categoryModalBackdrop').classList.remove('open');
}

async function saveCategory(e) {
  e.preventDefault();
  const fd = new FormData(e.target);

  try {
    const res = await fetch('<?= BASE_URL ?>/admin/api/category_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      closeCategoryModal();
      setTimeout(() => window.location.reload(), 500);
    } else {
      showAdminToast(data.message || 'Error occurred', 'error');
    }
  } catch (err) {
    showAdminToast('Network connection error', 'error');
  }
}

async function deleteCategory(id) {
  if (!confirm('Are you sure you want to remove this category?')) return;

  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('category_id', id);

    const res = await fetch('<?= BASE_URL ?>/admin/api/category_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 500);
    } else {
      showAdminToast(data.message || 'Delete failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network connection error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
