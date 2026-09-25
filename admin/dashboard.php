<?php
/**
 * MarketLink - Super Admin Executive Dashboard
 * Enterprise Silicon Valley Architecture & Modern Visual Analytics
 */

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Fetch aggregate platform metrics
try {
    $totalGrossRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status NOT IN ('cancelled', 'declined')")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalFarmers = (int)$pdo->query("SELECT COUNT(*) FROM farmer_profiles")->fetchColumn();
    $pendingApprovals = (int)$pdo->query("SELECT COUNT(*) FROM farmer_profiles WHERE approval_status = 'pending'")->fetchColumn();
    $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $totalMarkets = (int)$pdo->query("SELECT COUNT(*) FROM markets WHERE status = 'active'")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Order status breakdown for doughnut chart
    $statusStmt = $pdo->query("SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status");
    $statusData = [];
    while ($row = $statusStmt->fetch()) {
        $statusData[$row['order_status']] = (int)$row['cnt'];
    }

    // Pending Farmers awaiting verification
    $pendingFarmers = $pdo->query("SELECT fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, fp.business_email, fp.address, fp.created_at, u.username, u.email
                                   FROM farmer_profiles fp
                                   JOIN users u ON fp.farmer_id = u.user_id
                                   WHERE fp.approval_status = 'pending'
                                   ORDER BY fp.created_at DESC LIMIT 5")->fetchAll();

    // Recent 7 Orders across platform
    $recentOrders = $pdo->query("SELECT o.order_id, o.order_number, o.total_amount, o.order_status, o.pickup_date, o.created_at,
                                        u.username as customer_name,
                                        fp.stall_name as farmer_stall,
                                        m.market_name
                                 FROM orders o
                                 JOIN users u ON o.customer_id = u.user_id
                                 JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                                 JOIN markets m ON o.market_id = m.market_id
                                 ORDER BY o.created_at DESC LIMIT 7")->fetchAll();

    // Recent Users
    $recentUsers = $pdo->query("SELECT user_id, username, email, role, status, created_at FROM users ORDER BY user_id DESC LIMIT 6")->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard query error: " . $e->getMessage());
}
?>

<!-- Executive Header -->
<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="shield-check" style="color:var(--admin-accent);"></i>
      Platform Command Center
    </h1>
    <p>Real-time ecosystem metrics, seller verification queue, and logistics oversight.</p>
  </div>

  <div class="admin-header-actions">
    <a href="<?= BASE_URL ?>/admin/reports.php" class="admin-btn admin-btn-secondary">
      <i data-lucide="file-text"></i>
      <span>Generate Reports</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/announcements.php" class="admin-btn admin-btn-primary">
      <i data-lucide="megaphone"></i>
      <span>Broadcast Alert</span>
    </a>
  </div>
</div>

<?php if ($pendingApprovals > 0): ?>
  <!-- Urgent Action Banner -->
  <div class="admin-alert-banner urgent">
    <div style="display:flex; align-items:center; gap:0.85rem;">
      <i data-lucide="alert-triangle" style="width:24px; height:24px; color:#f59e0b;"></i>
      <div>
        <strong style="display:block; font-size:0.95rem; font-family:var(--font-heading);">
          Action Required: <?= $pendingApprovals ?> Farmer <?= $pendingApprovals === 1 ? 'Stall is' : 'Stalls are' ?> awaiting registration approval!
        </strong>
        <span style="font-size:0.8125rem; opacity:0.9;">Unapproved farmers cannot publish inventories or accept pickup orders.</span>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-btn admin-btn-sm" style="background:#f59e0b; color:#0f172a; font-weight:700;">
      Review Queue &rarr;
    </a>
  </div>
<?php endif; ?>

<?php if (!empty($unreadInquiriesCount) && $unreadInquiriesCount > 0): ?>
  <!-- Unread Contact Inquiries Banner -->
  <div class="admin-alert-banner" style="background:rgba(56, 189, 248, 0.12); border:1.5px solid #38bdf8; color:#7dd3fc; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; padding:1rem 1.4rem; border-radius:14px;">
    <div style="display:flex; align-items:center; gap:0.85rem;">
      <i data-lucide="mail-warning" style="width:24px; height:24px; color:#38bdf8;"></i>
      <div>
        <strong style="display:block; font-size:0.95rem; font-family:var(--font-heading); color:#f0f9ff;">
          Customer Support: <?= $unreadInquiriesCount ?> New Contact <?= $unreadInquiriesCount === 1 ? 'Inquiry' : 'Inquiries' ?> Awaiting Response!
        </strong>
        <span style="font-size:0.8125rem; opacity:0.9; color:#bae6fd;">Visitors have submitted questions or feedback via the website contact form.</span>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/admin/inquiries.php?status=unread" class="admin-btn admin-btn-sm" style="background:#38bdf8; color:#081b14; font-weight:700; border-radius:8px;">
      View &amp; Reply &rarr;
    </a>
  </div>
<?php endif; ?>

<!-- Top Metrics Grid -->
<div class="admin-stats-grid">
  <!-- Gross Pre-Orders Revenue -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Platform Gross Volume</span>
      <div class="admin-stat-icon-wrapper icon-emerald">
        <i data-lucide="dollar-sign"></i>
      </div>
    </div>
    <div class="admin-stat-value">$<?= number_format($totalGrossRevenue, 2) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend positive"><i data-lucide="trending-up"></i> Live</span>
      <span>Total pre-order reservations</span>
    </div>
  </div>

  <!-- Total Pre-Orders -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Total Pre-Orders</span>
      <div class="admin-stat-icon-wrapper icon-purple">
        <i data-lucide="shopping-bag"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalOrders) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend neutral"><?= $activeOrdersCount ?> Active</span>
      <span>placed / ready for pickup</span>
    </div>
  </div>

  <!-- Registered Farmers -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Producer Stalls</span>
      <div class="admin-stat-icon-wrapper icon-amber">
        <i data-lucide="tractor"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalFarmers) ?></div>
    <div class="admin-stat-footer">
      <?php if ($pendingApprovals > 0): ?>
        <span class="stat-trend negative" style="color:var(--admin-amber);"><i data-lucide="clock"></i> <?= $pendingApprovals ?> Pending</span>
      <?php else: ?>
        <span class="stat-trend positive">100% Verified</span>
      <?php endif; ?>
      <span>Registered farms</span>
    </div>
  </div>

  <!-- Customers & Markets -->
  <div class="admin-stat-card">
    <div class="admin-stat-header">
      <span class="admin-stat-label">Active Markets / Hubs</span>
      <div class="admin-stat-icon-wrapper icon-cyan">
        <i data-lucide="map-pin"></i>
      </div>
    </div>
    <div class="admin-stat-value"><?= number_format($totalMarkets) ?></div>
    <div class="admin-stat-footer">
      <span class="stat-trend positive"><?= number_format($totalCustomers) ?> Customers</span>
      <span>Community pickup hubs</span>
    </div>
  </div>
</div>

<!-- Charts & Visual Analytics Section -->
<div class="admin-dashboard-split" style="margin-bottom:2rem;">
  <!-- Order Status Breakdown Chart -->
  <div class="admin-card" style="margin-bottom:0;">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="pie-chart" style="color:var(--admin-accent);"></i>
          Pre-Order Fulfillment Status Distribution
        </h3>
        <p class="admin-card-subtitle">Real-time breakdown across order lifecycle stages</p>
      </div>
    </div>
    <div class="admin-card-body" style="height: 290px; display:flex; align-items:center; justify-content:center;">
      <canvas id="orderStatusChart" style="max-height: 260px;"></canvas>
    </div>
  </div>

  <!-- Fast Actions & Platform Status -->
  <div class="admin-card" style="margin-bottom:0;">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="zap" style="color:var(--admin-amber);"></i>
          Quick Admin Actions
        </h3>
        <p class="admin-card-subtitle">Common executive shortcuts</p>
      </div>
    </div>
    <div class="admin-card-body" style="display:flex; flex-direction:column; gap:0.85rem;">
      <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-btn admin-btn-secondary" style="justify-content:flex-start;">
        <i data-lucide="user-check" style="color:var(--admin-amber);"></i>
        <span>Review Farmer Verification (<?= $pendingApprovals ?>)</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/markets.php" class="admin-btn admin-btn-secondary" style="justify-content:flex-start;">
        <i data-lucide="plus-circle" style="color:var(--admin-emerald);"></i>
        <span>Establish New Farmers Market</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/categories.php" class="admin-btn admin-btn-secondary" style="justify-content:flex-start;">
        <i data-lucide="tag" style="color:var(--admin-cyan);"></i>
        <span>Manage Product Categories</span>
      </a>
      <a href="<?= BASE_URL ?>/admin/reviews.php" class="admin-btn admin-btn-secondary" style="justify-content:flex-start;">
        <i data-lucide="shield-alert" style="color:var(--admin-rose);"></i>
        <span>Moderate Customer Reviews (<?= $pendingReviewsCount ?>)</span>
      </a>
    </div>
  </div>
</div>

<?php if (!empty($pendingFarmers)): ?>
  <!-- Pending Farmer Verification Queue Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="clock" style="color:var(--admin-amber);"></i>
          Pending Farmer Approvals (<?= count($pendingFarmers) ?>)
        </h3>
        <p class="admin-card-subtitle">Producer registration requests awaiting administrative approval</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/farmers.php?status=pending" class="admin-btn admin-btn-secondary admin-btn-sm">
        View All Pending
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Stall / Farm Name</th>
            <th>Contact Person</th>
            <th>Email & Phone</th>
            <th>Location Address</th>
            <th>Requested Date</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingFarmers as $f): ?>
            <tr id="farmer-row-<?= $f['farmer_id'] ?>">
              <td>
                <div style="font-weight:700; color:var(--admin-text-main);">
                  <?= htmlspecialchars($f['stall_name']) ?>
                </div>
                <span class="status-pill pending">Pending Review</span>
              </td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($f['contact_person']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">@<?= htmlspecialchars($f['username']) ?></div>
              </td>
              <td>
                <div style="font-size:0.8125rem;"><?= htmlspecialchars($f['business_email'] ?: $f['email']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($f['business_phone']) ?></div>
              </td>
              <td style="max-width:240px; font-size:0.8125rem; color:var(--admin-text-muted);">
                <?= htmlspecialchars($f['address']) ?>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle);">
                <?= date('M d, Y', strtotime($f['created_at'])) ?>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem;">
                  <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" onclick="handleFarmerAction(<?= $f['farmer_id'] ?>, 'approve')">
                    <i data-lucide="check"></i> Approve
                  </button>
                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="handleFarmerAction(<?= $f['farmer_id'] ?>, 'reject')">
                    <i data-lucide="x"></i> Reject
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- Recent Platform Pre-Orders & System Registrations -->
<div class="admin-dashboard-split">
  <!-- Recent Pre-Orders Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="package-check" style="color:var(--admin-emerald);"></i>
          Recent Platform Pre-Orders
        </h3>
        <p class="admin-card-subtitle">Live transactions across all pickup stalls</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/orders.php" class="admin-btn admin-btn-secondary admin-btn-sm">
        View All Orders
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Producer Stall</th>
            <th>Total</th>
            <th>Pickup Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">
                No customer orders placed yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td>
                  <strong style="color:var(--admin-accent); font-family:monospace;"><?= htmlspecialchars($o['order_number']) ?></strong>
                </td>
                <td style="font-weight:600;"><?= htmlspecialchars($o['customer_name']) ?></td>
                <td>
                  <div style="font-size:0.85rem;"><?= htmlspecialchars($o['farmer_stall']) ?></div>
                  <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($o['market_name']) ?></div>
                </td>
                <td style="font-weight:700; color:var(--admin-emerald);">$<?= number_format($o['total_amount'], 2) ?></td>
                <td style="font-size:0.8125rem; color:var(--admin-text-subtle);"><?= date('M d, Y', strtotime($o['pickup_date'])) ?></td>
                <td>
                  <span class="status-pill <?= htmlspecialchars($o['order_status']) ?>">
                    <?= ucwords(str_replace('_', ' ', $o['order_status'])) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent User Registrations -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h3 class="admin-card-title">
          <i data-lucide="user-plus" style="color:var(--admin-cyan);"></i>
          New Users
        </h3>
        <p class="admin-card-subtitle">Latest accounts created</p>
      </div>
      <a href="<?= BASE_URL ?>/admin/users.php" class="admin-btn admin-btn-secondary admin-btn-sm">
        All Users
      </a>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($u['username']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($u['email']) ?></div>
              </td>
              <td>
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:<?= $u['role'] === 'farmer' ? '#34d399' : ($u['role'] === 'admin' ? '#818cf8' : '#38bdf8') ?>;">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($u['status']) ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Chart & AJAX Interaction Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Chart.js Doughnut Status Chart
  const ctx = document.getElementById('orderStatusChart');
  if (ctx) {
    const rawData = <?= json_encode($statusData) ?>;
    const labels = Object.keys(rawData).map(s => s.replace('_', ' ').toUpperCase());
    const dataVals = Object.values(rawData);

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels.length ? labels : ['NO ORDERS'],
        datasets: [{
          data: dataVals.length ? dataVals : [1],
          backgroundColor: [
            'rgba(245, 158, 11, 0.85)', // placed
            'rgba(6, 182, 212, 0.85)',  // accepted
            'rgba(16, 185, 129, 0.85)', // ready_for_pickup
            'rgba(99, 102, 241, 0.85)', // completed
            'rgba(239, 68, 68, 0.85)',  // cancelled
            'rgba(148, 163, 184, 0.5)'  // declined
          ],
          borderWidth: 2,
          borderColor: 'transparent'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: '#94a3b8',
              font: { family: 'Inter', size: 11, weight: '600' },
              padding: 12
            }
          }
        },
        cutout: '70%'
      }
    });
  }
});

// Farmer Action Handler (Approve / Reject)
async function handleFarmerAction(farmerId, action) {
  const confirmMsg = action === 'approve' 
    ? 'Are you sure you want to approve this farmer stall? They will be able to sell products immediately.'
    : 'Are you sure you want to reject this farmer registration?';

  if (!confirm(confirmMsg)) return;

  try {
    const formData = new FormData();
    formData.append('farmer_id', farmerId);
    formData.append('action', action);

    const res = await fetch('<?= BASE_URL ?>/admin/api/farmer_action.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      const row = document.getElementById(`farmer-row-${farmerId}`);
      if (row) {
        row.style.opacity = '0';
        row.style.transform = 'scale(0.95)';
        row.style.transition = 'all 0.3s ease';
        setTimeout(() => row.remove(), 300);
      }
    } else {
      showAdminToast(data.message || 'Operation failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network error while processing request', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
