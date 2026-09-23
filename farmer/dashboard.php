<?php
/**
 * MarketLink - Farmer Stall Dashboard Portal
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

requireRole(['farmer', 'admin']);

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch farmer profile
$stmt = $pdo->prepare("SELECT u.username, u.email, u.phone_number, fp.stall_name, fp.contact_person, 
                              fp.business_phone, fp.business_email, fp.address, fp.approval_status 
                       FROM users u 
                       LEFT JOIN farmer_profiles fp ON u.user_id = fp.farmer_id 
                       WHERE u.user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $userId]);
$farmer = $stmt->fetch();

$displayName = $farmer['contact_person'] ?: $farmer['stall_name'] ?: $farmer['username'];
$approvalStatus = $farmer['approval_status'] ?? 'pending';

$pageTitle = 'Farmer Stall Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="min-height: 100vh; background: #f8fafc;">
  <!-- Navigation Header -->
  <header style="background: #ffffff; border-bottom: 1px solid var(--slate-200); padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 1rem;">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="MarketLink" style="height: 38px;">
      <span style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: var(--radius-full); text-transform: uppercase;">Farmer Producer Portal</span>
    </div>
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <div style="text-align: right;">
        <div style="font-weight: 700; font-size: 0.9375rem; color: var(--slate-900);"><?= htmlspecialchars($displayName) ?></div>
        <div style="font-size: 0.75rem; color: var(--slate-500);"><?= htmlspecialchars($farmer['stall_name']) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.5rem 1rem; height: 38px;">
        <span>🚪</span> Sign Out
      </a>
    </div>
  </header>

  <!-- Main Body Content -->
  <main style="max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem;">

    <!-- Approval Status Alert Banner -->
    <?php if ($approvalStatus === 'pending'): ?>
      <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
        <div style="font-size: 1.75rem;">⏳</div>
        <div>
          <h4 style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: #92400e; margin-bottom: 0.25rem;">
            Stall Verification In Progress (Status: Pending)
          </h4>
          <p style="color: #b45309; font-size: 0.875rem; line-height: 1.5;">
            Your farmer profile for <strong><?= htmlspecialchars($farmer['stall_name']) ?></strong> has been safely created. MarketLink administrators are reviewing your stall details. Once verified, your produce templates and pickup slots will go live on the public directory.
          </p>
        </div>
      </div>
    <?php else: ?>
      <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
        <div style="font-size: 1.75rem;">✅</div>
        <div>
          <h4 style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: #065f46; margin-bottom: 0.25rem;">
            Stall Status: Verified &amp; Active
          </h4>
          <p style="color: #047857; font-size: 0.875rem;">
            Your producer account is active. You can manage products, weekly inventory, and stall pickup slots.
          </p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Welcome Banner -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); position: relative; overflow: hidden; margin-bottom: 2rem;">
      <div style="position: relative; z-index: 2; max-width: 650px;">
        <span style="background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(74, 222, 128, 0.3); padding: 0.35rem 0.8rem; border-radius: var(--radius-full); font-size: 0.8125rem; font-weight: 600; display: inline-block; margin-bottom: 1rem;">🚜 Producer Management Console</span>
        <h1 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;"><?= htmlspecialchars($farmer['stall_name']) ?></h1>
        <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6;">
          Managed by <strong><?= htmlspecialchars($farmer['contact_person']) ?></strong>. Connect directly with thousands of local households waiting for fresh farm produce.
        </p>
      </div>
      <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.15; pointer-events: none;">🌾</div>
    </div>

    <!-- Stall Overview Details -->
    <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
      <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin-bottom: 1.25rem;">Stall Registration Profile</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; font-size: 0.875rem;">
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Farm / Stall Name</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($farmer['stall_name']) ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Contact Person</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($farmer['contact_person']) ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Business Phone</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($farmer['business_phone'] ?: $farmer['phone_number']) ?></strong>
        </div>
        <div>
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Business Email</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($farmer['business_email'] ?: $farmer['email']) ?></strong>
        </div>
        <div style="grid-column: 1 / -1;">
          <span style="color: var(--slate-500); display: block; font-size: 0.75rem; text-transform: uppercase;">Farm / Stall Address</span>
          <strong style="color: var(--slate-800); font-size: 1rem;"><?= htmlspecialchars($farmer['address'] ?: 'Not provided') ?></strong>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
