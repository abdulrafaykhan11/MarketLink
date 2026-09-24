<?php
/**
 * MarketLink - Admin Platform Users & Customer Management
 * SRS Section 1.6: Admin can view, activate, or deactivate customer accounts in case of policy violations.
 */

$pageTitle = 'Users & Customers';
$activeNav = 'users';
require_once __DIR__ . '/includes/admin_header.php';

$filterRole   = trim($_GET['role'] ?? 'all');
$filterStatus = trim($_GET['status'] ?? 'all');
$searchQuery  = trim($_GET['search'] ?? '');

$whereClauses = [];
$params = [];

if (!empty($filterRole) && $filterRole !== 'all') {
    $whereClauses[] = "u.role = :role";
    $params[':role'] = $filterRole;
}

if (!empty($filterStatus) && $filterStatus !== 'all') {
    $whereClauses[] = "u.status = :status";
    $params[':status'] = $filterStatus;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(u.username LIKE :q OR u.email LIKE :q OR u.phone_number LIKE :q OR cp.full_name LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT u.user_id, u.username, u.email, u.role, u.status, u.phone_number, u.created_at,
               cp.full_name as customer_name,
               fp.stall_name as farmer_stall
        FROM users u
        LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id
        LEFT JOIN farmer_profiles fp ON u.user_id = fp.farmer_id
        {$whereSql}
        ORDER BY u.user_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll();

// Role counts
$roleCounts = [];
$rStmt = $pdo->query("SELECT role, COUNT(*) as c FROM users GROUP BY role")->fetchAll();
foreach ($rStmt as $r) {
    $roleCounts[$r['role']] = (int)$r['c'];
}
$totalUsersCount = array_sum($roleCounts);
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="users" style="color:var(--admin-accent);"></i>
      Platform User &amp; Customer Directory
    </h1>
    <p>Manage authenticated user credentials, view shopper profiles, and enforce platform security policies.</p>
  </div>
</div>

<div class="admin-card">
  <!-- Toolbar -->
  <div class="admin-toolbar">
    <div class="admin-filter-tabs">
      <a href="?role=all<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterRole === 'all') ? 'active' : '' ?>">
        All Users (<?= $totalUsersCount ?>)
      </a>
      <a href="?role=customer<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterRole === 'customer') ? 'active' : '' ?>">
        Customers (<?= $roleCounts['customer'] ?? 0 ?>)
      </a>
      <a href="?role=farmer<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterRole === 'farmer') ? 'active' : '' ?>">
        Farmers (<?= $roleCounts['farmer'] ?? 0 ?>)
      </a>
      <a href="?role=admin<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterRole === 'admin') ? 'active' : '' ?>">
        Admins (<?= $roleCounts['admin'] ?? 0 ?>)
      </a>
    </div>

    <form method="GET" class="admin-search-wrapper">
      <?php if ($filterRole !== 'all'): ?>
        <input type="hidden" name="role" value="<?= htmlspecialchars($filterRole) ?>">
      <?php endif; ?>
      <span class="admin-search-icon"><i data-lucide="search" style="width:16px; height:16px;"></i></span>
      <input type="text" name="search" class="admin-search-input" placeholder="Search by name, username, email..." value="<?= htmlspecialchars($searchQuery) ?>">
    </form>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>User Account</th>
          <th>Full Name / Business</th>
          <th>Role</th>
          <th>Contact Phone</th>
          <th>Status</th>
          <th>Registered</th>
          <th style="text-align:right;">Access Controls</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usersList)): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No user accounts found matching your query.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($usersList as $u): ?>
            <tr id="user-row-<?= $u['user_id'] ?>">
              <td>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                  <div class="admin-avatar" style="width:32px; height:32px; font-size:0.75rem;">
                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:700; color:var(--admin-text-main);">
                      <?= htmlspecialchars($u['username']) ?>
                    </div>
                    <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                      <?= htmlspecialchars($u['email']) ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <?php if ($u['role'] === 'customer'): ?>
                  <span style="font-weight:600;"><?= htmlspecialchars($u['customer_name'] ?: 'Retail Customer') ?></span>
                <?php elseif ($u['role'] === 'farmer'): ?>
                  <span style="font-weight:600; color:var(--admin-emerald);"><?= htmlspecialchars($u['farmer_stall'] ?: 'Stall Profile') ?></span>
                <?php else: ?>
                  <span style="font-weight:700; color:var(--admin-accent);">System Administrator</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size:0.725rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;
                             padding:0.2rem 0.55rem; border-radius:var(--radius-full);
                             background:<?= $u['role'] === 'farmer' ? 'rgba(16,185,129,0.15)' : ($u['role'] === 'admin' ? 'rgba(99,102,241,0.15)' : 'rgba(6,182,212,0.15)') ?>;
                             color:<?= $u['role'] === 'farmer' ? '#34d399' : ($u['role'] === 'admin' ? '#818cf8' : '#22d3ee') ?>;">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-muted);">
                <?= htmlspecialchars($u['phone_number'] ?: '—') ?>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($u['status']) ?>" id="user-status-pill-<?= $u['user_id'] ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle);">
                <?= date('M d, Y', strtotime($u['created_at'])) ?>
              </td>
              <td style="text-align:right;">
                <?php if ($u['user_id'] !== (int)$_SESSION['user_id']): ?>
                  <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                    <?php if ($u['status'] === 'active'): ?>
                      <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                              title="Suspend user account"
                              onclick="toggleUserStatus(<?= $u['user_id'] ?>, 'suspend')">
                        <i data-lucide="slash"></i> Suspend
                      </button>
                    <?php else: ?>
                      <button type="button" class="admin-btn admin-btn-emerald admin-btn-sm" 
                              title="Reactivate user account"
                              onclick="toggleUserStatus(<?= $u['user_id'] ?>, 'activate')">
                        <i data-lucide="check-circle"></i> Activate
                      </button>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <span style="font-size:0.75rem; color:var(--admin-text-subtle); font-style:italic;">You</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function toggleUserStatus(userId, action) {
  if (!confirm(`Are you sure you want to ${action} this user account?`)) return;

  try {
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('action', action);

    const res = await fetch('<?= BASE_URL ?>/admin/api/user_status.php', {
      method: 'POST',
      body: fd
    });

    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showAdminToast(data.message || 'Error occurred', 'error');
    }
  } catch (e) {
    showAdminToast('Network communication error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
