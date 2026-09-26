<?php
/**
 * MarketLink - Admin Contact Inquiries & Direct Email Support
 * Review incoming messages, compose replies, and dispatch transactional emails
 */

$pageTitle = 'Contact Inquiries & Support';
$activeNav = 'inquiries';

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/mailer.php';

// Handle Admin Reply Submission
$flashSuccess = '';
$flashError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply_inquiry') {
        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        $replyText = trim($_POST['admin_reply'] ?? '');

        if ($inquiryId <= 0 || empty($replyText)) {
            $flashError = 'Reply content cannot be empty.';
        } else {
            // Fetch inquiry
            $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
            $stmt->execute([$inquiryId]);
            $inq = $stmt->fetch();

            if (!$inq) {
                $flashError = 'Inquiry not found.';
            } else {
                try {
                    // Update database
                    $updateStmt = $pdo->prepare("UPDATE contact_messages 
                                                 SET admin_reply = :reply, 
                                                     status = 'replied', 
                                                     replied_at = NOW(), 
                                                     replied_by = :admin_id 
                                                 WHERE id = :id");
                    $updateStmt->execute([
                        ':reply'    => $replyText,
                        ':admin_id' => $adminId,
                        ':id'       => $inquiryId
                    ]);

                    // Send email to customer
                    $adminDisplayName = !empty($_SESSION['username']) ? ucfirst($_SESSION['username']) . ' (MarketLink Admin)' : 'MarketLink Support Team';
                    $emailDispatched = sendAdminReplyEmail(
                        $inq['name'], 
                        $inq['email'], 
                        $inq['subject'], 
                        $inq['message'], 
                        $replyText, 
                        $adminDisplayName
                    );

                    if ($emailDispatched) {
                        $flashSuccess = "Reply successfully saved and emailed to <strong>" . htmlspecialchars($inq['email']) . "</strong>!";
                    } else {
                        $flashSuccess = "Reply saved to system. (Note: Email delivery failed or SMTP timed out, but reply is recorded).";
                    }
                } catch (Exception $e) {
                    $flashError = 'Database error: ' . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'mark_read') {
        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        if ($inquiryId > 0) {
            $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ? AND status = 'unread'")->execute([$inquiryId]);
            $flashSuccess = "Message marked as read.";
        }
    } elseif ($_POST['action'] === 'delete_inquiry') {
        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
        if ($inquiryId > 0) {
            $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$inquiryId]);
            $flashSuccess = "Inquiry deleted successfully.";
        }
    }
}

// Filter by Status only
$filterStatus = $_GET['status'] ?? 'all';

$sql = "SELECT cm.*, u.username as replied_by_username 
        FROM contact_messages cm 
        LEFT JOIN users u ON cm.replied_by = u.user_id 
        WHERE 1=1";
$params = [];

if ($filterStatus === 'unread') {
    $sql .= " AND cm.status = 'unread'";
} elseif ($filterStatus === 'replied') {
    $sql .= " AND cm.status = 'replied'";
} elseif ($filterStatus === 'read') {
    $sql .= " AND cm.status = 'read'";
}

$sql .= " ORDER BY cm.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

// Metrics
$metricTotal   = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$metricUnread  = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$metricReplied = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn();

// Check if a specific inquiry ID is opened via query param
$activeInquiryId = (int)($_GET['id'] ?? 0);
$activeInquiry = null;
if ($activeInquiryId > 0) {
    foreach ($inquiries as $item) {
        if ($item['id'] === $activeInquiryId) {
            $activeInquiry = $item;
            break;
        }
    }
    // If not in filtered list, query directly
    if (!$activeInquiry) {
        $singleStmt = $pdo->prepare("SELECT cm.*, u.username as replied_by_username FROM contact_messages cm LEFT JOIN users u ON cm.replied_by = u.user_id WHERE cm.id = ?");
        $singleStmt->execute([$activeInquiryId]);
        $activeInquiry = $singleStmt->fetch();
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="mail-question" style="color:var(--admin-emerald, #34d399);"></i>
      Customer Inquiries &amp; Support Central
    </h1>
    <p>Manage incoming communications from the public contact form, reply directly to users, and send real-time confirmation emails.</p>
  </div>
</div>

<?php if ($flashSuccess): ?>
  <div class="admin-alert admin-alert-success" style="background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:1rem 1.4rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
    <i data-lucide="check-circle"></i>
    <div><?= $flashSuccess ?></div>
  </div>
<?php endif; ?>

<?php if ($flashError): ?>
  <div class="admin-alert admin-alert-danger" style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#f87171; padding:1rem 1.4rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
    <i data-lucide="alert-triangle"></i>
    <div><?= htmlspecialchars($flashError) ?></div>
  </div>
<?php endif; ?>

<!-- Metric Counter Cards -->
<div class="admin-metrics-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
  <div class="admin-card" style="padding:1.4rem 1.6rem; border-radius:16px;">
    <div style="font-size:0.82rem; font-weight:700; color:var(--admin-text-subtle, #94a3b8); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.5rem;">Total Messages</div>
    <div style="font-size:2rem; font-weight:800; color:#f8fafc;"><?= $metricTotal ?></div>
  </div>

  <div class="admin-card" style="padding:1.4rem 1.6rem; border-radius:16px; border-left:4px solid #ef4444;">
    <div style="font-size:0.82rem; font-weight:700; color:#f87171; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.5rem;">Unread / Needs Reply</div>
    <div style="font-size:2rem; font-weight:800; color:#fca5a5;"><?= $metricUnread ?></div>
  </div>

  <div class="admin-card" style="padding:1.4rem 1.6rem; border-radius:16px; border-left:4px solid #10b981;">
    <div style="font-size:0.82rem; font-weight:700; color:#34d399; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.5rem;">Replied &amp; Resolved</div>
    <div style="font-size:2rem; font-weight:800; color:#6ee7b7;"><?= $metricReplied ?></div>
  </div>
</div>

<!-- Status Filter Toolbar -->
<div class="admin-card" style="padding:1.25rem 1.5rem; margin-bottom:1.5rem; border-radius:16px;">
  <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem;">
    <a href="?status=all" 
       class="admin-btn <?= $filterStatus === 'all' ? 'admin-btn-primary' : 'admin-btn-outline' ?>" style="border-radius:999px; padding:0.45rem 1.1rem; font-size:0.85rem;">
      All (<?= $metricTotal ?>)
    </a>
    <a href="?status=unread" 
       class="admin-btn <?= $filterStatus === 'unread' ? 'admin-btn-primary' : 'admin-btn-outline' ?>" style="border-radius:999px; padding:0.45rem 1.1rem; font-size:0.85rem;">
      Unread (<?= $metricUnread ?>)
    </a>
    <a href="?status=replied" 
       class="admin-btn <?= $filterStatus === 'replied' ? 'admin-btn-primary' : 'admin-btn-outline' ?>" style="border-radius:999px; padding:0.45rem 1.1rem; font-size:0.85rem;">
      Replied (<?= $metricReplied ?>)
    </a>
    <a href="?status=read" 
       class="admin-btn <?= $filterStatus === 'read' ? 'admin-btn-primary' : 'admin-btn-outline' ?>" style="border-radius:999px; padding:0.45rem 1.1rem; font-size:0.85rem;">
      Read
    </a>
  </div>
</div>

<!-- Main Inquiries Table -->
<div class="admin-card" style="border-radius:16px; overflow:hidden;">
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Ticket #</th>
          <th>Sender</th>
          <th>Subject &amp; Preview</th>
          <th>Date Received</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($inquiries)): ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:3.5rem 1rem; color:var(--admin-text-subtle, #94a3b8);">
              <i data-lucide="inbox" style="width:48px; height:48px; margin:0 auto 1rem; opacity:0.4; display:block;"></i>
              <div style="font-size:1.1rem; font-weight:600; color:#e2e8f0;">No inquiries found</div>
              <p style="font-size:0.88rem; margin-top:0.35rem;">There are no contact inquiries matching your current filter criteria.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($inquiries as $row): 
            $ticketCode = 'INQ-' . str_pad((string)$row['id'], 5, '0', STR_PAD_LEFT);
            $isUnread = ($row['status'] === 'unread');
            $isReplied = ($row['status'] === 'replied');
          ?>
            <tr style="<?= $isUnread ? 'background:rgba(239,68,68,0.04); font-weight:500;' : '' ?>">
              <td>
                <span style="font-family:monospace; font-weight:700; color:var(--admin-emerald, #34d399); font-size:0.88rem;">
                  #<?= $ticketCode ?>
                </span>
              </td>
              <td>
                <div style="font-weight:700; color:#f8fafc; font-size:0.95rem;"><?= htmlspecialchars($row['name']) ?></div>
                <div style="font-size:0.82rem; color:#94a3b8;">
                  <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color:#38bdf8; text-decoration:none;">
                    <?= htmlspecialchars($row['email']) ?>
                  </a>
                </div>
                <?php if (!empty($row['phone'])): ?>
                  <div style="font-size:0.78rem; color:#64748b;">📞 <?= htmlspecialchars($row['phone']) ?></div>
                <?php endif; ?>
              </td>
              <td style="max-width:320px;">
                <div style="font-weight:600; color:#e2e8f0; margin-bottom:0.25rem;">
                  <?= htmlspecialchars($row['subject']) ?>
                </div>
                <div style="font-size:0.82rem; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  <?= htmlspecialchars(mb_substr($row['message'], 0, 75)) ?>…
                </div>
              </td>
              <td style="font-size:0.84rem; color:#94a3b8; white-space:nowrap;">
                <?= date('d M Y', strtotime($row['created_at'])) ?><br>
                <span style="font-size:0.75rem; color:#64748b;"><?= date('h:i A', strtotime($row['created_at'])) ?></span>
              </td>
              <td>
                <?php if ($isReplied): ?>
                  <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.3rem 0.75rem; border-radius:999px; font-size:0.76rem; font-weight:700; background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(52,211,153,0.3);">
                    ✓ Replied
                  </span>
                <?php elseif ($isUnread): ?>
                  <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.3rem 0.75rem; border-radius:999px; font-size:0.76rem; font-weight:700; background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3);">
                    <span style="width:6px; height:6px; border-radius:50%; background:#ef4444; display:inline-block;"></span> Unread
                  </span>
                <?php else: ?>
                  <span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.3rem 0.75rem; border-radius:999px; font-size:0.76rem; font-weight:700; background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.25);">
                    Read
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <button type="button" 
                        class="admin-btn admin-btn-sm admin-btn-primary" 
                        onclick="openReplyModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                        style="border-radius:8px;">
                  <i data-lucide="message-square"></i>
                  <span><?= $isReplied ? 'View / Reply Again' : 'View &amp; Reply' ?></span>
                </button>

                <form method="POST" style="display:inline-block; margin-left:0.35rem;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                  <input type="hidden" name="action" value="delete_inquiry">
                  <input type="hidden" name="inquiry_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline" style="border-radius:8px; color:#ef4444;" title="Delete inquiry">
                    <i data-lucide="trash-2"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ==========================================================================
     REPLY / MESSAGE DETAILS MODAL
     ========================================================================== -->
<div id="replyModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(8px); z-index:9999; align-items:center; justify-content:center; padding:1.5rem;">
  <div class="admin-modal-content" style="background:#0c1d16; border:1px solid rgba(52,211,153,0.3); border-radius:20px; max-width:680px; width:100%; max-height:90vh; overflow-y:auto; padding:2rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); position:relative;">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:1.25rem;">
      <div>
        <span id="modalTicketBadge" style="font-family:monospace; font-weight:700; color:#34d399; font-size:0.85rem;">#INQ-00000</span>
        <h2 id="modalSubject" style="font-size:1.35rem; font-weight:800; color:#f8fafc; margin:0.35rem 0 0 0;">Inquiry Subject</h2>
      </div>
      <button type="button" class="admin-btn admin-btn-outline" onclick="closeReplyModal()" style="border-radius:50%; width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;">✕</button>
    </div>

    <!-- Customer Metadata Block -->
    <div style="background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:1.25rem; margin-bottom:1.5rem;">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; font-size:0.88rem;">
        <div>
          <span style="color:#94a3b8;">Sender Name:</span> 
          <strong id="modalSenderName" style="color:#f8fafc; margin-left:0.35rem;"></strong>
        </div>
        <div>
          <span style="color:#94a3b8;">Email Address:</span> 
          <a id="modalSenderEmail" href="#" style="color:#38bdf8; text-decoration:none; margin-left:0.35rem;"></a>
        </div>
        <div>
          <span style="color:#94a3b8;">Phone:</span> 
          <span id="modalSenderPhone" style="color:#e2e8f0; margin-left:0.35rem;"></span>
        </div>
        <div>
          <span style="color:#94a3b8;">Received:</span> 
          <span id="modalReceivedDate" style="color:#e2e8f0; margin-left:0.35rem;"></span>
        </div>
      </div>
    </div>

    <!-- Original Message -->
    <div style="margin-bottom:1.75rem;">
      <label style="font-size:0.82rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.04em; display:block; margin-bottom:0.5rem;">User Inquiry Message:</label>
      <div id="modalOriginalMessage" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:1.25rem; color:#f1f5f9; font-size:0.95rem; line-height:1.65; white-space:pre-wrap;"></div>
    </div>

    <!-- Existing Admin Reply (if already replied) -->
    <div id="existingReplyBox" style="display:none; background:rgba(16,185,129,0.08); border:1.5px solid rgba(52,211,153,0.3); border-radius:12px; padding:1.25rem; margin-bottom:1.75rem;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
        <span style="font-size:0.8rem; font-weight:700; color:#34d399; text-transform:uppercase;">💬 Previous Admin Response</span>
        <span id="existingReplyTime" style="font-size:0.78rem; color:#94a3b8;"></span>
      </div>
      <div id="existingReplyText" style="color:#a7f3d0; font-size:0.92rem; line-height:1.6; white-space:pre-wrap;"></div>
    </div>

    <!-- Admin Reply Form -->
    <form method="POST" action="">
      <input type="hidden" name="action" value="reply_inquiry">
      <input type="hidden" name="inquiry_id" id="replyInquiryId" value="">

      <div style="margin-bottom:1.25rem;">
        <label for="adminReplyInput" style="font-size:0.85rem; font-weight:700; color:#34d399; display:flex; align-items:center; gap:0.4rem; margin-bottom:0.5rem;">
          <i data-lucide="send" style="width:16px; height:16px;"></i>
          Compose Official Admin Reply (Will be Emailed Immediately):
        </label>
        <textarea id="adminReplyInput" 
                  name="admin_reply" 
                  rows="5" 
                  required
                  class="admin-input" 
                  placeholder="Dear Customer, thank you for reaching out..." 
                  style="width:100%; border-radius:12px; padding:1rem; line-height:1.5; font-size:0.92rem; background:rgba(0,0,0,0.4); border:1.5px solid rgba(52,211,153,0.3); color:#f8fafc;"></textarea>
      </div>

      <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:10px; padding:0.85rem 1rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.65rem; font-size:0.82rem; color:#7dd3fc;">
        <i data-lucide="mail-check" style="flex-shrink:0;"></i>
        <span>Clicking <strong>Send Reply &amp; Email</strong> will instantly dispatch an official branded notification email to the customer's mailbox.</span>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
        <button type="button" class="admin-btn admin-btn-outline" onclick="closeReplyModal()" style="border-radius:10px;">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary" style="border-radius:10px; padding:0.65rem 1.4rem;">
          <i data-lucide="send"></i>
          <span>Send Reply &amp; Email Customer</span>
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openReplyModal(inq) {
  const modal = document.getElementById('replyModal');
  const ticketCode = 'INQ-' + String(inq.id).padStart(5, '0');
  
  document.getElementById('modalTicketBadge').textContent = '#' + ticketCode;
  document.getElementById('modalSubject').textContent = inq.subject;
  document.getElementById('modalSenderName').textContent = inq.name;
  
  const emailEl = document.getElementById('modalSenderEmail');
  emailEl.textContent = inq.email;
  emailEl.href = 'mailto:' + inq.email;

  document.getElementById('modalSenderPhone').textContent = inq.phone || 'Not provided';
  document.getElementById('modalReceivedDate').textContent = inq.created_at;
  document.getElementById('modalOriginalMessage').textContent = inq.message;
  document.getElementById('replyInquiryId').value = inq.id;

  const existingBox = document.getElementById('existingReplyBox');
  if (inq.admin_reply) {
    existingBox.style.display = 'block';
    document.getElementById('existingReplyText').textContent = inq.admin_reply;
    document.getElementById('existingReplyTime').textContent = inq.replied_at ? ('Replied: ' + inq.replied_at) : '';
  } else {
    existingBox.style.display = 'none';
  }

  modal.style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}

function closeReplyModal() {
  document.getElementById('replyModal').style.display = 'none';
}

// Auto open if active inquiry from query param
<?php if ($activeInquiry): ?>
document.addEventListener('DOMContentLoaded', function() {
  openReplyModal(<?= json_encode($activeInquiry, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
