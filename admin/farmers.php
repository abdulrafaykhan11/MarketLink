<?php
/**
 * MarketLink - Admin Farmer Verification & Stall Management
 * SRS Section 1.6: Admin can view, approve, or suspend Farmer registrations before they can list products.
 */

$pageTitle = 'Farmer Stall Verification';
$activeNav = 'farmers';
require_once __DIR__ . '/includes/admin_header.php';

$filterStatus = trim($_GET['status'] ?? 'all');
$searchQuery  = trim($_GET['search'] ?? '');

// Build query
$whereClauses = [];
$params = [];

if (!empty($filterStatus) && $filterStatus !== 'all') {
    $whereClauses[] = "fp.approval_status = :status";
    $params[':status'] = $filterStatus;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(fp.stall_name LIKE :q_stall OR fp.contact_person LIKE :q_contact OR u.email LIKE :q_email OR fp.business_phone LIKE :q_phone)";
    $searchPattern = "%{$searchQuery}%";
    $params[':q_stall'] = $searchPattern;
    $params[':q_contact'] = $searchPattern;
    $params[':q_email'] = $searchPattern;
    $params[':q_phone'] = $searchPattern;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT fp.*, u.username, u.email, u.status as user_status, u.profile_image,
               (SELECT COUNT(*) FROM products p WHERE p.farmer_id = fp.farmer_id) as total_products,
               (SELECT COUNT(*) FROM orders o WHERE o.farmer_id = fp.farmer_id) as total_orders
        FROM farmer_profiles fp
        JOIN users u ON fp.farmer_id = u.user_id
        {$whereSql}
        ORDER BY CASE WHEN fp.approval_status = 'pending' THEN 1 ELSE 2 END, fp.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$farmers = $stmt->fetchAll();

// Counts for filter pills
$counts = [];
$statusRows = $pdo->query("SELECT approval_status, COUNT(*) as c FROM farmer_profiles GROUP BY approval_status")->fetchAll();
foreach ($statusRows as $r) {
    $counts[$r['approval_status']] = (int)$r['c'];
}
$allCount = array_sum($counts);
?>

<!-- Page Header -->
<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="tractor" style="color:var(--admin-accent);"></i>
      Farmer Stall Verification &amp; Producer Profiles
    </h1>
    <p>Verify farm credentials, approve producer applications, or suspend non-compliant seller accounts.</p>
  </div>
</div>

<!-- Main Table Container Card -->
<div class="admin-card">
  <!-- Toolbar: Search & Status Filters -->
  <div class="admin-toolbar">
    <div class="admin-filter-tabs">
      <a href="?status=all<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'all') ? 'active' : '' ?>">
        All Farmers (<?= $allCount ?>)
      </a>
      <a href="?status=pending<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'pending') ? 'active' : '' ?>">
        Pending (<?= $counts['pending'] ?? 0 ?>)
      </a>
      <a href="?status=approved<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'approved') ? 'active' : '' ?>">
        Approved (<?= $counts['approved'] ?? 0 ?>)
      </a>
      <a href="?status=suspended<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'suspended') ? 'active' : '' ?>">
        Suspended (<?= $counts['suspended'] ?? 0 ?>)
      </a>
      <a href="?status=rejected<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'rejected') ? 'active' : '' ?>">
        Rejected (<?= $counts['rejected'] ?? 0 ?>)
      </a>
    </div>

    <form method="GET" class="admin-search-wrapper">
      <?php if ($filterStatus !== 'all'): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <?php endif; ?>
      <span class="admin-search-icon"><i data-lucide="search" style="width:16px; height:16px;"></i></span>
      <input type="text" name="search" class="admin-search-input" placeholder="Search stall, farmer, phone, email..." value="<?= htmlspecialchars($searchQuery) ?>">
    </form>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Stall / Farm Details</th>
          <th>Contact &amp; Credentials</th>
          <th>Location &amp; Coordinates</th>
          <th>Catalog &amp; Orders</th>
          <th>Status</th>
          <th style="text-align:right;">Admin Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($farmers)): ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No farmer accounts found matching the criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($farmers as $f): ?>
            <tr id="farmer-row-<?= $f['farmer_id'] ?>">
              <td>
                <div style="font-weight:700; font-size:0.95rem; color:var(--admin-text-main);">
                  <?= htmlspecialchars($f['stall_name']) ?>
                </div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  Cutoff: <?= htmlspecialchars($f['order_cutoff_time']) ?> &bull; Joined <?= date('M Y', strtotime($f['created_at'])) ?>
                </div>
              </td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($f['contact_person']) ?></div>
                <div style="font-size:0.78125rem; color:var(--admin-text-muted);"><?= htmlspecialchars($f['business_email'] ?: $f['email']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($f['business_phone']) ?></div>
              </td>
              <td style="max-width:220px;">
                <div style="font-size:0.8125rem; color:var(--admin-text-muted); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= htmlspecialchars($f['address']) ?>">
                  <?= htmlspecialchars($f['address']) ?>
                </div>
                <div style="font-size:0.7rem; color:var(--admin-accent); font-family:monospace; margin-top:2px;">
                  📍 <?= number_format($f['latitude'], 4) ?>, <?= number_format($f['longitude'], 4) ?>
                </div>
              </td>
              <td>
                <div style="font-size:0.85rem; font-weight:600;">
                  <span>📦 <?= $f['total_products'] ?></span> products
                </div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  <span>🛒 <?= $f['total_orders'] ?></span> pre-orders
                </div>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($f['approval_status']) ?>" id="status-pill-<?= $f['farmer_id'] ?>">
                  <?= ucfirst($f['approval_status']) ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                  <?php if ($f['approval_status'] !== 'approved'): ?>
                    <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" 
                            title="Approve Farmer Registration"
                            onclick="updateFarmer(<?= $f['farmer_id'] ?>, 'approve')">
                      <i data-lucide="check"></i> Approve
                    </button>
                  <?php endif; ?>

                  <?php if ($f['approval_status'] === 'approved'): ?>
                    <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                            title="Suspend Seller Privileges"
                            onclick="updateFarmer(<?= $f['farmer_id'] ?>, 'suspend')">
                      <i data-lucide="circle-pause"></i> Suspend
                    </button>
                  <?php endif; ?>

                  <?php if ($f['approval_status'] === 'pending'): ?>
                    <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                            style="color:var(--admin-rose); border-color:rgba(239,68,68,0.3);"
                            title="Reject Registration"
                            onclick="updateFarmer(<?= $f['farmer_id'] ?>, 'reject')">
                      <i data-lucide="x"></i> Reject
                    </button>
                  <?php endif; ?>

                  <?php if ($f['approval_status'] === 'suspended'): ?>
                    <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" 
                            title="Reactivate Farmer Stall"
                            onclick="updateFarmer(<?= $f['farmer_id'] ?>, 'activate')">
                      <i data-lucide="play"></i> Reactivate
                    </button>
                  <?php endif; ?>
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
async function updateFarmer(farmerId, action) {
  let reason = '';
  if (action === 'reject' || action === 'suspend') {
    reason = prompt(`Please provide an optional reason for ${action}ing this farmer:`) || '';
    if (reason === null) return; // cancelled
  } else {
    if (!confirm(`Are you sure you want to ${action} this farmer profile?`)) return;
  }

  try {
    const fd = new FormData();
    fd.append('farmer_id', farmerId);
    fd.append('action', action);
    fd.append('reason', reason);

    const res = await fetch('<?= BASE_URL ?>/admin/api/farmer_action.php', {
      method: 'POST',
      body: fd
    });

    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showAdminToast(data.message || 'Operation failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network connection failed', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
