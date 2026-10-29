<?php
/**
 * MarketLink - Farmer / Seller Order Messages
 * Real-time order messaging system between Farmer and Customer
 * Supports text, photos, documents, and auto-closure on order completion
 */

$pageTitle = 'Order Messages & Inquiries';
$activePage = 'chat';
require_once __DIR__ . '/includes/farmer_header.php';
require_once __DIR__ . '/../config/pusher.php';

$initialOrderId = (int)($_GET['order_id'] ?? 0);
?>

<div class="farmer-content">

  <!-- Header -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
      <h1 style="font-family:var(--font-heading); font-size:1.75rem; font-weight:800; color:var(--text-primary); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.65rem;">
        <i class="fa-solid fa-comments" style="color:var(--primary-500);"></i>
        <span>Customer Order Messages</span>
      </h1>
      <p style="color:var(--slate-400); font-size:0.875rem;">
        Real-time messaging channel with customers who have reserved harvest pre-orders with your stall.
      </p>
    </div>
  </div>

  <!-- Chat Portal Layout -->
  <div class="chat-portal-layout" id="chatPortalApp">

    <!-- Left: Conversations List -->
    <aside class="chat-sidebar-panel">
      <div class="chat-sidebar-header">
        <div class="chat-search-box">
          <i class="fa-solid fa-magnifying-glass" style="font-size:0.85rem; color:var(--text-muted);"></i>
          <input type="text" id="convSearchInput" placeholder="Search orders, customers..." autocomplete="off">
        </div>
      </div>

      <div class="chat-conversations-list" id="convListContainer">
        <div style="padding:2.5rem 1rem; text-align:center; color:var(--text-muted); font-size:0.875rem;">
          <i class="fa-solid fa-spinner fa-spin" style="margin-right:0.4rem; color:var(--primary-500);"></i> Loading customer chats...
        </div>
      </div>
    </aside>

    <!-- Right: Chat Thread Window -->
    <main class="chat-main-window">
      
      <!-- Empty State (No chat selected) -->
      <div id="chatEmptyState" class="chat-empty-panel">
        <div class="chat-empty-icon-wrap">
          <i class="fa-solid fa-comments"></i>
        </div>
        <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem; font-family:var(--font-heading);">
          Select a Customer Conversation
        </h3>
        <p style="color:var(--text-secondary); max-width:420px; margin:0 auto 1.5rem; font-size:0.9rem; line-height:1.55;">
          Select an active pre-order from the left panel to coordinate pickup slots, answer customer questions, or share harvest photos.
        </p>
      </div>

      <!-- Active Chat Thread -->
      <div id="chatActiveThread" class="chat-thread-wrapper" style="display:none;">
        
        <!-- Thread Header -->
        <div class="chat-thread-header">
          <div style="display:flex; align-items:center; gap:0.85rem;">
            <button type="button" class="chat-back-mobile-btn" id="btnBackToConvList" aria-label="Back to conversations">
              <i class="fa-solid fa-arrow-left"></i>
            </button>
            <div class="chat-avatar-circle" id="threadAvatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); line-height:1.2; font-family:var(--font-heading);" id="threadTitle">
                Customer Name
              </div>
              <div style="font-size:0.75rem; color:var(--slate-400); margin-top:2px;" id="threadSubtitle">
                Order #ML-XXXX &bull; Pickup Date
              </div>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:0.65rem;">
            <button type="button" id="btnRefreshChat" class="chat-header-action-btn" title="Refresh messages">
              <i class="fa-solid fa-arrows-rotate" id="refreshChatIcon" aria-hidden="true"></i>
              <span>Refresh</span>
            </button>
            <span class="farmer-status-badge badge-placed" id="threadStatusBadge">● Placed</span>
            <a href="#" id="threadOrderLink" class="chat-header-action-btn" style="text-decoration:none;" title="View order packing slip">
              <i class="fa-solid fa-receipt" aria-hidden="true"></i>
              <span>Order Slip</span>
            </a>
          </div>
        </div>

        <!-- Closed Banner (Shown if order is completed / cancelled) -->
        <div id="chatClosedBanner" class="chat-closed-alert" style="display:none;">
          <i class="fa-solid fa-lock" style="font-size:1rem; flex-shrink:0;"></i>
          <div>
            <strong>Order Fulfilled / Closed:</strong>
            This pre-order is closed. The messaging thread is preserved in read-only mode for historical records.
          </div>
        </div>

        <!-- Messages Stream Area -->
        <div class="chat-messages-stream" id="messagesContainer">
          <!-- Messages rendered dynamically via JS -->
        </div>

        <!-- Attachment Preview Chip -->
        <div id="attachmentPreviewBox" class="chat-attachment-preview" style="display:none;">
          <div style="display:flex; align-items:center; gap:0.6rem; font-size:0.85rem; color:var(--text-primary);">
            <span id="attachIconPreview" style="color:var(--primary-400);"><i class="fa-solid fa-paperclip"></i></span>
            <span id="attachNamePreview" style="font-weight:700; max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">file.pdf</span>
          </div>
          <button type="button" id="btnRemoveAttachment" style="background:none; border:none; color:var(--rose-400, #f87171); cursor:pointer; font-weight:700; font-size:1.1rem; padding:0 0.25rem;">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Message Composer -->
        <div class="chat-composer-container" id="chatComposerBox">
          <form id="chatMessageForm" style="display:flex; align-items:center; gap:0.65rem; width:100%;">
            <!-- Hidden File Input -->
            <input type="file" id="chatFileInput" name="attachment" style="display:none;" 
                   accept="image/*,.pdf,.doc,.docx,.txt,.zip">

            <!-- Attachment Trigger Button -->
            <button type="button" id="btnTriggerFile" class="chat-btn-icon" title="Attach Photo or Document">
              <i class="fa-solid fa-paperclip"></i>
            </button>

            <!-- Text Input -->
            <input type="text" id="chatTextInput" placeholder="Type a message to the customer..." 
                   class="chat-text-input" autocomplete="off">

            <!-- Send Button -->
            <button type="submit" id="btnSendMessage" class="chat-btn-send" title="Send Message">
              <span>Send</span>
              <i class="fa-solid fa-paper-plane" style="font-size:0.85rem;"></i>
            </button>
          </form>
        </div>

      </div>

    </main>
  </div>

</div><!-- /.farmer-content -->

<!-- Image Lightbox Modal for Photo Attachments -->
<div id="imageLightboxModal" class="chat-lightbox-backdrop" onclick="this.style.display='none'">
  <div class="chat-lightbox-content" onclick="event.stopPropagation();">
    <img src="" id="lightboxImg" alt="Attachment Preview">
    <button type="button" class="chat-lightbox-close" onclick="document.getElementById('imageLightboxModal').style.display='none'">✕</button>
  </div>
</div>

<style>
/* Chat Portal Responsive Styling for Farmer Console */
.chat-portal-layout {
  display: flex;
  height: calc(100vh - 230px);
  min-height: 580px;
  background: var(--bg-surface);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
  position: relative;
}

[data-theme="light"] .chat-portal-layout {
  background: #ffffff;
  border-color: var(--slate-200, #e2e8f0);
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.chat-sidebar-panel {
  width: 340px;
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  background: var(--bg-secondary);
  flex-shrink: 0;
}

[data-theme="light"] .chat-sidebar-panel {
  background: #f8fafc;
  border-right-color: #e2e8f0;
}

.chat-sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-surface);
}

[data-theme="light"] .chat-sidebar-header {
  background: #ffffff;
  border-bottom-color: #e2e8f0;
}

.chat-search-box {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 0.55rem 0.85rem;
  transition: all var(--transition-fast);
}

[data-theme="light"] .chat-search-box {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.chat-search-box:focus-within {
  border-color: var(--primary-500);
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

.chat-search-box input {
  border: none;
  background: transparent;
  outline: none;
  color: var(--text-primary);
  width: 100%;
  font-size: 0.875rem;
}

[data-theme="light"] .chat-search-box input {
  color: #0f172a;
}

.chat-search-box input::placeholder {
  color: var(--text-muted);
}

.chat-conversations-list {
  flex: 1;
  overflow-y: auto;
}

.chat-conv-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.9rem 1rem;
  border-bottom: 1px solid var(--border-color);
  cursor: pointer;
  transition: background 0.15s ease, border-left-color 0.15s ease;
  text-decoration: none;
  color: var(--text-primary);
}

[data-theme="light"] .chat-conv-item {
  border-bottom-color: #f1f5f9;
  color: #1e293b;
}

.chat-conv-item:hover {
  background: rgba(34, 197, 94, 0.08);
}

[data-theme="light"] .chat-conv-item:hover {
  background: #f0fdf4;
}

.chat-conv-item.active {
  background: rgba(34, 197, 94, 0.16);
  border-left: 4px solid var(--primary-500);
}

[data-theme="light"] .chat-conv-item.active {
  background: #ecfdf5;
  border-left-color: #10b981;
}

.chat-avatar-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-600, #16a34a), var(--primary-500, #22c55e));
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.05rem;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.chat-main-window {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--bg-surface);
  position: relative;
  overflow: hidden;
}

[data-theme="light"] .chat-main-window {
  background: #ffffff;
}

.chat-empty-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2.5rem 1.5rem;
  background: var(--bg-surface);
}

[data-theme="light"] .chat-empty-panel {
  background: #ffffff;
}

.chat-empty-icon-wrap {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.25);
  color: var(--primary-400, #4ade80);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.25rem;
  margin-bottom: 1.25rem;
}

[data-theme="light"] .chat-empty-icon-wrap {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #059669;
}

.chat-thread-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-thread-header {
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--bg-surface);
}

[data-theme="light"] .chat-thread-header {
  background: #ffffff;
  border-bottom-color: #e2e8f0;
}

.chat-header-action-btn {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: var(--radius-md);
  padding: 0.42rem 0.85rem;
  font-size: 0.8rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  font-family: var(--font-heading);
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

.chat-header-action-btn:hover {
  background: rgba(34, 197, 94, 0.16);
  border-color: var(--primary-400, #4ade80);
  color: var(--primary-400, #4ade80);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

.chat-header-action-btn:active {
  transform: translateY(0);
}

[data-theme="light"] .chat-header-action-btn {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #334155;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

[data-theme="light"] .chat-header-action-btn:hover {
  background: #ecfdf5;
  border-color: #10b981;
  color: #047857;
  box-shadow: 0 3px 8px rgba(16, 185, 129, 0.15);
}

.chat-back-mobile-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-primary);
  padding: 0.25rem;
}

.chat-closed-alert {
  background: rgba(239, 68, 68, 0.12);
  border-bottom: 1px solid rgba(239, 68, 68, 0.28);
  color: #f87171;
  padding: 0.75rem 1.25rem;
  font-size: 0.8125rem;
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

[data-theme="light"] .chat-closed-alert {
  background: #fef2f2;
  border-bottom-color: #fecaca;
  color: #dc2626;
}

.chat-messages-stream {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  background: var(--bg-primary);
}

[data-theme="light"] .chat-messages-stream {
  background: #f8fafc;
}

.chat-bubble-row {
  display: flex;
  width: 100%;
}

.chat-bubble-row.mine {
  justify-content: flex-end;
}

.chat-bubble-row.theirs {
  justify-content: flex-start;
}

.chat-bubble {
  max-width: 72%;
  padding: 0.85rem 1.15rem;
  border-radius: 16px;
  font-size: 0.9rem;
  line-height: 1.55;
  position: relative;
  word-break: break-word;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.chat-bubble-row.mine .chat-bubble {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: #ffffff !important;
  border-bottom-right-radius: 4px;
}

.chat-bubble-row.mine .chat-bubble * {
  color: #ffffff;
}

.chat-bubble-row.theirs .chat-bubble {
  background: var(--bg-surface);
  color: var(--text-primary) !important;
  border: 1px solid var(--border-color);
  border-bottom-left-radius: 4px;
}

[data-theme="light"] .chat-bubble-row.theirs .chat-bubble {
  background: #ffffff;
  border-color: #e2e8f0;
  color: #1e293b !important;
  box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.chat-bubble-row.theirs .chat-bubble * {
  color: var(--text-primary);
}

[data-theme="light"] .chat-bubble-row.theirs .chat-bubble * {
  color: #1e293b;
}

.chat-bubble-meta {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.4rem;
  font-size: 0.6875rem;
  margin-top: 5px;
  opacity: 0.85;
}

.chat-bubble-row.mine .chat-bubble-meta {
  color: rgba(255, 255, 255, 0.9);
}

.chat-bubble-row.theirs .chat-bubble-meta {
  color: var(--text-muted) !important;
}

.chat-attachment-img {
  width: 100%;
  max-width: 280px;
  max-height: 240px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  display: block;
  margin-top: 8px;
  border: 1px solid var(--border-color);
  transition: transform 0.2s ease;
}

.chat-attachment-img:hover {
  transform: scale(1.02);
}

.chat-doc-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-radius: 8px;
  padding: 0.6rem 0.85rem;
  margin-top: 8px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.chat-bubble-row.theirs .chat-doc-card {
  background: rgba(56, 189, 248, 0.08);
  border: 1px solid var(--border-color);
  color: var(--text-primary) !important;
}

.chat-bubble-row.mine .chat-doc-card {
  background: rgba(255,255,255,0.2);
  color: #ffffff !important;
}

.chat-attachment-preview {
  padding: 0.65rem 1.25rem;
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

[data-theme="light"] .chat-attachment-preview {
  background: #f8fafc;
  border-top-color: #e2e8f0;
}

.chat-composer-container {
  padding: 0.85rem 1.25rem;
  background: var(--bg-surface);
  border-top: 1px solid var(--border-color);
}

[data-theme="light"] .chat-composer-container {
  background: #ffffff;
  border-top-color: #e2e8f0;
}

.chat-text-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
  background: var(--bg-primary);
  color: var(--text-primary);
  outline: none;
  font-size: 0.9rem;
  transition: border-color var(--transition-fast);
}

[data-theme="light"] .chat-text-input {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #0f172a;
}

.chat-text-input:focus {
  border-color: var(--primary-500);
}

.chat-text-input::placeholder {
  color: var(--text-muted);
}

.chat-btn-icon {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  font-size: 1.15rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-primary);
  transition: all var(--transition-fast);
}

[data-theme="light"] .chat-btn-icon {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #475569;
}

.chat-btn-icon:hover {
  background: var(--primary-500);
  color: #ffffff;
  border-color: var(--primary-500);
}

.chat-btn-send {
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  color: #ffffff;
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: var(--radius-md);
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  transition: opacity 0.2s, transform 0.15s;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
  font-family: var(--font-heading);
}

.chat-btn-send:hover {
  opacity: 0.92;
  transform: translateY(-1px);
}

.chat-lightbox-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(8px);
  z-index: 9999;
  align-items: center;
  justify-content: center;
}

.chat-lightbox-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.chat-lightbox-content img {
  max-width: 100%;
  max-height: 90vh;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}

.chat-lightbox-close {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: #ffffff;
  font-size: 1.75rem;
  cursor: pointer;
}

@media (max-width: 768px) {
  .chat-portal-layout {
    height: calc(100vh - 160px);
    position: relative;
  }
  .chat-sidebar-panel {
    width: 100%;
  }
  .chat-sidebar-panel.hidden-mobile {
    display: none;
  }
  .chat-main-window.hidden-mobile {
    display: none;
  }
  .chat-back-mobile-btn {
    display: inline-block;
  }
}
</style>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
let currentOrderId = <?= $initialOrderId ?>;
let conversationsData = [];
const pusherConfig = <?= json_encode(getPusherClientConfig(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let pusher = null;
let activeChannel = null;

const convListContainer = document.getElementById('convListContainer');
const convSearchInput = document.getElementById('convSearchInput');
const chatEmptyState = document.getElementById('chatEmptyState');
const chatActiveThread = document.getElementById('chatActiveThread');
const messagesContainer = document.getElementById('messagesContainer');
const chatClosedBanner = document.getElementById('chatClosedBanner');
const chatComposerBox = document.getElementById('chatComposerBox');

const threadTitle = document.getElementById('threadTitle');
const threadSubtitle = document.getElementById('threadSubtitle');
const threadAvatar = document.getElementById('threadAvatar');
const threadStatusBadge = document.getElementById('threadStatusBadge');
const threadOrderLink = document.getElementById('threadOrderLink');

const chatFileInput = document.getElementById('chatFileInput');
const btnTriggerFile = document.getElementById('btnTriggerFile');
const attachmentPreviewBox = document.getElementById('attachmentPreviewBox');
const attachNamePreview = document.getElementById('attachNamePreview');
const attachIconPreview = document.getElementById('attachIconPreview');
const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');
const chatTextInput = document.getElementById('chatTextInput');
const chatMessageForm = document.getElementById('chatMessageForm');

// 1. Fetch Conversations
async function loadConversations() {
  try {
    const res = await fetch('<?= BASE_URL ?>/api/chat_service.php?action=fetch_conversations');
    const data = await res.json();

    if (data.status === 'success') {
      conversationsData = data.conversations;
      renderConversationsList();

      if (currentOrderId > 0 && !chatActiveThread.dataset.loaded) {
        selectOrderChat(currentOrderId);
      }
    }
  } catch (err) {
    console.error('Error fetching conversations:', err);
  }
}

function renderConversationsList() {
  const query = (convSearchInput.value || '').toLowerCase().trim();
  const filtered = conversationsData.filter(c => {
    return c.order_number.toLowerCase().includes(query) || 
           c.other_name.toLowerCase().includes(query);
  });

  if (filtered.length === 0) {
    convListContainer.innerHTML = `
      <div style="padding:2.5rem 1rem; text-align:center; color:var(--text-muted); font-size:0.875rem;">
        <i class="fa-solid fa-inbox" style="font-size:1.75rem; margin-bottom:0.5rem; display:block; opacity:0.5;"></i>
        No customer pre-order chats found.
      </div>`;
    return;
  }

  let html = '';
  filtered.forEach(c => {
    const isActive = (c.order_id == currentOrderId);
    const unreadHtml = c.unread_count > 0 
      ? `<span style="background:var(--primary-500); color:#fff; font-size:0.7rem; font-weight:700; padding:2px 7px; border-radius:999px;">${c.unread_count}</span>` 
      : '';

    const statusPill = `<span class="farmer-status-badge badge-${c.order_status}" style="font-size:0.65rem; padding:0.15rem 0.55rem;">● ${c.order_status}</span>`;

    html += `
      <div class="chat-conv-item ${isActive ? 'active' : ''}" onclick="selectOrderChat(${c.order_id})">
        <div class="chat-avatar-circle">
          ${escapeHtml(c.other_name.charAt(0).toUpperCase())}
        </div>
        <div style="flex:1; overflow:hidden;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
            <strong style="font-size:0.9rem; color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
              ${escapeHtml(c.other_name)}
            </strong>
            <span style="font-size:0.7rem; color:var(--text-muted);">${c.last_time_human || c.formatted_date}</span>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
            <div style="font-size:0.75rem; color:var(--text-muted); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; max-width:160px;">
              ${c.last_message ? escapeHtml(c.last_message) : ('Order #' + c.order_number)}
            </div>
            <div style="display:flex; align-items:center; gap:0.35rem;">
              ${statusPill}
              ${unreadHtml}
            </div>
          </div>
        </div>
      </div>`;
  });

  convListContainer.innerHTML = html;
}

// 2. Select an Order Conversation
async function selectOrderChat(orderId) {
  currentOrderId = orderId;
  renderConversationsList();

  // Handle mobile layout
  const sidebar = document.querySelector('.chat-sidebar-panel');
  const mainWin = document.querySelector('.chat-main-window');
  if (window.innerWidth <= 768) {
    sidebar.classList.add('hidden-mobile');
    mainWin.classList.remove('hidden-mobile');
  }

  chatEmptyState.style.display = 'none';
  chatActiveThread.style.display = 'flex';

  await loadMessages(orderId);

  chatActiveThread.dataset.loaded = 'true';
  subscribeToOrderChannel(orderId);
}

function subscribeToOrderChannel(orderId) {
  if (!pusher) return;
  if (activeChannel) pusher.unsubscribe(activeChannel.name);
  activeChannel = pusher.subscribe(`private-order-${orderId}`);
  activeChannel.bind('new-message', (event) => {
    if (Number(event.order_id) === Number(currentOrderId)) loadMessages(currentOrderId, true);
    loadConversations();
  });
}

// 3. Load Messages for Order
async function loadMessages(orderId, isPolling = false) {
  try {
    const res = await fetch(`<?= BASE_URL ?>/api/chat_service.php?action=fetch_messages&order_id=${orderId}`);
    const data = await res.json();

    if (data.status === 'success') {
      const order = data.order;
      threadTitle.innerText = order.other_party_name;
      threadSubtitle.innerText = `#${order.order_number} • Pickup: ${order.pickup_date}`;
      threadAvatar.innerText = order.other_party_name.charAt(0).toUpperCase();

      threadStatusBadge.className = `farmer-status-badge badge-${order.order_status}`;
      threadStatusBadge.innerHTML = `● ${order.order_status.replace('_', ' ').toUpperCase()}`;
      threadOrderLink.href = `<?= BASE_URL ?>/farmer/orders.php?view_order=${order.order_id}`;

      if (order.is_closed) {
        chatClosedBanner.style.display = 'flex';
        chatComposerBox.style.display = 'none';
        attachmentPreviewBox.style.display = 'none';
      } else {
        chatClosedBanner.style.display = 'none';
        chatComposerBox.style.display = 'block';
      }

      renderMessagesStream(data.messages, isPolling);
    }
  } catch (err) {
    console.error('Error fetching messages:', err);
  }
}

function renderMessagesStream(messages, isPolling) {
  if (messages.length === 0) {
    messagesContainer.innerHTML = `
      <div style="text-align:center; padding:3rem 1rem; color:var(--slate-400); font-size:0.875rem;">
        <i class="fa-solid fa-seedling" style="font-size:1.75rem; color:var(--primary-500); margin-bottom:0.75rem; display:block;"></i>
        No messages in this customer pre-order chat yet. You can confirm harvest readiness or coordinate pickup preferences.
      </div>`;
    return;
  }

  let html = '';
  messages.forEach(m => {
    let attachmentHtml = '';
    if (m.attachment_path) {
      if (m.attachment_type === 'image') {
        attachmentHtml = `
          <img src="<?= BASE_URL ?>/${escapeHtml(m.attachment_path)}" 
               alt="Photo attachment" class="chat-attachment-img" 
               onclick="openLightbox('<?= BASE_URL ?>/${escapeHtml(m.attachment_path)}')">`;
      } else {
        attachmentHtml = `
          <a href="<?= BASE_URL ?>/${escapeHtml(m.attachment_path)}" target="_blank" download class="chat-doc-card">
            <i class="fa-solid fa-file-lines" style="font-size:1.5rem; color:var(--primary-400);"></i>
            <div>
              <div style="font-weight:700; font-size:0.8rem;">${escapeHtml(m.attachment_name || 'Document')}</div>
              <div style="font-size:0.6875rem; opacity:0.8;">${m.file_size_formatted} &bull; Click to Download</div>
            </div>
          </a>`;
      }
    }

    const textHtml = m.message_text ? `<div>${escapeHtml(m.message_text).replace(/\n/g, '<br>')}</div>` : '';

    const checks = m.is_mine 
      ? (m.is_read ? '<span title="Read" style="color:#86efac;"><i class="fa-solid fa-check-double"></i></span>' : '<span title="Sent"><i class="fa-solid fa-check"></i></span>') 
      : '';

    html += `
      <div class="chat-bubble-row ${m.is_mine ? 'mine' : 'theirs'}">
        <div class="chat-bubble">
          ${textHtml}
          ${attachmentHtml}
          <div class="chat-bubble-meta">
            <span>${m.time_formatted}</span>
            ${checks}
          </div>
        </div>
      </div>`;
  });

  const shouldScroll = !isPolling || (messagesContainer.scrollTop + messagesContainer.clientHeight >= messagesContainer.scrollHeight - 60);
  messagesContainer.innerHTML = html;

  if (shouldScroll) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }
}

// 4. Send Message (Text & Attachments)
if (chatMessageForm) {
  chatMessageForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentOrderId) return;

    const text = chatTextInput.value.trim();
    const hasFile = chatFileInput.files.length > 0;

    if (!text && !hasFile) return;

    const btnSend = document.getElementById('btnSendMessage');
    btnSend.disabled = true;
    btnSend.innerHTML = '<span>Sending...</span> <i class="fa-solid fa-spinner fa-spin"></i>';

    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('order_id', currentOrderId);
    fd.append('message_text', text);
    if (hasFile) {
      fd.append('attachment', chatFileInput.files[0]);
    }

    try {
      const res = await fetch('<?= BASE_URL ?>/api/chat_service.php', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();

      if (data.status === 'success') {
        chatTextInput.value = '';
        clearAttachment();
        await loadMessages(currentOrderId);
        loadConversations();
      } else {
        alert(data.message || 'Error sending message.');
      }
    } catch (err) {
      alert('Could not dispatch message to server.');
    } finally {
      btnSend.disabled = false;
      btnSend.innerHTML = '<span>Send</span> <i class="fa-solid fa-paper-plane" style="font-size:0.85rem;"></i>';
    }
  });
}

// Attachment triggers
if (btnTriggerFile && chatFileInput) {
  btnTriggerFile.addEventListener('click', () => chatFileInput.click());
  chatFileInput.addEventListener('change', () => {
    if (chatFileInput.files.length > 0) {
      const file = chatFileInput.files[0];
      attachNamePreview.innerText = file.name;
      const isImg = file.type.startsWith('image/');
      attachIconPreview.innerHTML = isImg ? '<i class="fa-solid fa-image"></i>' : '<i class="fa-solid fa-file"></i>';
      attachmentPreviewBox.style.display = 'flex';
    }
  });
}

if (btnRemoveAttachment) {
  btnRemoveAttachment.addEventListener('click', clearAttachment);
}

function clearAttachment() {
  if (chatFileInput) chatFileInput.value = '';
  if (attachmentPreviewBox) attachmentPreviewBox.style.display = 'none';
}

function openLightbox(src) {
  const modal = document.getElementById('imageLightboxModal');
  const img = document.getElementById('lightboxImg');
  if (modal && img) {
    img.src = src;
    modal.style.display = 'flex';
  }
}

// Search filter
if (convSearchInput) {
  convSearchInput.addEventListener('input', renderConversationsList);
}

// Mobile back button
const btnBackMobile = document.getElementById('btnBackToConvList');
if (btnBackMobile) {
  btnBackMobile.addEventListener('click', () => {
    document.querySelector('.chat-sidebar-panel').classList.remove('hidden-mobile');
    document.querySelector('.chat-main-window').classList.add('hidden-mobile');
  });
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

const btnRefreshChat = document.getElementById('btnRefreshChat');
if (btnRefreshChat) btnRefreshChat.addEventListener('click', async () => {
  const icon = document.getElementById('refreshChatIcon');
  if (icon) icon.classList.add('fa-spin');
  try {
    if (currentOrderId) await loadMessages(currentOrderId, true);
    await loadConversations();
  } finally {
    setTimeout(() => {
      if (icon) icon.classList.remove('fa-spin');
    }, 450);
  }
});

if (pusherConfig.enabled && window.Pusher) {
  pusher = new Pusher(pusherConfig.key, { cluster: pusherConfig.cluster,
    authEndpoint: '<?= BASE_URL ?>/api/chat_service.php?action=pusher_auth' });
}

// Initialize on page load
loadConversations();
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
