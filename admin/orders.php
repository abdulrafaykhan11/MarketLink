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
                  $<?= number_format($o['total_amount'], 2) ?>
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

<!-- Modal: View Order Items Details -->
<div class="admin-modal-backdrop" id="orderItemsModalBackdrop">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <h3 id="orderModalTitle">Pre-Order Details</h3>
      <button type="button" class="admin-modal-close" onclick="closeOrderModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="admin-modal-body" id="orderItemsContent">
      <div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Loading items...</div>
    </div>
    <div class="admin-modal-footer">
      <button type="button" class="admin-btn admin-btn-secondary" onclick="closeOrderModal()">Close</button>
    </div>
  </div>
</div>

<script>
async function viewOrderItems(orderId, orderNum) {
  document.getElementById('orderModalTitle').textContent = `Order Items: ${orderNum}`;
  document.getElementById('orderItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-text-subtle);">Fetching items from database...</div>';
  document.getElementById('orderItemsModalBackdrop').classList.add('open');

  try {
    const res = await fetch(`<?= BASE_URL ?>/admin/api/order_details.php?order_id=${orderId}`);
    const data = await res.json();
    if (data.success && data.items) {
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
              <div style="font-weight:700;">${it.product_name}</div>
              <div style="font-size:0.75rem; color:var(--admin-text-subtle);">${it.unit || ''}</div>
            </td>
            <td>$${parseFloat(it.unit_price).toFixed(2)}</td>
            <td><strong style="color:var(--admin-accent);">${it.quantity}</strong></td>
            <td style="text-align:right; font-weight:700; color:var(--admin-emerald);">$${parseFloat(it.subtotal).toFixed(2)}</td>
          </tr>
        `;
      });
      html += `
          </tbody>
        </table>
        <div style="text-align:right; margin-top:1.25rem; font-size:1.15rem; font-weight:800; color:var(--admin-text-main);">
          Order Grand Total: <span style="color:var(--admin-emerald);">$${parseFloat(data.order.total_amount).toFixed(2)}</span>
        </div>
      `;
      document.getElementById('orderItemsContent').innerHTML = html;
    } else {
      document.getElementById('orderItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-rose);">Unable to load order line items.</div>';
    }
  } catch (err) {
    document.getElementById('orderItemsContent').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--admin-rose);">Network error loading order items.</div>';
  }
}

function closeOrderModal() {
  document.getElementById('orderItemsModalBackdrop').classList.remove('open');
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
