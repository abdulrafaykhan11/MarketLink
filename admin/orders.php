<?php
/**
 * MarketLink - Admin Platform Pre-Orders Central
 * SRS Section 1.6: Platform-wide pre-order tracking and fulfillment pipeline oversight.
 */

$pageTitle = 'Pre-Orders Central';
$activeNav = 'orders';
require_once __DIR__ . '/includes/admin_header.php';

$filterStatus = trim($_GET['status'] ?? 'all');
$searchQuery  = trim($_GET['search'] ?? '');

$whereClauses = [];
$params = [];

if (!empty($filterStatus) && $filterStatus !== 'all') {
    $whereClauses[] = "o.order_status = :status";
    $params[':status'] = $filterStatus;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(o.order_number LIKE :q_order OR u.username LIKE :q_customer OR fp.stall_name LIKE :q_stall OR m.market_name LIKE :q_market)";
    $searchPattern = "%{$searchQuery}%";
    $params[':q_order'] = $searchPattern;
    $params[':q_customer'] = $searchPattern;
    $params[':q_stall'] = $searchPattern;
    $params[':q_market'] = $searchPattern;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT o.*, 
               u.username as customer_name, u.email as customer_email, u.phone_number as customer_phone,
               fp.stall_name, fp.contact_person as farmer_name,
               m.market_name,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
        JOIN markets m ON o.market_id = m.market_id
        {$whereSql}
        ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Order Counts by status
$statusCounts = [];
$scStmt = $pdo->query("SELECT order_status, COUNT(*) as c FROM orders GROUP BY order_status")->fetchAll();
foreach ($scStmt as $sc) {
    $statusCounts[$sc['order_status']] = (int)$sc['c'];
}
$allOrdersCount = array_sum($statusCounts);
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="shopping-bag" style="color:var(--admin-accent);"></i>
      Platform Pre-Orders Central
    </h1>
    <p>Monitor customer reservation pipeline, track physical market fulfillment, and inspect transactions.</p>
  </div>
</div>

<div class="admin-card">
  <!-- Toolbar -->
  <div class="admin-toolbar">
    <div class="admin-filter-tabs">
      <a href="?status=all<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'all') ? 'active' : '' ?>">
        All Orders (<?= $allOrdersCount ?>)
      </a>
      <a href="?status=placed<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'placed') ? 'active' : '' ?>">
        Placed (<?= $statusCounts['placed'] ?? 0 ?>)
      </a>
      <a href="?status=accepted<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'accepted') ? 'active' : '' ?>">
        Accepted (<?= $statusCounts['accepted'] ?? 0 ?>)
      </a>
      <a href="?status=ready_for_pickup<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'ready_for_pickup') ? 'active' : '' ?>">
        Ready (<?= $statusCounts['ready_for_pickup'] ?? 0 ?>)
      </a>
      <a href="?status=completed<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'completed') ? 'active' : '' ?>">
        Completed (<?= $statusCounts['completed'] ?? 0 ?>)
      </a>
      <a href="?status=cancelled<?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" 
         class="admin-filter-tab <?= ($filterStatus === 'cancelled') ? 'active' : '' ?>">
        Cancelled (<?= $statusCounts['cancelled'] ?? 0 ?>)
      </a>
    </div>

    <form method="GET" class="admin-search-wrapper">
      <?php if ($filterStatus !== 'all'): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <?php endif; ?>
      <span class="admin-search-icon"><i data-lucide="search" style="width:16px; height:16px;"></i></span>
      <input type="text" name="search" class="admin-search-input" placeholder="Search order #, customer, stall, market..." value="<?= htmlspecialchars($searchQuery) ?>">
    </form>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order # &amp; Date</th>
          <th>Customer Details</th>
          <th>Producer Stall &amp; Hub</th>
          <th>Items</th>
          <th>Total Value</th>
          <th>Pickup Date</th>
          <th>Fulfillment Status</th>
          <th style="text-align:right;">Details</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="8" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No pre-orders found matching your criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>
                <div style="font-weight:800; font-family:monospace; font-size:0.95rem; color:var(--admin-accent);">
                  <?= htmlspecialchars($o['order_number']) ?>
                </div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  <?= date('M d, Y H:i', strtotime($o['created_at'])) ?>
                </div>
              </td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($o['customer_name']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-muted);"><?= htmlspecialchars($o['customer_email']) ?></div>
              </td>
              <td>
                <div style="font-weight:600; font-size:0.85rem; color:var(--admin-emerald);"><?= htmlspecialchars($o['stall_name']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($o['market_name']) ?></div>
              </td>
              <td>
                <span style="font-weight:700; font-size:0.85rem;">
                  📦 <?= $o['items_count'] ?> <?= $o['items_count'] == 1 ? 'item' : 'items' ?>
                </span>
              </td>
              <td>
                <div style="font-family:var(--font-heading); font-weight:800; font-size:1.05rem; color:var(--admin-emerald);">
                  Rs. <?= number_format($o['total_amount'], 2) ?>
                </div>
                <div style="font-size:0.7rem; color:var(--admin-text-subtle);">Paid at pickup</div>
              </td>
              <td style="font-size:0.85rem; font-weight:600; color:var(--admin-text-main);">
                📅 <?= date('M d, Y', strtotime($o['pickup_date'])) ?>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($o['order_status']) ?>">
                  <?= ucwords(str_replace('_', ' ', $o['order_status'])) ?>
                </span>
                <?php if (!empty($o['cancellation_reason'])): ?>
                  <div style="font-size:0.7rem; color:var(--admin-rose); margin-top:2px;">
                    Reason: <?= htmlspecialchars($o['cancellation_reason']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="text-align:right;">
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                        onclick="viewOrderItems(<?= $o['order_id'] ?>, '<?= htmlspecialchars(addslashes($o['order_number'])) ?>')">
                  <i data-lucide="eye"></i> View Items
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: View Order Items & Messages Details -->
<div class="admin-modal-backdrop" id="orderItemsModalBackdrop">
  <div class="admin-modal-card" style="max-width:720px; width:92%;">
    <div class="admin-modal-header" style="border-bottom:1px solid var(--admin-border, rgba(255,255,255,0.08));">
      <div>
        <h3 id="orderModalTitle" style="margin:0 0 4px 0;">Pre-Order Details</h3>
        <div id="orderModalMeta" style="font-size:0.78rem; color:var(--admin-text-subtle);"></div>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeOrderModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <!-- Modal Tabs Navigation -->
    <div style="display:flex; gap:0.5rem; padding:0.75rem 1.5rem 0; border-bottom:1px solid var(--admin-border, rgba(255,255,255,0.08)); background:var(--admin-surface, #1e293b);">
      <button type="button" id="tabBtnItems" class="admin-btn-tab active" onclick="switchOrderModalTab('items')" style="background:none; border:none; border-bottom:2px solid var(--admin-accent); padding:0.6rem 1rem; color:var(--admin-accent); font-weight:700; font-size:0.875rem; cursor:pointer;">
        <span>📦 Produce Items (<span id="tabCountItems">0</span>)</span>
      </button>
      <button type="button" id="tabBtnMessages" class="admin-btn-tab" onclick="switchOrderModalTab('messages')" style="background:none; border:none; border-bottom:2px solid transparent; padding:0.6rem 1rem; color:var(--admin-text-subtle); font-weight:700; font-size:0.875rem; cursor:pointer;">
        <span>💬 Order Messages (<span id="tabCountMessages">0</span>)</span>
      </button>
    </div>

    <div class="admin-modal-body" style="padding:1.25rem 1.5rem; max-height:60vh; overflow-y:auto;">
      <!-- Tab 1: Produce Items -->
      <div id="orderTabItemsContent">
        <div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Loading items...</div>
      </div>

      <!-- Tab 2: Order Messages Audit -->
      <div id="orderTabMessagesContent" style="display:none;">
        <div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Loading messages...</div>
      </div>
    </div>

    <div class="admin-modal-footer" style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--admin-border, rgba(255,255,255,0.08));">
      <div id="modalOrderSummaryFooter" style="font-weight:700; font-size:0.95rem; color:var(--admin-emerald);"></div>
      <button type="button" class="admin-btn admin-btn-secondary" onclick="closeOrderModal()">Close</button>
    </div>
  </div>
</div>

<script>
let currentLoadedOrderData = null;

async function viewOrderItems(orderId, orderNum) {
  document.getElementById('orderModalTitle').textContent = `Order Details: ${orderNum}`;
  document.getElementById('orderModalMeta').textContent = 'Loading live pre-order and conversation audit...';
  document.getElementById('orderTabItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Fetching items from database...</div>';
  document.getElementById('orderTabMessagesContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Fetching messages...</div>';
  document.getElementById('orderItemsModalBackdrop').classList.add('open');
  switchOrderModalTab('items');

  try {
    const res = await fetch(`<?= BASE_URL ?>/admin/api/order_details.php?order_id=${orderId}`);
    const data = await res.json();
    currentLoadedOrderData = data;

    if (data.success && data.order) {
      const ord = data.order;
      document.getElementById('orderModalMeta').textContent = `Customer: ${ord.customer_name} (${ord.customer_phone || ord.customer_email}) • Stall: ${ord.stall_name} (${ord.market_name})`;
      document.getElementById('tabCountItems').textContent = data.items ? data.items.length : 0;
      document.getElementById('tabCountMessages').textContent = data.messages ? data.messages.length : 0;
      document.getElementById('modalOrderSummaryFooter').textContent = `Grand Total: Rs. ${parseFloat(ord.total_amount).toFixed(2)} (Paid on Pickup)`;

      // Render Items
      if (data.items && data.items.length > 0) {
        let html = `
          <table class="admin-table">
            <thead>
              <tr>
                <th>Produce Item</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th style="text-align:right;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
        `;
        data.items.forEach(it => {
          html += `
            <tr>
              <td>
                <div style="font-weight:700; color:var(--admin-text-main);">${it.product_name}</div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">${it.unit || ''}</div>
              </td>
              <td>Rs. ${parseFloat(it.unit_price).toFixed(2)}</td>
              <td><strong style="color:var(--admin-accent);">${it.quantity}</strong></td>
              <td style="text-align:right; font-weight:700; color:var(--admin-emerald);">Rs. ${parseFloat(it.subtotal).toFixed(2)}</td>
            </tr>
          `;
        });
        html += `
            </tbody>
          </table>
        `;
        document.getElementById('orderTabItemsContent').innerHTML = html;
      } else {
        document.getElementById('orderTabItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">No items recorded.</div>';
      }

      // Render Messages Audit
      if (data.messages && data.messages.length > 0) {
        let msgHtml = '<div style="display:flex; flex-direction:column; gap:0.75rem; padding:0.5rem 0;">';
        data.messages.forEach(m => {
          const isFarmer = (m.sender_role === 'farmer');
          const bubbleBg = isFarmer ? 'rgba(16, 185, 129, 0.14)' : 'rgba(56, 189, 248, 0.12)';
          const bubbleBorder = isFarmer ? 'rgba(16, 185, 129, 0.3)' : 'rgba(56, 189, 248, 0.25)';
          const senderBadge = isFarmer ? '<span style="background:#10b981; color:#fff; font-size:0.65rem; padding:1px 6px; border-radius:10px; font-weight:700; margin-left:6px;">PRODUCER</span>' : '<span style="background:#0284c7; color:#fff; font-size:0.65rem; padding:1px 6px; border-radius:10px; font-weight:700; margin-left:6px;">CUSTOMER</span>';

          let attachHtml = '';
          if (m.attachment_path) {
            if (m.attachment_type === 'image') {
              attachHtml = `<div style="margin-top:6px;"><img src="<?= BASE_URL ?>/${m.attachment_path}" style="max-width:240px; max-height:160px; border-radius:8px; border:1px solid rgba(255,255,255,0.15);" alt="Attachment"></div>`;
            } else {
              attachHtml = `<div style="margin-top:6px;"><a href="<?= BASE_URL ?>/${m.attachment_path}" target="_blank" download style="color:var(--admin-accent); font-size:0.8rem; text-decoration:none;">📄 ${m.attachment_name || 'Download Document'}</a></div>`;
            }
          }

          msgHtml += `
            <div style="background:${bubbleBg}; border:1px solid ${bubbleBorder}; border-radius:12px; padding:0.75rem 1rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <span style="font-weight:700; font-size:0.85rem; color:var(--admin-text-main);">
                  ${m.sender_name} ${senderBadge}
                </span>
                <span style="font-size:0.7rem; color:var(--admin-text-subtle);">
                  ${m.created_at}
                </span>
              </div>
              <div style="font-size:0.875rem; color:var(--admin-text-main); line-height:1.5;">
                ${m.message_text ? m.message_text.replace(/\n/g, '<br>') : ''}
              </div>
              ${attachHtml}
            </div>
          `;
        });
        msgHtml += '</div>';
        document.getElementById('orderTabMessagesContent').innerHTML = msgHtml;
      } else {
        document.getElementById('orderTabMessagesContent').innerHTML = `
          <div style="text-align:center; padding:3rem 1rem; color:var(--admin-text-subtle);">
            <div style="font-size:2.5rem; margin-bottom:0.5rem;">💬</div>
            <h4 style="margin:0 0 4px 0; color:var(--admin-text-main);">No Order Messages Yet</h4>
            <p style="font-size:0.85rem; margin:0;">Neither the farmer nor the customer has sent a direct message for this order.</p>
          </div>
        `;
      }
    } else {
      document.getElementById('orderTabItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-rose);">Unable to load order details.</div>';
    }
  } catch (err) {
    document.getElementById('orderTabItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-rose);">Network error loading order items.</div>';
  }
}

function switchOrderModalTab(tab) {
  const itemsContent = document.getElementById('orderTabItemsContent');
  const msgsContent = document.getElementById('orderTabMessagesContent');
  const btnItems = document.getElementById('tabBtnItems');
  const btnMsgs = document.getElementById('tabBtnMessages');

  if (tab === 'items') {
    itemsContent.style.display = 'block';
    msgsContent.style.display = 'none';
    btnItems.style.borderBottomColor = 'var(--admin-accent)';
    btnItems.style.color = 'var(--admin-accent)';
    btnMsgs.style.borderBottomColor = 'transparent';
    btnMsgs.style.color = 'var(--admin-text-subtle)';
  } else {
    itemsContent.style.display = 'none';
    msgsContent.style.display = 'block';
    btnMsgs.style.borderBottomColor = 'var(--admin-accent)';
    btnMsgs.style.color = 'var(--admin-accent)';
    btnItems.style.borderBottomColor = 'transparent';
    btnItems.style.color = 'var(--admin-text-subtle)';
  }
}

function closeOrderModal() {
  document.getElementById('orderItemsModalBackdrop').classList.remove('open');
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
