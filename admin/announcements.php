<?php
/**
 * MarketLink - Admin Platform Announcements & Broadcasts
 * SRS Section 1.6: Admin can publish platform-wide notifications or announcements.
 */

$pageTitle = 'Platform Announcements';
$activeNav = 'announcements';
require_once __DIR__ . '/includes/admin_header.php';

$announcements = $pdo->query("SELECT pa.*, u.username as admin_name 
                              FROM platform_announcements pa 
                              JOIN users u ON pa.admin_id = u.user_id 
                              ORDER BY pa.created_at DESC")->fetchAll();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="megaphone" style="color:var(--admin-accent);"></i>
      Platform Announcements &amp; Broadcasts
    </h1>
    <p>Broadcast system updates, market holiday notices, and seasonal reminders to farmers and shoppers.</p>
  </div>

  <div class="admin-header-actions">
    <button type="button" class="admin-btn admin-btn-primary" onclick="openAnnouncementModal()">
      <i data-lucide="plus-circle"></i>
      <span>New Announcement</span>
    </button>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="bell" style="color:var(--admin-emerald);"></i>
        Active Platform Notices (<?= count($announcements) ?>)
      </h3>
      <p class="admin-card-subtitle">Public and role-targeted system communications</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Notice Title</th>
          <th>Message Content</th>
          <th>Target Role</th>
          <th>Published Date</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($announcements)): ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No announcements published yet. Click "New Announcement" above to broadcast to users.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($announcements as $a): ?>
            <tr>
              <td>
                <div style="font-weight:700; color:var(--admin-text-main); font-size:0.95rem;">
                  <?= htmlspecialchars($a['title']) ?>
                </div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  By @<?= htmlspecialchars($a['admin_name']) ?>
                </div>
              </td>
              <td style="max-width:380px;">
                <div style="font-size:0.875rem; color:var(--admin-text-muted); line-height:1.4;">
                  <?= nl2br(htmlspecialchars($a['content'])) ?>
                </div>
              </td>
              <td>
                <span style="font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;
                             padding:0.2rem 0.55rem; border-radius:var(--radius-full);
                             background:<?= $a['target_role'] === 'farmer' ? 'rgba(16,185,129,0.15)' : ($a['target_role'] === 'customer' ? 'rgba(6,182,212,0.15)' : 'rgba(99,102,241,0.15)') ?>;
                             color:<?= $a['target_role'] === 'farmer' ? '#34d399' : ($a['target_role'] === 'customer' ? '#22d3ee' : '#818cf8') ?>;">
                  <?= ucfirst($a['target_role']) ?>
                </span>
              </td>
              <td style="font-size:0.8125rem; color:var(--admin-text-subtle); white-space:nowrap;">
                <?= date('M d, Y H:i', strtotime($a['created_at'])) ?>
              </td>
              <td>
                <span class="status-pill <?= $a['is_active'] ? 'active' : 'suspended' ?>">
                  <?= $a['is_active'] ? 'Active' : 'Archived' ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                  <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                          onclick="toggleAnnouncement(<?= $a['announcement_id'] ?>, <?= $a['is_active'] ? 0 : 1 ?>)">
                    <?= $a['is_active'] ? '<i data-lucide="archive"></i> Archive' : '<i data-lucide="check"></i> Activate' ?>
                  </button>
                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                          onclick="deleteAnnouncement(<?= $a['announcement_id'] ?>)">
                    <i data-lucide="trash-2"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: New Announcement -->
<div class="admin-modal-backdrop" id="announcementModalBackdrop">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <h3>Broadcast New Announcement</h3>
      <button type="button" class="admin-modal-close" onclick="closeAnnouncementModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form onsubmit="saveAnnouncement(event)">
      <input type="hidden" name="action" value="create">
      <div class="admin-modal-body">
        <div class="admin-form-group">
          <label class="admin-form-label">Notice Title *</label>
          <input type="text" name="title" class="admin-form-control" required placeholder="e.g. Harvest Festival & Saturday Market Schedule">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Target Audience *</label>
          <select name="target_role" class="admin-form-control">
            <option value="all">All Community (Farmers &amp; Customers)</option>
            <option value="farmer">Farmers / Producers Only</option>
            <option value="customer">Retail Customers Only</option>
          </select>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Announcement Content *</label>
          <textarea name="content" rows="4" class="admin-form-control" required placeholder="Write the complete notice message here..."></textarea>
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
          <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
            <input type="checkbox" name="broadcast_notifications" value="1" checked style="accent-color:var(--admin-accent); width:18px; height:18px;">
            <span style="font-size:0.875rem; font-weight:600; color:var(--admin-text-main);">
              Broadcast as instant In-App notification alert to all targeted user accounts
            </span>
          </label>
        </div>
      </div>

      <div class="admin-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAnnouncementModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i data-lucide="send"></i> Publish &amp; Broadcast
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openAnnouncementModal() {
  document.getElementById('announcementModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAnnouncementModal() {
  document.getElementById('announcementModalBackdrop').classList.remove('open');
}

async function saveAnnouncement(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);

  try {
    const res = await fetch('<?= BASE_URL ?>/admin/api/announcement_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      closeAnnouncementModal();
      setTimeout(() => window.location.reload(), 600);
    } else {
      showAdminToast(data.message || 'Broadcast failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network error while broadcasting announcement', 'error');
  }
}

async function toggleAnnouncement(id, status) {
  try {
    const fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('announcement_id', id);
    fd.append('is_active', status);

    const res = await fetch('<?= BASE_URL ?>/admin/api/announcement_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 500);
    }
  } catch (err) {
    showAdminToast('Network connection error', 'error');
  }
}

async function deleteAnnouncement(id) {
  if (!confirm('Are you sure you want to delete this announcement?')) return;

  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('announcement_id', id);

    const res = await fetch('<?= BASE_URL ?>/admin/api/announcement_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 500);
    }
  } catch (err) {
    showAdminToast('Network connection error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
