<?php
/**
 * MarketLink - Customer Profile & Pickup Preferences
 * Fully implements SRS Section 1.6: Name, Contact number, Email, Address, and account security.
 */

$pageTitle = 'Profile & Settings';
$activePage = 'profile';
require_once __DIR__ . '/includes/customer_header.php';

$successMsg = '';
$errorMsg = '';

// Handle Profile Update Form
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['default_address'] ?? '');

    if (empty($fullName)) {
        $errorMsg = "Full Name cannot be empty.";
    } else {
        try {
            // Update users table phone
            $uStmt = $pdo->prepare("UPDATE users SET phone_number = :phone WHERE user_id = :uid");
            $uStmt->execute([':phone' => $phone, ':uid' => $currentUserId]);

            // Update or insert customer_profiles
            $cpStmt = $pdo->prepare("INSERT INTO customer_profiles (customer_id, full_name, default_address, created_at)
                                     VALUES (:uid, :name, :addr, NOW())
                                     ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), default_address = VALUES(default_address)");
            $cpStmt->execute([':uid' => $currentUserId, ':name' => $fullName, ':addr' => $address]);

            $_SESSION['full_name'] = $fullName;
            $successMsg = "Your profile and pickup details have been updated successfully! ✔";

            // Refresh user variable
            $stmt = $pdo->prepare("SELECT u.username, u.email, u.phone_number, cp.full_name, cp.default_address, u.created_at as member_since
                                   FROM users u 
                                   LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id 
                                   WHERE u.user_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $currentUserId]);
            $customerUser = $stmt->fetch();
            $displayName = $fullName;
        } catch (Exception $e) {
            $errorMsg = "Database error: " . $e->getMessage();
        }
    }
}

// Handle Password Change Form
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['change_password'])) {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $errorMsg = "Please fill in all password fields.";
    } elseif ($newPass !== $confirmPass) {
        $errorMsg = "New passwords do not match.";
    } elseif (strlen($newPass) < 6) {
        $errorMsg = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        $pCheck = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :uid");
        $pCheck->execute([':uid' => $currentUserId]);
        $hash = $pCheck->fetchColumn();

        if (password_verify($currentPass, $hash)) {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $pUpd = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid");
            $pUpd->execute([':hash' => $newHash, ':uid' => $currentUserId]);
            $successMsg = "Your password has been changed securely. ✔";
        } else {
            $errorMsg = "Current password is incorrect.";
        }
    }
}

// Fetch member details
$stmt = $pdo->prepare("SELECT u.username, u.email, u.phone_number, cp.full_name, cp.default_address, u.created_at as member_since
                       FROM users u 
                       LEFT JOIN customer_profiles cp ON u.user_id = cp.customer_id 
                       WHERE u.user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $currentUserId]);
$profileData = $stmt->fetch();

// Fetch order counts for account summary card
$tcStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :uid");
$tcStmt->execute([':uid' => $currentUserId]);
$totalOrdersCount = (int)$tcStmt->fetchColumn();

$acStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :uid AND order_status IN ('placed','accepted','ready_for_pickup')");
$acStmt->execute([':uid' => $currentUserId]);
$activeOrdersCount = (int)$acStmt->fetchColumn();
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>👤</span> Profile & Pickup Settings
    </h1>
    <p class="page-subheading">
      Manage your customer information, primary market contact, and security preferences
    </p>
  </div>
</div>

<?php if ($successMsg): ?>
  <div style="background:rgba(16,185,129,0.15); border:1px solid var(--emerald-500); color:var(--emerald-400); padding:1rem 1.25rem; border-radius:var(--radius-lg); margin-bottom:1.5rem; font-weight:600;">
    <?= htmlspecialchars($successMsg) ?>
  </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
  <div style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#ef4444; padding:1rem 1.25rem; border-radius:var(--radius-lg); margin-bottom:1.5rem; font-weight:600;">
    <?= htmlspecialchars($errorMsg) ?>
  </div>
<?php endif; ?>

<div class="dashboard-grid-2">

  <!-- Left: Profile Details Edit Form -->
  <div class="portal-card">
    <div class="portal-card-header">
      <h3 class="portal-card-title">
        <span>📝</span> Personal & Contact Details
      </h3>
    </div>

    <form method="POST">
      <input type="hidden" name="update_profile" value="1">

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
          Username (Account Handle)
        </label>
        <input type="text" value="@<?= htmlspecialchars($profileData['username']) ?>" disabled class="topbar-search-input" style="border-radius:var(--radius-md); opacity:0.65; cursor:not-allowed;">
        <span style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem; display:block;">Usernames cannot be changed.</span>
      </div>

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
          Email Address
        </label>
        <input type="email" value="<?= htmlspecialchars($profileData['email']) ?>" disabled class="topbar-search-input" style="border-radius:var(--radius-md); opacity:0.65; cursor:not-allowed;">
        <span style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem; display:block;">Primary email for order status confirmations.</span>
      </div>

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
          Full Name *
        </label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($profileData['full_name'] ?? '') ?>" required class="topbar-search-input" style="border-radius:var(--radius-md);">
      </div>

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
          Contact Phone Number (For Stall Pickup Coordination)
        </label>
        <input type="tel" name="phone_number" value="<?= htmlspecialchars($profileData['phone_number'] ?? '') ?>" placeholder="e.g. 03001234567" class="topbar-search-input" style="border-radius:var(--radius-md);">
      </div>

      <div style="margin-bottom:1.75rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
          Default Address (Area / Colony / Street)
        </label>
        <textarea name="default_address" class="topbar-search-input" style="border-radius:var(--radius-md); height:80px; resize:none;" placeholder="Your residential address for nearby market discovery..."><?= htmlspecialchars($profileData['default_address'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-primary" style="padding:0.7rem 1.75rem; font-size:0.95rem; cursor:pointer;">
        <span>💾</span> Save Profile Changes
      </button>
    </form>
  </div>

  <!-- Right: Account Overview & Security -->
  <div style="display:flex; flex-direction:column; gap:1.5rem;">

    <!-- Account Summary Card -->
    <div class="portal-card">
      <div class="portal-card-header">
        <h3 class="portal-card-title">
          <span>🌿</span> Account Status
        </h3>
      </div>

      <div style="display:flex; flex-direction:column; gap:0.85rem; font-size:0.875rem;">
        <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-subtle); padding-bottom:0.5rem;">
          <span style="color:var(--text-muted);">Account Role:</span>
          <strong style="color:var(--emerald-400); text-transform:uppercase;">Customer</strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-subtle); padding-bottom:0.5rem;">
          <span style="color:var(--text-muted);">Member Since:</span>
          <strong style="color:var(--text-primary);"><?= date('M d, Y', strtotime($profileData['member_since'])) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-subtle); padding-bottom:0.5rem;">
          <span style="color:var(--text-muted);">Total Pre-Orders:</span>
          <strong style="color:var(--text-primary);"><?= $totalOrdersCount ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between;">
          <span style="color:var(--text-muted);">Active Pickups:</span>
          <strong style="color:var(--sky-400);"><?= $activeOrdersCount ?></strong>
        </div>
      </div>
    </div>

    <!-- Password Change Card -->
    <div class="portal-card">
      <div class="portal-card-header">
        <h3 class="portal-card-title">
          <span>🔒</span> Security & Password
        </h3>
      </div>

      <form method="POST">
        <input type="hidden" name="change_password" value="1">

        <div style="margin-bottom:1rem;">
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:0.35rem;">
            Current Password
          </label>
          <input type="password" name="current_password" required class="topbar-search-input" style="border-radius:var(--radius-md);">
        </div>

        <div style="margin-bottom:1rem;">
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:0.35rem;">
            New Password (Min. 6 chars)
          </label>
          <input type="password" name="new_password" required minlength="6" class="topbar-search-input" style="border-radius:var(--radius-md);">
        </div>

        <div style="margin-bottom:1.25rem;">
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:0.35rem;">
            Confirm New Password
          </label>
          <input type="password" name="confirm_password" required minlength="6" class="topbar-search-input" style="border-radius:var(--radius-md);">
        </div>

        <button type="submit" class="btn-secondary" style="width:100%; padding:0.65rem; font-size:0.9rem; cursor:pointer;">
          Update Password
        </button>
      </form>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
