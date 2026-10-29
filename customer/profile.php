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
            $successMsg = "Your profile and pickup details have been updated successfully.";

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
      <span><i class="fa-solid fa-user" aria-hidden="true"></i></span> Profile & Pickup Settings
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
        <span><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></span> Personal & Contact Details
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
        <span><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></span> Save Profile Changes
      </button>
    </form>
  </div>

  <!-- Right: Account Overview & Security -->
  <div style="display:flex; flex-direction:column; gap:1.5rem;">

    <!-- Account Summary Card -->
    <div class="portal-card">
      <div class="portal-card-header">
        <h3 class="portal-card-title">
          <span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span> Account Status
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

    <!-- Security Info Card -->
    <div class="portal-card">
      <div class="portal-card-header">
        <h3 class="portal-card-title">
          <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span> Account Security
        </h3>
      </div>
      <div style="font-size:0.875rem; color:var(--text-secondary); line-height:1.6;">
        <p style="margin:0 0 0.85rem;">
          Your MarketLink account credentials and customer communications are safely encrypted.
        </p>
        <div style="background:rgba(34,197,94,0.08); border:1px solid rgba(34,197,94,0.25); border-radius:var(--radius-md); padding:0.85rem 1rem; color:var(--text-primary); font-size:0.8125rem;">
          <strong style="color:var(--emerald-400); display:block; margin-bottom:0.25rem;">
            <i class="fa-solid fa-key" aria-hidden="true"></i> Password Recovery
          </strong>
          To update or recover your password, you can use the secure 
          <a href="<?= BASE_URL ?>/forgot_password.php" style="color:var(--primary-400); font-weight:700; text-decoration:underline;">Forgot Password</a> 
          verification link on the login page.
        </div>
      </div>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
