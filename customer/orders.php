<?php
/**
 * MarketLink - Customer Pre-Orders & Live Tracking Management
 * Fully implements SRS Section 1.6: View, Cancel, Reorder, and Status tracking.
 */

$pageTitle = 'My Orders & Tracking';
$activePage = 'orders';
require_once __DIR__ . '/includes/customer_header.php';

$filter = $_GET['status'] ?? 'all';

// Fetch orders for current customer
$where = ["o.customer_id = :uid"];
$params = [':uid' => $currentUserId];

if ($filter === 'active') {
    $where[] = "o.order_status IN ('placed', 'accepted', 'ready_for_pickup')";
} elseif ($filter === 'completed') {
    $where[] = "o.order_status = 'completed'";
} elseif ($filter === 'cancelled') {
    $where[] = "o.order_status IN ('cancelled', 'declined')";
}

$sql = "SELECT o.*, fp.stall_name, fp.contact_person, fp.business_phone, fp.order_cutoff_time,
               m.market_name, m.address as market_address,
               fms.stall_number_location,
               ps.start_time, ps.end_time,
               (SELECT COUNT(*) FROM farmer_reviews fr WHERE fr.order_id = o.order_id) as has_farmer_review
        FROM orders o
        JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
        JOIN markets m ON o.market_id = m.market_id
        LEFT JOIN farmer_market_stalls fms ON o.stall_id = fms.stall_id
        LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY o.order_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Fetch items for all listed orders
$orderIds = array_column($orders, 'order_id');
$itemsByOrder = [];

if (!empty($orderIds)) {
    $inClause = implode(',', array_map('intval', $orderIds));
    $itemSql = "SELECT oi.*, p.product_name, p.unit, p.image_url 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id IN ($inClause)";
    $items = $pdo->query($itemSql)->fetchAll();
    foreach ($items as $it) {
        $itemsByOrder[$it['order_id']][] = $it;
    }
}
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>📦</span> Pre-Orders & Pickup Tracking
    </h1>
    <p class="page-subheading">
      Monitor live harvest progress, manage pickup dates, and reorder previous farm purchases
    </p>
  </div>
</div>

<!-- Status Filter Tabs -->
<div class="filter-pills-row" style="margin-bottom:2rem;">
  <a href="<?= BASE_URL ?>/customer/orders.php" class="filter-pill <?= ($filter === 'all') ? 'active' : '' ?>">
    All Orders (<?= $totalOrdersCount ?>)
  </a>
  <a href="<?= BASE_URL ?>/customer/orders.php?status=active" class="filter-pill <?= ($filter === 'active') ? 'active' : '' ?>">
    🌱 Active Pre-Orders (<?= $activeOrdersCount ?>)
  </a>
  <a href="<?= BASE_URL ?>/customer/orders.php?status=completed" class="filter-pill <?= ($filter === 'completed') ? 'active' : '' ?>">
    ✔ Picked Up & Completed
  </a>
  <a href="<?= BASE_URL ?>/customer/orders.php?status=cancelled" class="filter-pill <?= ($filter === 'cancelled') ? 'active' : '' ?>">
    ✖ Cancelled / Declined
  </a>
</div>

<!-- Orders List -->
<?php if (!empty($orders)): ?>
  <div style="display:flex; flex-direction:column; gap:2rem;">
    <?php foreach ($orders as $ord): ?>
      <?php
        $st = $ord['order_status'];
        $steps = ['placed', 'accepted', 'ready_for_pickup', 'completed'];
        $currIndex = array_search($st, $steps);
        if ($currIndex === false) $currIndex = -1;
        $orderItems = $itemsByOrder[$ord['order_id']] ?? [];
      ?>
      <div class="portal-card" id="order-card-<?= $ord['order_id'] ?>">
        
        <!-- Order Card Top Bar -->
        <div class="portal-card-header" style="flex-wrap:wrap; gap:1rem;">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">
              PRE-ORDER ID & DATE
            </div>
            <div style="display:flex; align-items:center; gap:0.65rem; margin-top:0.2rem;">
              <span style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; color:var(--text-primary);">
                #<?= htmlspecialchars($ord['order_number']) ?>
              </span>
              <span style="font-size:0.8rem; color:var(--text-muted);">
                Placed on <?= date('M d, Y h:i A', strtotime($ord['created_at'])) ?>
              </span>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:0.85rem;">
            <span class="order-status-badge status-<?= htmlspecialchars($st) ?>">
              ● <?= ucwords(str_replace('_', ' ', $st)) ?>
            </span>
            <span style="font-family:var(--font-heading); font-size:1.25rem; font-weight:800; color:var(--emerald-400);">
              Rs. <?= number_format($ord['total_amount'], 2) ?>
            </span>
          </div>
        </div>

        <!-- 4-Step Stepper (if not cancelled/declined) -->
        <?php if ($currIndex >= 0): ?>
          <div class="order-stepper" style="margin:1.5rem 0 2rem;">
            <div class="order-step <?= ($currIndex >= 0) ? ($currIndex > 0 ? 'completed' : 'active') : '' ?>">
              <div class="order-step-bubble"><?= ($currIndex > 0) ? '✔' : '1' ?></div>
              <span class="order-step-title">Order Placed</span>
            </div>

            <div class="order-step <?= ($currIndex >= 1) ? ($currIndex > 1 ? 'completed' : 'active') : '' ?>">
              <div class="order-step-bubble"><?= ($currIndex > 1) ? '✔' : '2' ?></div>
              <span class="order-step-title">Farmer Confirmed</span>
            </div>

            <div class="order-step <?= ($currIndex >= 2) ? ($currIndex > 2 ? 'completed' : 'active') : '' ?>">
              <div class="order-step-bubble"><?= ($currIndex > 2) ? '✔' : '3' ?></div>
              <span class="order-step-title">Ready at Stall</span>
            </div>

            <div class="order-step <?= ($currIndex >= 3) ? 'completed' : '' ?>">
              <div class="order-step-bubble"><?= ($currIndex >= 3) ? '✔' : '4' ?></div>
              <span class="order-step-title">Picked Up</span>
            </div>
          </div>
        <?php else: ?>
          <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); border-radius:var(--radius-md); padding:0.85rem 1.25rem; margin-bottom:1.5rem; color:#ef4444; font-size:0.875rem;">
            <strong>Order Status: <?= ucfirst($st) ?></strong>
            <?php if (!empty($ord['cancellation_reason'])): ?>
              <div style="margin-top:0.25rem; font-size:0.8rem; color:var(--text-secondary);">
                Reason: <?= htmlspecialchars($ord['cancellation_reason']) ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Pickup & Stall Details Grid -->
        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1.5rem;">
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; font-size:0.85rem;">
            <div>
              <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Farmer Stall</span>
              <strong style="color:var(--text-primary); font-size:0.95rem;"><?= htmlspecialchars($ord['stall_name']) ?></strong>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">
                📞 <?= htmlspecialchars($ord['business_phone']) ?> (<?= htmlspecialchars($ord['contact_person']) ?>)
              </div>
            </div>

            <div>
              <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Market & Stall Location</span>
              <strong style="color:var(--text-primary);"><?= htmlspecialchars($ord['market_name']) ?></strong>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">
                📍 <?= htmlspecialchars($ord['stall_number_location'] ?? 'Stall') ?>
              </div>
            </div>

            <div>
              <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Pickup Reservation</span>
              <strong style="color:var(--emerald-400);"><?= date('l, M d, Y', strtotime($ord['pickup_date'])) ?></strong>
              <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.2rem;">
                ⏰ <?= !empty($ord['start_time']) ? date('h:i A', strtotime($ord['start_time'])).' - '.date('h:i A', strtotime($ord['end_time'])) : 'Market Hours' ?>
              </div>
            </div>

            <div>
              <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Payment Status</span>
              <strong style="color:var(--text-primary);">Pay At Stall (In Person)</strong>
              <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">
                Cash / Stall QR on pickup
              </div>
            </div>
          </div>
        </div>

        <!-- Itemized Table -->
        <table class="cart-items-table" style="margin-bottom:1.5rem;">
          <thead>
            <tr>
              <th>Reserved Harvest Item</th>
              <th>Unit Price</th>
              <th>Quantity</th>
              <th style="text-align:right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orderItems as $item): ?>
              <tr>
                <td>
                  <div class="cart-item-info">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_url']) ?>" alt="" class="cart-item-thumb">
                    <div>
                      <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);">
                        <?= htmlspecialchars($item['product_name']) ?>
                      </div>
                      <div style="font-size:0.75rem; color:var(--text-muted);">
                        Unit: <?= htmlspecialchars($item['unit']) ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td style="font-size:0.85rem; color:var(--text-secondary);">
                  Rs. <?= number_format($item['unit_price'], 2) ?>
                </td>
                <td style="font-weight:700; font-size:0.85rem; color:var(--text-primary);">
                  <?= $item['quantity'] ?>
                </td>
                <td style="text-align:right; font-weight:800; font-size:0.9rem; color:var(--text-primary);">
                  Rs. <?= number_format($item['subtotal'], 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Order Action Buttons -->
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border-subtle); padding-top:1rem; flex-wrap:wrap;">
          
          <!-- Quick 1-Click Reorder -->
          <button type="button" class="btn-secondary" onclick="quickReorder(<?= $ord['order_id'] ?>)" style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.15rem; font-size:0.85rem;">
            <span>🔄</span> Reorder These Items
          </button>

          <!-- Rate & Review Button for Completed Orders -->
          <?php if ($st === 'completed'): ?>
            <a href="<?= BASE_URL ?>/customer/reviews.php?order_id=<?= $ord['order_id'] ?>" 
               class="btn-primary" style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.15rem; font-size:0.85rem; text-decoration:none;">
              <span>⭐</span> <?= $ord['has_farmer_review'] ? 'View My Review' : 'Rate & Review Produce' ?>
            </a>
          <?php endif; ?>

          <!-- Cancel Order (Only if active & before cutoff) -->
          <?php if (in_array($st, ['placed', 'accepted'])): ?>
            <button type="button" onclick="openCancelModal(<?= $ord['order_id'] ?>, '<?= htmlspecialchars($ord['order_number']) ?>')" 
                    style="background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.3); padding:0.5rem 1.15rem; border-radius:var(--radius-lg); font-weight:700; font-size:0.85rem; cursor:pointer;">
              <span>✕</span> Cancel Pre-Order
            </button>
          <?php endif; ?>

        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="portal-card" style="text-align:center; padding:4rem 1.5rem;">
    <div style="font-size:4rem; margin-bottom:1rem;">📦</div>
    <h3 style="font-size:1.35rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">No Pre-Orders in this category</h3>
    <p style="color:var(--text-muted); font-size:0.95rem; max-width:480px; margin:0 auto 1.5rem;">
      You have no orders matching "<?= htmlspecialchars($filter) ?>". Browse local stalls to reserve your fresh market harvest.
    </p>
    <a href="<?= BASE_URL ?>/customer/products.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding:0.65rem 1.75rem;">
      🥬 Browse Fresh Harvest
    </a>
  </div>
<?php endif; ?>

<!-- Cancel Order Reason Modal -->
<div id="cancelOrderModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:300; align-items:center; justify-content:center;">
  <div class="portal-card" style="max-width:450px; width:90%; animation:chatbot-slide-up 0.3s ease;">
    <h3 style="font-family:var(--font-heading); font-size:1.25rem; font-weight:800; color:var(--text-primary); margin:0 0 0.5rem 0;">
      Cancel Pre-Order <span id="cancelOrderNum" style="color:var(--emerald-400);"></span>
    </h3>
    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.25rem;">
      Per SRS guidelines, you may cancel your order before the farmer's daily cutoff time. Please let the grower know why:
    </p>

    <form id="cancelOrderForm">
      <input type="hidden" name="order_id" id="cancelOrderId">
      <input type="hidden" name="action" value="cancel">

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:0.35rem;">
          Reason for Cancellation *
        </label>
        <select name="reason" id="cancelReasonSelect" class="topbar-search-input" style="border-radius:var(--radius-md); margin-bottom:0.65rem;" required>
          <option value="Schedule conflict on market day">Schedule conflict on market day</option>
          <option value="Selected wrong market pickup stall">Selected wrong market pickup stall</option>
          <option value="Placed order by mistake">Placed order by mistake</option>
          <option value="Other reason">Other reason</option>
        </select>
        <textarea name="reason_custom" id="cancelReasonCustom" class="topbar-search-input" style="border-radius:var(--radius-md); height:70px; resize:none; display:none;" placeholder="Please specify..."></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
        <button type="button" onclick="closeCancelModal()" class="btn-secondary" style="padding:0.5rem 1rem;">
          Keep Order
        </button>
        <button type="submit" id="btnConfirmCancel" style="background:#ef4444; color:#fff; border:none; padding:0.5rem 1.25rem; border-radius:var(--radius-lg); font-weight:700; cursor:pointer;">
          Confirm Cancellation
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCancelModal(orderId, orderNum) {
  document.getElementById('cancelOrderId').value = orderId;
  document.getElementById('cancelOrderNum').textContent = '#' + orderNum;
  const modal = document.getElementById('cancelOrderModal');
  modal.style.display = 'flex';
}

function closeCancelModal() {
  document.getElementById('cancelOrderModal').style.display = 'none';
}

document.getElementById('cancelReasonSelect')?.addEventListener('change', function() {
  const custom = document.getElementById('cancelReasonCustom');
  if (this.value === 'Other reason') {
    custom.style.display = 'block';
    custom.required = true;
  } else {
    custom.style.display = 'none';
    custom.required = false;
  }
});

document.getElementById('cancelOrderForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnConfirmCancel');
  btn.disabled = true;
  btn.textContent = 'Cancelling...';

  const formData = new FormData(this);
  if (document.getElementById('cancelReasonSelect').value === 'Other reason') {
    formData.set('reason', document.getElementById('cancelReasonCustom').value);
  }

  fetch('<?= BASE_URL ?>/customer/api/order.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showPortalToast(data.message, 'success');
        setTimeout(() => location.reload(), 600);
      } else {
        showPortalToast(data.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Confirm Cancellation';
      }
    })
    .catch(() => {
      showPortalToast('Failed to cancel order.', 'error');
      btn.disabled = false;
      btn.textContent = 'Confirm Cancellation';
    });
});

window.quickReorder = function(orderId) {
  const formData = new FormData();
  formData.append('action', 'reorder');
  formData.append('order_id', orderId);

  fetch('<?= BASE_URL ?>/customer/api/order.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showPortalToast(data.message, 'success');
        setTimeout(() => {
          window.location.href = data.redirect_url;
        }, 600);
      } else {
        showPortalToast(data.message, 'error');
      }
    })
    .catch(() => showPortalToast('Error performing reorder.', 'error'));
};
</script>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
