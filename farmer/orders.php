<?php
/**
 * MarketLink - Farmer Pre-Order Fulfillment Manager
 * Aligned with SRS specifications and DB schema
 */

$pageTitle = 'Pre-Order Fulfillment';
$activePage = 'orders';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Parse Status and Search Filters
$statusFilter = trim($_GET['status'] ?? 'all');
$marketFilter = (int)($_GET['market_id'] ?? 0);
$dateFilter = trim($_GET['date_filter'] ?? 'all');
$searchTerm = trim($_GET['search'] ?? '');
$viewOrderId = (int)($_GET['view_order'] ?? 0);

// Status counts for tabs
$countsQuery = $pdo->prepare("SELECT order_status, COUNT(*) as cnt 
                             FROM orders WHERE farmer_id = :fid GROUP BY order_status");
$countsQuery->execute([':fid' => $farmerId]);
$rawCounts = $countsQuery->fetchAll(PDO::FETCH_KEY_PAIR);

$totalOrdersCount = array_sum($rawCounts);
$placedCount = $rawCounts['placed'] ?? 0;
$acceptedCount = $rawCounts['accepted'] ?? 0;
$readyCount = $rawCounts['ready_for_pickup'] ?? 0;
$completedCount = $rawCounts['completed'] ?? 0;
$cancelledCount = ($rawCounts['cancelled'] ?? 0) + ($rawCounts['declined'] ?? 0);

// Build dynamic WHERE clause
$params = [':fid' => $farmerId];
$whereConditions = ["o.farmer_id = :fid"];

if ($statusFilter === 'placed') {
    $whereConditions[] = "o.order_status = 'placed'";
} elseif ($statusFilter === 'accepted') {
    $whereConditions[] = "o.order_status = 'accepted'";
} elseif ($statusFilter === 'ready') {
    $whereConditions[] = "o.order_status = 'ready_for_pickup'";
} elseif ($statusFilter === 'completed') {
    $whereConditions[] = "o.order_status = 'completed'";
} elseif ($statusFilter === 'cancelled') {
    $whereConditions[] = "o.order_status IN ('cancelled', 'declined')";
}

if ($marketFilter > 0) {
    $whereConditions[] = "o.market_id = :mid";
    $params[':mid'] = $marketFilter;
}

if ($dateFilter === 'today') {
    $whereConditions[] = "DATE(o.pickup_date) = CURDATE()";
} elseif ($dateFilter === 'tomorrow') {
    $whereConditions[] = "DATE(o.pickup_date) = CURDATE() + INTERVAL 1 DAY";
} elseif ($dateFilter === 'upcoming') {
    $whereConditions[] = "DATE(o.pickup_date) >= CURDATE()";
}

if (!empty($searchTerm)) {
    $whereConditions[] = "(o.order_number LIKE :term OR cp.full_name LIKE :term OR u.phone_number LIKE :term)";
    $params[':term'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $whereConditions);

// Fetch orders list
$orderListStmt = $pdo->prepare("SELECT o.*, cp.full_name as customer_name, u.phone_number as customer_phone, u.email as customer_email,
                                       m.market_name, ps.start_time, ps.end_time,
                                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
                                FROM orders o
                                JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                                JOIN users u ON cp.customer_id = u.user_id
                                JOIN markets m ON o.market_id = m.market_id
                                LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                                WHERE {$whereSql}
                                ORDER BY o.pickup_date ASC, o.order_id DESC");
$orderListStmt->execute($params);
$orders = $orderListStmt->fetchAll();

// If view_order parameter passed, fetch detailed itemization
$selectedOrder = null;
$selectedOrderItems = [];
if ($viewOrderId > 0) {
    $sStmt = $pdo->prepare("SELECT o.*, cp.full_name as customer_name, u.phone_number as customer_phone, u.email as customer_email,
                                   m.market_name, m.address as market_address, ps.start_time, ps.end_time,
                                   fms.stall_number_location
                            FROM orders o
                            JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                            JOIN users u ON cp.customer_id = u.user_id
                            JOIN markets m ON o.market_id = m.market_id
                            LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                            LEFT JOIN farmer_market_stalls fms ON o.stall_id = fms.stall_id
                            WHERE o.order_id = :oid AND o.farmer_id = :fid LIMIT 1");
    $sStmt->execute([':oid' => $viewOrderId, ':fid' => $farmerId]);
    $selectedOrder = $sStmt->fetch();

    if ($selectedOrder) {
        $itemsStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.unit, p.image_url, pc.category_name 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.product_id 
                                    JOIN product_categories pc ON p.category_id = pc.category_id 
                                    WHERE oi.order_id = :oid");
        $itemsStmt->execute([':oid' => $viewOrderId]);
        $selectedOrderItems = $itemsStmt->fetchAll();
    }
}
?>

<div class="farmer-content">

  <!-- Header & Breadcrumb -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Pre-Orders &amp; Pickup Fulfillment
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        Review incoming reservation requests, organize market day pickup packing, and confirm customer collections.
      </p>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <button type="button" class="farmer-btn-secondary" onclick="window.print()" title="Print market day order packing slip">
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
        <span>Print Packing Slips</span>
      </button>
      <a href="<?= BASE_URL ?>/farmer/pickup-slots.php" class="farmer-btn-primary">
        <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
        <span>Pickup Slots</span>
      </a>
    </div>
  </div>

  <!-- Status Tabs Navigation Bar -->
  <div style="display: flex; align-items: center; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
    <a href="?status=all<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'all') ? 'active' : '' ?>">
      <span>All Orders (<?= $totalOrdersCount ?>)</span>
    </a>
    <a href="?status=placed<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'placed') ? 'active' : '' ?>" style="<?= $placedCount > 0 ? 'border-color: #f59e0b; color: #fbbf24;' : '' ?>">
      <span>Incoming Requests (<?= $placedCount ?>)</span>
      <?php if ($placedCount > 0): ?>
        <span class="pulse-dot-amber" style="margin-left: 0.25rem;"></span>
      <?php endif; ?>
    </a>
    <a href="?status=accepted<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'accepted') ? 'active' : '' ?>">
      <span>In Preparation (<?= $acceptedCount ?>)</span>
    </a>
    <a href="?status=ready<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'ready') ? 'active' : '' ?>">
      <span>Ready for Pickup (<?= $readyCount ?>)</span>
    </a>
    <a href="?status=completed<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'completed') ? 'active' : '' ?>">
      <span>Completed / Collected (<?= $completedCount ?>)</span>
    </a>
    <a href="?status=cancelled<?= $marketFilter ? '&market_id='.$marketFilter : '' ?>" class="farmer-btn-pill <?= ($statusFilter === 'cancelled') ? 'active' : '' ?>">
      <span>Cancelled / Declined (<?= $cancelledCount ?>)</span>
    </a>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="farmer-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form method="GET" action="" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">

      <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap; flex: 1;">
        <!-- Search Input -->
        <div style="position: relative; min-width: 260px; flex: 1;">
          <input type="text" name="search" class="farmer-form-input" placeholder="Search order #, customer name, phone..." value="<?= htmlspecialchars($searchTerm) ?>" style="padding-left: 2.25rem;">
          <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--slate-400);">
            <i data-lucide="search" style="width: 15px; height: 15px;"></i>
          </span>
        </div>

        <!-- Market Selector Filter -->
        <div style="min-width: 200px;">
          <select name="market_id" class="farmer-form-select" onchange="this.form.submit()">
            <option value="0">All Markets &amp; Stalls</option>
            <?php foreach ($assignedStalls as $st): ?>
              <option value="<?= $st['market_id'] ?>" <?= ($marketFilter === (int)$st['market_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($st['market_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date Window Filter -->
        <div style="min-width: 160px;">
          <select name="date_filter" class="farmer-form-select" onchange="this.form.submit()">
            <option value="all" <?= ($dateFilter === 'all') ? 'selected' : '' ?>>All Pickup Dates</option>
            <option value="today" <?= ($dateFilter === 'today') ? 'selected' : '' ?>>Today's Pickups</option>
            <option value="tomorrow" <?= ($dateFilter === 'tomorrow') ? 'selected' : '' ?>>Tomorrow's Pickups</option>
            <option value="upcoming" <?= ($dateFilter === 'upcoming') ? 'selected' : '' ?>>All Upcoming</option>
          </select>
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <button type="submit" class="farmer-btn-primary" style="padding: 0.65rem 1rem;">
          <i data-lucide="filter" style="width: 15px; height: 15px;"></i>
          <span>Apply Filter</span>
        </button>
        <?php if (!empty($searchTerm) || $marketFilter > 0 || $dateFilter !== 'all'): ?>
          <a href="?status=<?= htmlspecialchars($statusFilter) ?>" class="farmer-btn-secondary" style="padding: 0.65rem 0.85rem;" title="Clear Filters">
            ✕
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Orders Listing Table -->
  <div class="farmer-card">
    <?php if (empty($orders)): ?>
      <div style="text-align: center; padding: 4rem 1.5rem; color: var(--slate-400);">
        <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 0.75rem; color: var(--slate-500);"></i>
        <h4 style="color: var(--text-primary); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.35rem;">No Pre-Orders Matching Criteria</h4>
        <p style="font-size: 0.875rem; max-width: 450px; margin: 0 auto;">
          Try changing your status tab or clearing filters to view other pre-orders.
        </p>
      </div>
    <?php else: ?>
      <div class="farmer-table-responsive">
        <table class="farmer-table">
          <thead>
            <tr>
              <th>Order Number</th>
              <th>Customer</th>
              <th>Market Pickup Date &amp; Slot</th>
              <th>Market Bay</th>
              <th>Harvest Items</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th style="text-align: right;">Fulfillment Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $ord): ?>
              <tr>
                <!-- Order Number -->
                <td>
                  <strong style="font-family: var(--font-heading); color: var(--text-primary); font-size: 0.9375rem;">
                    #<?= htmlspecialchars($ord['order_number']) ?>
                  </strong>
                  <div style="font-size: 0.75rem; color: var(--slate-400);">
                    Booked <?= date('M j, Y • g:i A', strtotime($ord['created_at'])) ?>
                  </div>
                </td>

                <!-- Customer Details -->
                <td>
                  <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">
                    <?= htmlspecialchars($ord['customer_name']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.15rem;">
                    📞 <?= htmlspecialchars($ord['customer_phone'] ?: 'No phone provided') ?>
                  </div>
                </td>

                <!-- Pickup Date & Slot -->
                <td>
                  <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: var(--primary-500);"></i>
                    <span><?= date('D, M j, Y', strtotime($ord['pickup_date'])) ?></span>
                  </div>
                  <?php if (!empty($ord['start_time'])): ?>
                    <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.15rem;">
                      🕒 <?= date('g:i A', strtotime($ord['start_time'])) ?> - <?= date('g:i A', strtotime($ord['end_time'])) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Market Name -->
                <td>
                  <div style="font-size: 0.84375rem; color: var(--text-primary); font-weight: 500;">
                    <?= htmlspecialchars($ord['market_name']) ?>
                  </div>
                </td>

                <!-- Items Count -->
                <td>
                  <span style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); padding: 0.25rem 0.6rem; border-radius: var(--radius-md); font-weight: 600; font-size: 0.78125rem;">
                    <?= $ord['items_count'] ?> item<?= $ord['items_count'] !== 1 ? 's' : '' ?>
                  </span>
                </td>

                <!-- Total Amount -->
                <td>
                  <strong style="color: var(--primary-400, #4ade80); font-size: 1rem;">
                    Rs. <?= number_format($ord['total_amount'], 2) ?>
                  </strong>
                  <div style="font-size: 0.6875rem; color: var(--slate-400);">Pay on pickup</div>
                </td>

                <!-- Status Badge -->
                <td>
                  <?php
                    $statusClass = 'badge-' . $ord['order_status'];
                    $displayStatus = ucfirst(str_replace('_', ' ', $ord['order_status']));
                  ?>
                  <span class="farmer-status-badge <?= $statusClass ?>">
                    <?php if ($ord['order_status'] === 'placed'): ?>
                      <span class="pulse-dot-amber"></span>
                    <?php elseif ($ord['order_status'] === 'accepted'): ?>
                      <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                    <?php elseif ($ord['order_status'] === 'ready_for_pickup'): ?>
                      <i data-lucide="package" style="width: 12px; height: 12px;"></i>
                    <?php elseif ($ord['order_status'] === 'completed'): ?>
                      <i data-lucide="check-check" style="width: 12px; height: 12px;"></i>
                    <?php elseif ($ord['order_status'] === 'declined' || $ord['order_status'] === 'cancelled'): ?>
                      <i data-lucide="x" style="width: 12px; height: 12px;"></i>
                    <?php endif; ?>
                    <?= $displayStatus ?>
                  </span>
                </td>

                <!-- Actions -->
                <td style="text-align: right;">
                  <div class="farmer-action-group" style="justify-content: flex-end;">
                    <?php if ($ord['order_status'] === 'placed'): ?>
                      <button type="button" class="farmer-btn-action btn-action-accept" onclick="FarmerApp.updateOrderStatus(<?= $ord['order_id'] ?>, 'accepted')" title="Accept pre-order">
                        <i data-lucide="check" style="width: 13px; height: 13px;"></i>
                        <span>Accept</span>
                      </button>
                      <button type="button" class="farmer-btn-action btn-action-decline" onclick="openDeclineModal(<?= $ord['order_id'] ?>, '<?= htmlspecialchars(addslashes($ord['order_number'])) ?>')" title="Decline pre-order">
                        <i data-lucide="x" style="width: 13px; height: 13px;"></i>
                        <span>Decline</span>
                      </button>
                    <?php elseif ($ord['order_status'] === 'accepted'): ?>
                      <button type="button" class="farmer-btn-action btn-action-ready" onclick="FarmerApp.updateOrderStatus(<?= $ord['order_id'] ?>, 'ready_for_pickup')" title="Mark produce sorted and ready at stall">
                        <i data-lucide="package" style="width: 13px; height: 13px;"></i>
                        <span>Mark Ready</span>
                      </button>
                    <?php elseif ($ord['order_status'] === 'ready_for_pickup'): ?>
                      <button type="button" class="farmer-btn-action btn-action-complete" onclick="FarmerApp.updateOrderStatus(<?= $ord['order_id'] ?>, 'completed')" title="Customer paid and collected harvest">
                        <i data-lucide="check-check" style="width: 13px; height: 13px;"></i>
                        <span>Fulfill &amp; Paid</span>
                      </button>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>/farmer/chat.php?order_id=<?= $ord['order_id'] ?>" class="farmer-btn-action" style="background:rgba(16,185,129,0.12); color:var(--emerald-600); border:1px solid rgba(16,185,129,0.3);" title="Chat with Customer">
                      <i data-lucide="message-square" style="width: 14px; height: 14px;"></i>
                      <span>Chat</span>
                    </a>

                    <a href="?view_order=<?= $ord['order_id'] ?><?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?>" class="farmer-btn-action btn-action-view" title="Inspect items &amp; print slip">
                      <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                      <span>Details</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Modal / Drawer: Order Details & Packing Slip -->
<?php if ($selectedOrder): ?>
  <div class="farmer-modal-backdrop active" id="orderDetailsModal">
    <div class="farmer-modal" style="max-width: 680px;">
      <div class="farmer-modal-header">
        <h4 class="farmer-modal-title">
          <i data-lucide="clipboard-list" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
          <span>Pre-Order Packing Slip #<?= htmlspecialchars($selectedOrder['order_number']) ?></span>
        </h4>
        <button type="button" class="farmer-modal-close-btn" onclick="window.location.href='<?= BASE_URL ?>/farmer/orders.php?status=<?= htmlspecialchars($statusFilter) ?>'">✕</button>
      </div>

      <div class="farmer-modal-body" id="printSection">
        <!-- Pickup Summary Header -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.5rem;">
          <div>
            <span style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Customer Name</span>
            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;"><?= htmlspecialchars($selectedOrder['customer_name']) ?></div>
            <div style="font-size: 0.8125rem; color: var(--slate-400); margin-top: 0.15rem;">
              📞 <?= htmlspecialchars($selectedOrder['customer_phone'] ?: 'N/A') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Market Location</span>
            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;"><?= htmlspecialchars($selectedOrder['market_name']) ?></div>
            <div style="font-size: 0.8125rem; color: var(--slate-400); margin-top: 0.15rem;">
              📍 <?= htmlspecialchars($selectedOrder['stall_number_location'] ?: 'Stall Bay') ?>
            </div>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Pickup Slot</span>
            <div style="font-weight: 700; color: var(--primary-400); font-size: 0.9375rem;">
              <?= date('D, M j, Y', strtotime($selectedOrder['pickup_date'])) ?>
            </div>
            <?php if (!empty($selectedOrder['start_time'])): ?>
              <div style="font-size: 0.8125rem; color: var(--slate-400); margin-top: 0.15rem;">
                🕒 <?= date('g:i A', strtotime($selectedOrder['start_time'])) ?> - <?= date('g:i A', strtotime($selectedOrder['end_time'])) ?>
              </div>
            <?php endif; ?>
          </div>
          <div>
            <span style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Fulfillment Status</span>
            <div>
              <span class="farmer-status-badge badge-<?= $selectedOrder['order_status'] ?>" style="margin-top: 0.25rem;">
                <?= ucfirst(str_replace('_', ' ', $selectedOrder['order_status'])) ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Itemized Produce Items Table -->
        <h5 style="font-family: var(--font-heading); font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
          Itemized Harvest Breakdown
        </h5>
        <div class="farmer-table-responsive" style="margin-bottom: 1.5rem;">
          <table class="farmer-table">
            <thead>
              <tr>
                <th>Produce Item</th>
                <th>Category</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Line Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($selectedOrderItems as $item): ?>
                <tr>
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                      <div style="width: 38px; height: 38px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface); flex-shrink: 0;">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_url'] ?: 'assets/images/cat-vegetables.svg') ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                      </div>
                      <strong style="color: var(--text-primary); font-size: 0.875rem;">
                        <?= htmlspecialchars($item['product_name']) ?>
                      </strong>
                    </div>
                  </td>
                  <td>
                    <span style="font-size: 0.75rem; color: var(--slate-400);">
                      <?= htmlspecialchars($item['category_name']) ?>
                    </span>
                  </td>
                  <td style="text-align: right; font-size: 0.875rem;">
                    Rs. <?= number_format($item['unit_price'], 2) ?> / <?= htmlspecialchars($item['unit']) ?>
                  </td>
                  <td style="text-align: center; font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">
                    <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?>
                  </td>
                  <td style="text-align: right; font-weight: 700; color: var(--primary-400); font-size: 0.9375rem;">
                    Rs. <?= number_format($item['subtotal'], 2) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background: var(--bg-surface-elevated);">
                <td colspan="4" style="text-align: right; font-weight: 700; color: var(--text-primary); font-size: 1rem;">
                  Total Amount Due at Stall Pickup:
                </td>
                <td style="text-align: right; font-weight: 800; color: var(--primary-400); font-size: 1.15rem;">
                  Rs. <?= number_format($selectedOrder['total_amount'], 2) ?>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <?php if (!empty($selectedOrder['cancellation_reason'])): ?>
          <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 0.85rem; font-size: 0.8125rem; color: #f87171;">
            <strong>Cancellation / Decline Reason:</strong> <?= htmlspecialchars($selectedOrder['cancellation_reason']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="farmer-modal-footer">
        <button type="button" class="farmer-btn-secondary" onclick="window.print()">
          <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
          <span>Print Slip</span>
        </button>

        <?php if ($selectedOrder['order_status'] === 'placed'): ?>
          <button type="button" class="farmer-btn-primary" onclick="FarmerApp.updateOrderStatus(<?= $selectedOrder['order_id'] ?>, 'accepted')">
            <i data-lucide="check" style="width: 15px; height: 15px;"></i>
            <span>Accept Order</span>
          </button>
        <?php elseif ($selectedOrder['order_status'] === 'accepted'): ?>
          <button type="button" class="farmer-btn-primary" onclick="FarmerApp.updateOrderStatus(<?= $selectedOrder['order_id'] ?>, 'ready_for_pickup')">
            <i data-lucide="package" style="width: 15px; height: 15px;"></i>
            <span>Mark Ready for Pickup</span>
          </button>
        <?php elseif ($selectedOrder['order_status'] === 'ready_for_pickup'): ?>
          <button type="button" class="farmer-btn-primary" onclick="FarmerApp.updateOrderStatus(<?= $selectedOrder['order_id'] ?>, 'completed')">
            <i data-lucide="check-check" style="width: 15px; height: 15px;"></i>
            <span>Fulfill &amp; Mark Paid</span>
          </button>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/farmer/orders.php?status=<?= htmlspecialchars($statusFilter) ?>" class="farmer-btn-secondary">
          Close
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Decline Order Modal -->
<div class="farmer-modal-backdrop" id="declineModal">
  <div class="farmer-modal" style="max-width: 480px;">
    <div class="farmer-modal-header">
      <h4 class="farmer-modal-title" style="color: #f87171;">
        <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
        <span>Decline Pre-Order</span>
      </h4>
      <button type="button" class="farmer-modal-close-btn" onclick="closeDeclineModal()">✕</button>
    </div>
    <div class="farmer-modal-body">
      <input type="hidden" id="declineOrderId">
      <p style="font-size: 0.875rem; color: var(--slate-300); margin-bottom: 1rem;">
        Are you sure you want to decline Order <strong id="declineOrderNum" style="color: var(--text-primary);"></strong>? The customer will receive an immediate in-app cancellation notification.
      </p>
      <div class="farmer-form-group">
        <label class="farmer-form-label" for="declineReason">Reason for Decline</label>
        <select class="farmer-form-select" id="declineReason">
          <option value="Harvest yield unavailable / out of stock">Harvest yield unavailable / out of stock</option>
          <option value="Stall closed on selected market day">Stall closed on selected market day</option>
          <option value="Order arrived after harvesting cut-off time">Order arrived after harvesting cut-off time</option>
          <option value="Produce reserved for physical market stall">Produce reserved for physical market stall</option>
        </select>
      </div>
    </div>
    <div class="farmer-modal-footer">
      <button type="button" class="farmer-btn-secondary" onclick="closeDeclineModal()">Keep Order</button>
      <button type="button" class="farmer-btn-action btn-action-decline" onclick="confirmDeclineOrder()" style="padding: 0.65rem 1.25rem;">
        Confirm Decline
      </button>
    </div>
  </div>
</div>

<script>
function openDeclineModal(orderId, orderNum) {
  document.getElementById('declineOrderId').value = orderId;
  document.getElementById('declineOrderNum').textContent = '#' + orderNum;
  document.getElementById('declineModal').classList.add('active');
}

function closeDeclineModal() {
  document.getElementById('declineModal').classList.remove('active');
}

function confirmDeclineOrder() {
  const orderId = document.getElementById('declineOrderId').value;
  const reason = document.getElementById('declineReason').value;
  FarmerApp.updateOrderStatus(orderId, 'declined', reason);
  closeDeclineModal();
}
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
