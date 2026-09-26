<?php
/**
 * MarketLink - Admin Customer Reviews & Content Moderation
 * SRS Section 1.6: Admin can view, moderate, and control Homepage Testimonials.
 */

$pageTitle = 'Review Moderation & Testimonials';
$activeNav = 'reviews';
require_once __DIR__ . '/includes/admin_header.php';

$type   = trim($_GET['type'] ?? 'farmer'); // 'farmer' or 'product'
$filter = trim($_GET['filter'] ?? 'all');   // 'all' or 'featured'

if ($type === 'product') {
    $sql = "SELECT pr.review_id, pr.order_id, pr.rating, pr.comment as review_comment, '' as farmer_response,
                   pr.is_moderated, 0 as is_featured, pr.created_at,
                   u.username as customer_name,
                   p.product_name,
                   fp.stall_name
            FROM product_reviews pr
            JOIN users u ON pr.customer_id = u.user_id
            JOIN products p ON pr.product_id = p.product_id
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            ORDER BY pr.created_at DESC";
} else {
    $type = 'farmer';
    $where = ($filter === 'featured') ? "WHERE fr.is_featured = 1" : "";
    $sql = "SELECT fr.review_id, fr.order_id, fr.rating, fr.review_comment, fr.farmer_response,
                   fr.is_moderated, COALESCE(fr.is_featured, 0) as is_featured, fr.created_at,
                   u.username as customer_name,
                   fp.stall_name
            FROM farmer_reviews fr
            JOIN users u ON fr.customer_id = u.user_id
            JOIN farmer_profiles fp ON fr.farmer_id = fp.farmer_id
            {$where}
            ORDER BY fr.is_featured DESC, fr.rating DESC, fr.created_at DESC";
}

$reviews = $pdo->query($sql)->fetchAll();

// Counts
$farmerRevCount   = (int)$pdo->query("SELECT COUNT(*) FROM farmer_reviews")->fetchColumn();
$featuredRevCount = (int)$pdo->query("SELECT COUNT(*) FROM farmer_reviews WHERE is_featured = 1")->fetchColumn();
$prodRevCount     = (int)$pdo->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="star" style="color:var(--admin-accent);"></i>
      Customer Reviews &amp; Homepage Testimonials
    </h1>
    <p>Moderate community feedback and choose the top 5–6 stall reviews featured on the homepage testimonial section.</p>
  </div>
</div>

<div class="admin-card">
  <div class="admin-toolbar">
    <div class="admin-filter-tabs">
      <a href="?type=farmer&filter=all" class="admin-filter-tab <?= ($type === 'farmer' && $filter !== 'featured') ? 'active' : '' ?>">
        All Stall Reviews (<?= $farmerRevCount ?>)
      </a>
      <a href="?type=farmer&filter=featured" class="admin-filter-tab <?= ($type === 'farmer' && $filter === 'featured') ? 'active' : '' ?>" style="color: <?= ($filter === 'featured') ? '#f59e0b' : '' ?>;">
        ⭐ Homepage Testimonials (<?= $featuredRevCount ?> / 6 Active)
      </a>
      <a href="?type=product" class="admin-filter-tab <?= ($type === 'product') ? 'active' : '' ?>">
        Product Produce Reviews (<?= $prodRevCount ?>)
      </a>
    </div>
  </div>

  <?php if ($type === 'farmer' && $featuredRevCount >= 6): ?>
    <div style="margin: 0.5rem 1.5rem 1.25rem 1.5rem; padding: 1rem 1.25rem; background: rgba(245, 158, 11, 0.1); border: 1.5px solid rgba(245, 158, 11, 0.35); border-radius: 12px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <span style="font-size:1.35rem; line-height:1;">⚠️</span>
        <div style="font-size:0.875rem; line-height:1.45; color:var(--admin-text-main);">
          <strong style="color:#f59e0b;">Maximum Limit Reached (6/6 Active Slots):</strong>
          You currently have 6 reviews featured on the homepage. To feature a different review, click <em style="color:#f59e0b; font-weight:700;">"Unfeature"</em> on one of your active testimonials first.
        </div>
      </div>
      <a href="?type=farmer&filter=featured" class="admin-btn admin-btn-sm" style="background:rgba(245,158,11,0.2); color:#f59e0b; border:1px solid rgba(245,158,11,0.4); font-weight:700;">
        Manage Active (<?= $featuredRevCount ?>)
      </a>
    </div>
  <?php endif; ?>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Rating</th>
          <th>Subject / Stall</th>
          <th>Review Content &amp; Farmer Response</th>
          <th>Date</th>
          <th>Moderation &amp; Homepage</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reviews)): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No reviews found in this view.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($reviews as $r): ?>
            <tr id="review-row-<?= $r['review_id'] ?>">
              <td>
                <div style="font-weight:700; color:var(--admin-text-main);"><?= htmlspecialchars($r['customer_name']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">Order #<?= $r['order_id'] ?></div>
              </td>
              <td>
                <div style="color:#f59e0b; font-size:1rem; letter-spacing:1px; white-space:nowrap;">
                  <?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?>
                </div>
                <span style="font-size:0.75rem; font-weight:700; color:var(--admin-text-subtle);"><?= $r['rating'] ?>.0 / 5.0</span>
              </td>
              <td>
                <div style="font-weight:600; color:var(--admin-emerald);"><?= htmlspecialchars($r['stall_name']) ?></div>
                <?php if (!empty($r['product_name'])): ?>
                  <div style="font-size:0.75rem; color:var(--admin-text-subtle);">Produce: <?= htmlspecialchars($r['product_name']) ?></div>
                <?php endif; ?>
              </td>
              <td style="max-width:320px;">
                <div style="font-size:0.875rem; color:var(--admin-text-main); line-height:1.4;">
                  "<?= htmlspecialchars($r['review_comment'] ?: 'No written comment.') ?>"
                </div>
                <?php if (!empty($r['farmer_response'])): ?>
                  <div style="margin-top:0.4rem; padding:0.4rem 0.65rem; background:rgba(16,185,129,0.1); border-left:2.5px solid var(--admin-emerald); border-radius:4px; font-size:0.75rem; color:var(--admin-text-muted);">
                    <strong style="color:var(--admin-emerald);">Farmer reply:</strong> <?= htmlspecialchars($r['farmer_response']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle); white-space:nowrap;">
                <?= date('M d, Y', strtotime($r['created_at'])) ?>
              </td>
              <td>
                <div style="display:flex; flex-direction:column; gap:0.35rem; align-items:flex-start;">
                  <?php if ($r['is_moderated']): ?>
                    <span class="status-pill active" id="rev-pill-<?= $r['review_id'] ?>">Published</span>
                  <?php else: ?>
                    <span class="status-pill suspended" id="rev-pill-<?= $r['review_id'] ?>">Hidden by Admin</span>
                  <?php endif; ?>

                  <?php if ($type === 'farmer'): ?>
                    <?php if (!empty($r['is_featured'])): ?>
                      <span class="status-pill" style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.35); font-weight:700; font-size:0.7rem;">
                        ★ Homepage Testimonial
                      </span>
                    <?php else: ?>
                      <span style="font-size:0.7rem; color:var(--admin-text-subtle);">Standard</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end; flex-wrap:wrap;">
                  <?php if ($type === 'farmer'): ?>
                    <?php if (!empty($r['is_featured'])): ?>
                      <button type="button" class="admin-btn admin-btn-sm" 
                              style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.35);"
                              title="Remove from Homepage Testimonials"
                              onclick="toggleFeaturedReview(<?= $r['review_id'] ?>, 0)">
                        <i data-lucide="star-off"></i> Unfeature
                      </button>
                    <?php else: ?>
                      <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                              style="color:#f59e0b; border-color:rgba(245,158,11,0.35);"
                              title="Feature as Top Testimonial on Homepage"
                              onclick="toggleFeaturedReview(<?= $r['review_id'] ?>, 1)">
                        <i data-lucide="star"></i> Feature on Home
                      </button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if ($r['is_moderated']): ?>
                    <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                            style="color:var(--admin-rose); border-color:rgba(239,68,68,0.3);"
                            title="Hide review from public view"
                            onclick="moderateReview('<?= $type ?>', <?= $r['review_id'] ?>, 'toggle_visibility', 0)">
                      <i data-lucide="eye-off"></i> Hide
                    </button>
                  <?php else: ?>
                    <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" 
                            title="Make review visible"
                            onclick="moderateReview('<?= $type ?>', <?= $r['review_id'] ?>, 'toggle_visibility', 1)">
                      <i data-lucide="eye"></i> Publish
                    </button>
                  <?php endif; ?>

                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                          title="Permanently delete review"
                          onclick="moderateReview('<?= $type ?>', <?= $r['review_id'] ?>, 'delete')">
                    <i data-lucide="trash-2"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function moderateReview(revType, revId, action, newStatus = 1) {
  if (action === 'delete' && !confirm('Are you sure you want to permanently delete this review?')) return;

  try {
    const fd = new FormData();
    fd.append('review_type', revType);
    fd.append('review_id', revId);
    fd.append('action', action);
    if (action === 'toggle_visibility') {
      fd.append('is_moderated', newStatus);
    }

    const res = await fetch('<?= BASE_URL ?>/admin/api/review_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 400);
    } else {
      showAdminToast(data.message || 'Operation failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network communication error', 'error');
  }
}

const MAX_HOMEPAGE_TESTIMONIALS = 6;
let currentActiveFeaturedCount = <?= $featuredRevCount ?>;

async function toggleFeaturedReview(revId, newStatus) {
  // Validate maximum limit of 6 homepage testimonials
  if (newStatus === 1 && currentActiveFeaturedCount >= MAX_HOMEPAGE_TESTIMONIALS) {
    showAdminToast('Maximum limit reached! Only 6 reviews can be published as testimonials on the homepage. Please unfeature an existing review first.', 'error');
    return;
  }

  try {
    const fd = new FormData();
    fd.append('review_type', 'farmer');
    fd.append('review_id', revId);
    fd.append('action', 'toggle_featured');
    fd.append('is_featured', newStatus);

    const res = await fetch('<?= BASE_URL ?>/admin/api/review_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 400);
    } else {
      showAdminToast(data.message || 'Maximum limit reached! Only 6 reviews can be published as testimonials.', 'error');
    }
  } catch (err) {
    showAdminToast('Network communication error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
