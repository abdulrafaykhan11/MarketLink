<?php
/**
 * MarketLink - Customer Notifications & In-App Alerts
 * Fully implements SRS Section 1.7: Order confirmations, harvest readiness, and restock alerts.
 */

$pageTitle = 'Notifications & Alerts';
$activePage = 'notifications';
require_once __DIR__ . '/includes/customer_header.php';

// Handle "Mark All Read" action
if (isset($_POST['mark_all_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid")->execute([':uid' => $currentUserId]);
    $_SESSION['flash_success'] = "All notifications marked as read.";
    header("Location: " . BASE_URL . "/customer/notifications.php");
    exit;
}

// Fetch all notifications for this customer
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC");
$notifStmt->execute([':uid' => $currentUserId]);
$notifications = $notifStmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>🔔</span> Notifications & In-App Alerts
    </h1>
    <p class="page-subheading">
      Live updates on pre-orders, pickup slot readiness, and farmer announcements
    </p>
  </div>

  <?php if ($unreadNotifsCount > 0): ?>
    <form method="POST">
      <button type="submit" name="mark_all_read" value="1" class="btn-secondary" style="padding:0.5rem 1.15rem; font-size:0.85rem; cursor:pointer;">
        <span>✔</span> Mark All as Read
      </button>
    </form>
  <?php endif; ?>
</div>

<div class="portal-card">
  <?php if (!empty($notifications)): ?>
    <div style="display:flex; flex-direction:column; gap:1rem;">
      <?php foreach ($notifications as $n): ?>
        <?php
          $type = $n['notification_type'];
          $icon = match($type) {
            'order_status' => '📦',
            'restock_alert' => '🥬',
            'announcement' => '📢',
            default => '🌱'
          };
          $isUnread = !$n['is_read'];
        ?>
        <div style="background:<?= $isUnread ? 'var(--bg-surface-elevated)' : 'var(--bg-secondary)' ?>; 
                    border:1px solid <?= $isUnread ? 'var(--emerald-500)' : 'var(--border-subtle)' ?>; 
                    border-radius:var(--radius-lg); padding:1.25rem; display:flex; gap:1.25rem; align-items:start; transition:all var(--transition-fast);">
          
          <div style="width:44px; height:44px; border-radius:var(--radius-md); background:<?= $isUnread ? 'rgba(16,185,129,0.2)' : 'var(--bg-surface)' ?>; display:flex; align-items:center; justify-content:center; font-size:1.35rem; flex-shrink:0;">
            <?= $icon ?>
          </div>

          <div style="flex:1;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem; flex-wrap:wrap; gap:0.5rem;">
              <div style="display:flex; align-items:center; gap:0.6rem;">
                <h4 style="font-family:var(--font-heading); font-size:1rem; font-weight:700; color:var(--text-primary); margin:0;">
                  <?= htmlspecialchars($n['title']) ?>
                </h4>
                <?php if ($isUnread): ?>
                  <span style="background:var(--emerald-500); color:#fff; font-size:0.65rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:var(--radius-full); text-transform:uppercase;">
                    New
                  </span>
                <?php endif; ?>
              </div>

              <span style="font-size:0.75rem; color:var(--text-muted);">
                <?= date('M d, Y • h:i A', strtotime($n['created_at'])) ?>
              </span>
            </div>

            <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.5; margin:0;">
              <?= htmlspecialchars($n['message']) ?>
            </p>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="text-align:center; padding:4rem 1.5rem;">
      <div style="font-size:3.5rem; margin-bottom:1rem;">🔕</div>
      <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">No Notifications Yet</h3>
      <p style="color:var(--text-muted); font-size:0.95rem; max-width:440px; margin:0 auto;">
        You are all caught up! Order status updates, ready-for-pickup alerts, and restock notices will appear here.
      </p>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
