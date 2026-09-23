<?php
/**
 * MarketLink - Customer Dashboard Portal
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

requireRole(['customer', 'admin']);

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch customer profile
$stmt = $pdo->prepare("SELECT u.username, u.email, u.phone_number, cp.full_name, cp.default_address 
                       FROM users u 
                       LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id 
                       WHERE u.user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $userId]);
$user = $stmt->fetch();

$displayName = $user['full_name'] ?: $user['username'];

$pageTitle = 'Customer Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="min-height: 100vh; background: #f8fafc;">
  <!-- Navigation Header -->
  <header style="background: #ffffff; border-bottom: 1px solid var(--slate-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" style="height: 38px;">
      <span style="background: var(--primary-100); color: var(--primary-800); font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: var(--radius-full); text-transform: uppercase;">Customer Portal</span>
    </div>
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <div style="text-align: right;">
        <div style="font-weight: 700; font-size: 0.9375rem; color: var(--slate-900);"><?= htmlspecialchars($displayName) ?></div>
        <div style="font-size: 0.75rem; color: var(--slate-500);"><?= htmlspecialchars($user['email']) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.5rem 1rem; height: 38px;">
        <span>🚪</span> Sign Out
      </a>
    </div>
  </header>

  <!-- Main Body Content -->
  <main style="max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem;">
    <!-- Welcome Banner -->
    <div style="background: linear-gradient(135deg, #0a4023 0%, #15803d 100%); color: #ffffff; padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); position: relative; overflow: hidden; margin-bottom: 2rem;">
      <div style="position: relative; z-index: 2; max-width: 650px;">
        <span style="background: rgba(255,255,255,0.15); padding: 0.35rem 0.8rem; border-radius: var(--radius-full); font-size: 0.8125rem; font-weight: 600; display: inline-block; margin-bottom: 1rem;">🌿 Account Ready & Verified</span>
        <h1 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">Welcome to MarketLink, <?= htmlspecialchars($displayName) ?>!</h1>
        <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6;">
          Your registration with live database validation is complete. You can now explore fresh market stalls, pre-order from local farmers, and reserve pickup slots.
        </p>
      </div>
      <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.15; pointer-events: none;">🛒</div>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Active Orders</div>
        <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--slate-900); margin: 0.35rem 0;">0</div>
        <div style="font-size: 0.75rem; color: var(--primary-600);">Ready for your first fresh harvest order</div>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Favorite Farmers</div>
        <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--slate-900); margin: 0.35rem 0;">0</div>
        <div style="font-size: 0.75rem; color: var(--slate-500);">Bookmark local stalls to get restock alerts</div>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
        <div style="color: var(--slate-500); font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">Pickup Slot Status</div>
        <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--success); margin: 0.35rem 0;">Available</div>
        <div style="font-size: 0.75rem; color: var(--slate-500);">Slots open across local weekend markets</div>
      </div>
    </div>

    <!-- Account Details Card -->
    <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
      <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin-bottom: 1.25rem;">Account Profile Details</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; font-size: 0.875rem;">
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Username</span>
          <strong style="color: var(--slate-800); font-size: 1rem;">@<?= htmlspecialchars($user['username']) ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Email Address</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($user['email']) ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Phone Number</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($user['phone_number'] ?: 'Not provided') ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Default Address</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($user['default_address'] ?: 'Not set yet') ?></strong>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
