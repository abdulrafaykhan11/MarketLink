<?php
/**
 * MarketLink - Customer Notifications & In-App Alerts
 * Fully implements SRS Section 1.7: Order confirmations, harvest readiness, and restock alerts.
 * - Mark All Read: Fixed with proper POST handling before header output
 * - Clickable notifications: Route to relevant page based on type + order number
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

requireRole(['customer', 'admin']);

$pdo = getDBConnection();
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

// Handle "Mark All Read" action — must happen before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    try {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid")
            ->execute([':uid' => $currentUserId]);
    } catch (Exception $e) {
        // silently fail — just reload
    }
    header("Location: " . BASE_URL . "/customer/notifications.php");
    exit;
}

// Handle single notification mark-as-read + redirect to its target link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['read_and_go'])) {
    $notifId  = (int)($_POST['notif_id'] ?? 0);
    $target   = trim($_POST['target_url'] ?? '');

    if ($notifId > 0) {
        try {
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = :nid AND user_id = :uid")
                ->execute([':nid' => $notifId, ':uid' => $currentUserId]);
        } catch (Exception $e) { /* silent */ }
    }

    // Validate target URL is relative (security: don't redirect to arbitrary external URLs)
    if (!empty($target) && strpos($target, '/') === 0) {
        header("Location: " . BASE_URL . $target);
    } else {
        header("Location: " . BASE_URL . "/customer/notifications.php");
    }
    exit;
}

$pageTitle = 'Notifications & Alerts';
$activePage = 'notifications';
require_once __DIR__ . '/includes/customer_header.php';

// Fetch all notifications for this customer, newest first
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC");
$notifStmt->execute([':uid' => $currentUserId]);
$notifications = $notifStmt->fetchAll();

/**
 * Build a smart navigation link based on notification type + message content.
 * Routes to the most relevant page within the customer portal.
 */
function buildNotifLink(array $n): string {
    $type    = $n['notification_type'] ?? 'system';
    $message = $n['message'] ?? '';
    $title   = $n['title'] ?? '';

    switch ($type) {
        case 'order_status':
            // If message mentions "ready for pickup" or "completed" → show all orders
            // Otherwise show active orders
            $isActive = stripos($message, 'placed') !== false
                     || stripos($message, 'accepted') !== false
                     || stripos($message, 'ready') !== false;
            return $isActive ? "/customer/orders.php?status=active" : "/customer/orders.php";

        case 'restock_alert':
            return "/customer/products.php";

        case 'announcement':
            return "/customer/markets.php";

        default:
            return "/customer/dashboard.php";
    }
}
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>🔔</span> Notifications &amp; Alerts
    </h1>
    <p class="page-subheading">
      Live updates on pre-orders, pickup slot readiness, and farmer announcements
    </p>
  </div>

  <?php if ($unreadNotifsCount > 0): ?>
    <form method="POST" action="<?= BASE_URL ?>/customer/notifications.php">
      <button type="submit" name="mark_all_read" value="1"
              class="btn-secondary"
              style="padding:0.5rem 1.15rem; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem;">
        <span>✔</span> Mark All as Read (<?= $unreadNotifsCount ?>)
      </button>
    </form>
  <?php endif; ?>
</div>

<div class="portal-card">
  <?php if (!empty($notifications)): ?>
    <div style="display:flex; flex-direction:column; gap:0.75rem;">
      <?php foreach ($notifications as $n):
        $type     = $n['notification_type'];
        $isUnread = !$n['is_read'];
        $link     = buildNotifLink($n);

        $icon = match($type) {
          'order_status'  => '📦',
          'restock_alert' => '🥬',
          'announcement'  => '📢',
          default         => '🌱'
        };

        $typeLabel = match($type) {
          'order_status'  => 'Order Update',
          'restock_alert' => 'Restock Alert',
          'announcement'  => 'Announcement',
          default         => 'System'
        };

        $typeBg = match($type) {
          'order_status'  => 'rgba(56,189,248,0.15)',
          'restock_alert' => 'rgba(34,197,94,0.15)',
          'announcement'  => 'rgba(251,191,36,0.15)',
          default         => 'rgba(148,163,184,0.15)'
        };

        $typeColor = match($type) {
          'order_status'  => 'var(--sky-400)',
          'restock_alert' => 'var(--emerald-400)',
          'announcement'  => '#fbbf24',
          default         => 'var(--text-muted)'
        };
      ?>

        <!-- Notification Row — clicking marks as read + navigates to relevant page -->
        <form method="POST" action="<?= BASE_URL ?>/customer/notifications.php" style="margin:0;">
          <input type="hidden" name="read_and_go" value="1">
          <input type="hidden" name="notif_id" value="<?= (int)$n['notification_id'] ?>">
          <input type="hidden" name="target_url" value="<?= htmlspecialchars($link) ?>">
          <button type="submit" style="width:100%; background:none; border:none; padding:0; cursor:pointer; text-align:left;">
            <div style="background: <?= $isUnread ? 'var(--bg-surface-elevated)' : 'var(--bg-secondary)' ?>;
                        border: 1px solid <?= $isUnread ? 'var(--emerald-500)' : 'var(--border-subtle)' ?>;
                        border-radius: var(--radius-lg);
                        padding: 1.1rem 1.25rem;
                        display: flex;
                        gap: 1rem;
                        align-items: flex-start;
                        transition: all 0.18s;
                        position: relative;"
                 onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.18)'"
                 onmouseout="this.style.transform=''; this.style.boxShadow=''">

              <!-- Icon -->
              <div style="width:44px; height:44px; border-radius:var(--radius-md);
                          background: <?= $isUnread ? 'rgba(16,185,129,0.18)' : 'var(--bg-surface)' ?>;
                          display:flex; align-items:center; justify-content:center;
                          font-size:1.35rem; flex-shrink:0;">
                <?= $icon ?>
              </div>

              <!-- Content -->
              <div style="flex:1; min-width:0;">
                <!-- Title row -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.3rem; flex-wrap:wrap; gap:0.5rem;">
                  <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                    <h4 style="font-family:var(--font-heading); font-size:0.9375rem; font-weight:700; color:var(--text-primary); margin:0;">
                      <?= htmlspecialchars($n['title']) ?>
                    </h4>
                    <?php if ($isUnread): ?>
                      <span style="background:var(--emerald-500); color:#fff; font-size:0.6rem; font-weight:800; padding:0.12rem 0.5rem; border-radius:var(--radius-full); text-transform:uppercase; letter-spacing:0.05em;">
                        New
                      </span>
                    <?php endif; ?>
                    <!-- Type label pill -->
                    <span style="background: <?= $typeBg ?>; color: <?= $typeColor ?>; font-size:0.65rem; font-weight:700; padding:0.12rem 0.55rem; border-radius:var(--radius-full); text-transform:uppercase; letter-spacing:0.05em;">
                      <?= $typeLabel ?>
                    </span>
                  </div>
                  <span style="font-size:0.72rem; color:var(--text-muted); white-space:nowrap;">
                    <?= date('M d, Y · h:i A', strtotime($n['created_at'])) ?>
                  </span>
                </div>

                <!-- Message -->
                <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.55; margin:0 0 0.5rem;">
                  <?= htmlspecialchars($n['message']) ?>
                </p>

                <!-- CTA hint -->
                <div style="font-size:0.72rem; color: <?= $typeColor ?>; font-weight:600; display:flex; align-items:center; gap:0.3rem;">
                  <span>→</span>
                  <?= match($type) {
                    'order_status'  => 'View order details',
                    'restock_alert' => 'Browse available produce',
                    'announcement'  => 'View market schedule',
                    default         => 'Go to dashboard'
                  } ?>
                </div>
              </div>

              <!-- Unread indicator dot -->
              <?php if ($isUnread): ?>
                <div style="position:absolute; top:1rem; right:1rem; width:8px; height:8px; border-radius:50%; background:var(--emerald-500); box-shadow:0 0 8px rgba(34,197,94,0.7); flex-shrink:0;"></div>
              <?php endif; ?>
            </div>
          </button>
        </form>

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
