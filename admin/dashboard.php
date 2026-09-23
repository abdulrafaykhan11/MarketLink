<?php
/**
 * MarketLink - Platform Admin Portal
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

requireRole(['admin']);

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get database metrics
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFarmers = (int) $pdo->query("SELECT COUNT(*) FROM farmer_profiles")->fetchColumn();
$totalCustomers = (int) $pdo->query("SELECT COUNT(*) FROM customer_profiles")->fetchColumn();
$totalMarkets = (int) $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();

// Get recent users
$recentUsers = $pdo->query("SELECT user_id, username, email, role, status, created_at FROM users ORDER BY user_id DESC LIMIT 10")->fetchAll();

$pageTitle = 'Admin Administration';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="min-height: 100vh; background: #f8fafc;">
  <!-- Navigation Header -->
  <header style="background: #ffffff; border-bottom: 1px solid var(--slate-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" style="height: 38px;">
      <span style="background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: var(--radius-full); text-transform: uppercase;">Super Admin Console</span>
    </div>
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <div style="text-align: right;">
        <div style="font-weight: 700; font-size: 0.9375rem; color: var(--slate-900);">Administrator</div>
        <div style="font-size: 0.75rem; color: var(--slate-500);"><?= htmlspecialchars($_SESSION['username']) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.5rem 1rem; height: 38px;">
        <span>🚪</span> Sign Out
      </a>
    </div>
  </header>

  <!-- Main Body Content -->
  <main style="max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem;">
    <!-- Welcome Header -->
    <div style="margin-bottom: 2rem;">
      <h1 style="font-family: var(--font-heading); font-size: 1.875rem; font-weight: 800; color: var(--slate-900);">MarketLink System Overview</h1>
      <p style="color: var(--slate-500); font-size: 0.9375rem;">Real-time database statistics and platform management.</p>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Total Users</div>
        <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--slate-900); margin: 0.35rem 0;"><?= $totalUsers ?></div>
        <div style="font-size: 0.75rem; color: var(--primary-600);">Registered in system</div>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Farmers Registered</div>
        <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #d97706; margin: 0.35rem 0;"><?= $totalFarmers ?></div>
        <div style="font-size: 0.75rem; color: var(--slate-500);">Producer stall profiles</div>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Customer Profiles</div>
        <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #2563eb; margin: 0.35rem 0;"><?= $totalCustomers ?></div>
        <div style="font-size: 0.75rem; color: var(--slate-500);">Active retail shoppers</div>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Markets Established</div>
        <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--primary-600); margin: 0.35rem 0;"><?= $totalMarkets ?></div>
        <div style="font-size: 0.75rem; color: var(--slate-500);">Physical pickup hubs</div>
      </div>
    </div>

    <!-- Recent Users Table -->
    <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;">
      <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--slate-200);">
        <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--slate-900);">Recent System Registrations</h3>
      </div>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
          <thead>
            <tr style="background: var(--slate-50); color: var(--slate-600); border-bottom: 1px solid var(--slate-200);">
              <th style="padding: 0.875rem 1.25rem;">ID</th>
              <th style="padding: 0.875rem 1.25rem;">Username</th>
              <th style="padding: 0.875rem 1.25rem;">Email</th>
              <th style="padding: 0.875rem 1.25rem;">Role</th>
              <th style="padding: 0.875rem 1.25rem;">Status</th>
              <th style="padding: 0.875rem 1.25rem;">Registered At</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentUsers)): ?>
              <tr>
                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--slate-500);">No users registered yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentUsers as $u): ?>
                <tr style="border-bottom: 1px solid var(--slate-100);">
                  <td style="padding: 0.875rem 1.25rem; font-weight: 700;">#<?= $u['user_id'] ?></td>
                  <td style="padding: 0.875rem 1.25rem; font-weight: 600; color: var(--slate-800);"><?= htmlspecialchars($u['username']) ?></td>
                  <td style="padding: 0.875rem 1.25rem; color: var(--slate-600);"><?= htmlspecialchars($u['email']) ?></td>
                  <td style="padding: 0.875rem 1.25rem;">
                    <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: <?= $u['role'] === 'farmer' ? '#fef3c7; color: #92400e;' : ($u['role'] === 'admin' ? '#e0e7ff; color: #3730a3;' : '#dcfce7; color: #166534;') ?>">
                      <?= htmlspecialchars($u['role']) ?>
                    </span>
                  </td>
                  <td style="padding: 0.875rem 1.25rem;">
                    <span style="color: var(--success); font-weight: 600; font-size: 0.8125rem;">● <?= htmlspecialchars($u['status']) ?></span>
                  </td>
                  <td style="padding: 0.875rem 1.25rem; color: var(--slate-400); font-size: 0.8125rem;"><?= htmlspecialchars($u['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
