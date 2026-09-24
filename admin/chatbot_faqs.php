<?php
/**
 * MarketLink - Admin AI Chatbot Knowledge Base & FAQs
 * SRS Section 1.6: Manage automated assistant responses and FAQ training data.
 */

$pageTitle = 'AI Chatbot Knowledge';
$activeNav = 'chatbot_faqs';
require_once __DIR__ . '/includes/admin_header.php';

$faqs = $pdo->query("SELECT * FROM ai_chatbot_faqs ORDER BY category ASC, faq_id ASC")->fetchAll();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="bot" style="color:var(--admin-accent);"></i>
      AI Chatbot Knowledge Base &amp; FAQs
    </h1>
    <p>Train the FarmLink AI assistant with market questions, pickup policies, and seasonal produce guidelines.</p>
  </div>

  <div class="admin-header-actions">
    <button type="button" class="admin-btn admin-btn-primary" onclick="openFaqModal()">
      <i data-lucide="plus-circle"></i>
      <span>Add Training FAQ</span>
    </button>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="brain" style="color:var(--admin-purple);"></i>
        Trained Assistant Responses (<?= count($faqs) ?>)
      </h3>
      <p class="admin-card-subtitle">Knowledge entries matched against customer queries in real-time</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Category</th>
          <th>Question / Trigger</th>
          <th>Bot Answer Response</th>
          <th>Keywords</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($faqs as $f): ?>
          <tr>
            <td>
              <span style="font-size:0.75rem; font-weight:700; padding:0.2rem 0.55rem; border-radius:var(--radius-full); background:rgba(139,92,246,0.15); color:#a78bfa;">
                <?= htmlspecialchars($f['category']) ?>
              </span>
            </td>
            <td style="max-width:240px; font-weight:700; color:var(--admin-text-main);">
              <?= htmlspecialchars($f['question']) ?>
            </td>
            <td style="max-width:380px; font-size:0.875rem; color:var(--admin-text-muted); line-height:1.4;">
              <?= htmlspecialchars($f['answer']) ?>
            </td>
            <td style="max-width:180px;">
              <div style="font-size:0.75rem; color:var(--admin-cyan); font-family:monospace;">
                <?= htmlspecialchars($f['keywords'] ?: '—') ?>
              </div>
            </td>
            <td>
              <span class="status-pill <?= $f['is_active'] ? 'active' : 'inactive' ?>">
                <?= $f['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                        onclick='editFaq(<?= json_encode($f) ?>)'>
                  <i data-lucide="edit-3"></i> Edit
                </button>
                <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                        onclick="deleteFaq(<?= $f['faq_id'] ?>)">
                  <i data-lucide="trash-2"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add / Edit FAQ -->
<div class="admin-modal-backdrop" id="faqModalBackdrop">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <h3 id="faqModalTitle">Add Chatbot FAQ</h3>
      <button type="button" class="admin-modal-close" onclick="closeFaqModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form onsubmit="saveFaq(event)">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="faq_id" id="faq_id" value="0">

      <div class="admin-modal-body">
        <div class="admin-form-group">
          <label class="admin-form-label">Category *</label>
          <input type="text" name="category" id="faq_cat" class="admin-form-control" required placeholder="e.g. Orders, Pickup, Markets, Produce">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Customer Question *</label>
          <input type="text" name="question" id="faq_q" class="admin-form-control" required placeholder="e.g. When can I pick up my pre-order?">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Assistant Bot Answer *</label>
          <textarea name="answer" id="faq_a" rows="4" class="admin-form-control" required placeholder="Provide clear, concise answer..."></textarea>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Search Keywords (Comma Separated)</label>
          <input type="text" name="keywords" id="faq_kw" class="admin-form-control" placeholder="e.g. pickup, time, slot, hour, collect">
        </div>

        <div class="admin-form-group" style="margin-bottom:0;">
          <label style="display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
            <input type="checkbox" name="is_active" id="faq_active" value="1" checked style="accent-color:var(--admin-accent); width:18px; height:18px;">
            <span style="font-size:0.875rem; font-weight:600; color:var(--admin-text-main);">
              Active in AI knowledge retrieval
            </span>
          </label>
        </div>
      </div>

      <div class="admin-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeFaqModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i data-lucide="save"></i> Save Knowledge Entry
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openFaqModal() {
  document.getElementById('faqModalTitle').textContent = 'Add Chatbot FAQ Entry';
  document.getElementById('faq_id').value = '0';
  document.getElementById('faq_cat').value = 'General';
  document.getElementById('faq_q').value = '';
  document.getElementById('faq_a').value = '';
  document.getElementById('faq_kw').value = '';
  document.getElementById('faq_active').checked = true;
  document.getElementById('faqModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function editFaq(f) {
  document.getElementById('faqModalTitle').textContent = 'Edit Chatbot FAQ Entry';
  document.getElementById('faq_id').value = f.faq_id;
  document.getElementById('faq_cat').value = f.category;
  document.getElementById('faq_q').value = f.question;
  document.getElementById('faq_a').value = f.answer;
  document.getElementById('faq_kw').value = f.keywords || '';
  document.getElementById('faq_active').checked = parseInt(f.is_active) === 1;
  document.getElementById('faqModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeFaqModal() {
  document.getElementById('faqModalBackdrop').classList.remove('open');
}

async function saveFaq(e) {
  e.preventDefault();
  const fd = new FormData(e.target);

  try {
    const res = await fetch('<?= BASE_URL ?>/admin/api/faq_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      closeFaqModal();
      setTimeout(() => window.location.reload(), 500);
    } else {
      showAdminToast(data.message || 'Error occurred', 'error');
    }
  } catch (err) {
    showAdminToast('Network connection error', 'error');
  }
}

async function deleteFaq(id) {
  if (!confirm('Are you sure you want to delete this FAQ knowledge entry?')) return;

  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('faq_id', id);

    const res = await fetch('<?= BASE_URL ?>/admin/api/faq_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 500);
    } else {
      showAdminToast(data.message || 'Delete failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network communication error', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
