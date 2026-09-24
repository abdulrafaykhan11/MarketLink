<?php
/**
 * MarketLink - Farmer Pickup Slots & Cutoff Time Scheduling
 * SRS Specification: Farmers can set order cut-off times and manage available pickup slots
 */

$pageTitle = 'Pickup Slots & Cut-Off Controls';
$activePage = 'pickup-slots';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Fetch Farmer Stalls
$stallsQuery = $pdo->prepare("SELECT fms.*, m.market_name, m.city 
                              FROM farmer_market_stalls fms 
                              JOIN markets m ON fms.market_id = m.market_id 
                              WHERE fms.farmer_id = :fid");
$stallsQuery->execute([':fid' => $farmerId]);
$stalls = $stallsQuery->fetchAll();

$stallIds = array_column($stalls, 'stall_id');

// 2. Fetch Pickup Slots for these stalls
$slots = [];
if (!empty($stallIds)) {
    $inClause = implode(',', array_map('intval', $stallIds));
    $slotsQuery = $pdo->query("SELECT ps.*, fms.stall_number_location, m.market_name,
                                      (SELECT COUNT(*) FROM orders o WHERE o.pickup_slot_id = ps.pickup_slot_id AND o.order_status NOT IN ('cancelled', 'declined')) as booked_count
                               FROM pickup_slots ps
                               JOIN farmer_market_stalls fms ON ps.stall_id = fms.stall_id
                               JOIN markets m ON fms.market_id = m.market_id
                               WHERE ps.stall_id IN ({$inClause}) AND ps.slot_date >= CURDATE() - INTERVAL 1 DAY
                               ORDER BY ps.slot_date ASC, ps.start_time ASC LIMIT 40");
    $slots = $slotsQuery->fetchAll();
}

// Slot Stats
$totalSlots = count($slots);
$activeAvailable = count(array_filter($slots, fn($s) => $s['status'] === 'available'));
$totalBooked = array_sum(array_column($slots, 'booked_count'));
?>

<div class="farmer-content">

  <!-- Page Header -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Pickup Slots &amp; Cut-Off Controls
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        Schedule collection windows at physical market stalls and configure daily order harvesting cutoff thresholds.
      </p>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <button type="button" class="farmer-btn-primary" onclick="openAddSlotModal()">
        <i data-lucide="plus-circle" style="width: 17px; height: 17px;"></i>
        <span>Add Pickup Window</span>
      </button>
    </div>
  </div>

  <!-- Dual Cards: Cutoff Configuration & Capacity Stats -->
  <div class="farmer-grid-2-col" style="margin-bottom: 2rem;">
    <!-- Order Cut-Off Time Card -->
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="clock" style="width: 20px; height: 20px; color: #fbbf24;"></i>
            <span>Daily Order Cut-Off Threshold</span>
          </h3>
          <span class="farmer-card-subtitle">Enforce harvesting deadlines to avoid last-minute rush</span>
        </div>
      </div>

      <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-lg); padding: 1.15rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.85rem;">
        <i data-lucide="info" style="width: 20px; height: 20px; color: #f59e0b; flex-shrink: 0; margin-top: 2px;"></i>
        <div style="font-size: 0.8125rem; color: var(--slate-300); line-height: 1.5;">
          Customers cannot reserve produce for the next market day past this time. This ensures you have adequate time to harvest, wash, inspect, and bundle produce before market morning.
        </div>
      </div>

      <form id="cutoffForm" onsubmit="submitCutoffTime(event)" style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
        <div class="farmer-form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
          <label class="farmer-form-label" for="cutoffInput">Harvest Cut-Off Time (24h format)</label>
          <input type="time" class="farmer-form-input" id="cutoffInput" name="order_cutoff_time" value="<?= htmlspecialchars(substr($cutoffTime, 0, 5)) ?>" required style="font-weight: 700; font-size: 1.05rem;">
        </div>
        <button type="submit" class="farmer-btn-primary" id="cutoffSubmitBtn" style="padding: 0.72rem 1.25rem;">
          <i data-lucide="save" style="width: 16px; height: 16px;"></i>
          <span>Save Cutoff Time</span>
        </button>
      </form>
    </div>

    <!-- Quick Stats -->
    <div class="farmer-card" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div class="farmer-card-header" style="margin-bottom: 1rem;">
          <div class="farmer-card-title-group">
            <h3 class="farmer-card-title">
              <i data-lucide="gauge" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
              <span>Slot Capacity Utilization</span>
            </h3>
            <span class="farmer-card-subtitle">Current collection capacity across market bays</span>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1rem;">
            <div style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Upcoming Slots</div>
            <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin: 0.25rem 0;">
              <?= $totalSlots ?>
            </div>
            <div style="font-size: 0.75rem; color: var(--primary-400);"><?= $activeAvailable ?> slots open</div>
          </div>

          <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1rem;">
            <div style="font-size: 0.75rem; color: var(--slate-400); text-transform: uppercase;">Booked Reservations</div>
            <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--sky-400); margin: 0.25rem 0;">
              <?= $totalBooked ?>
            </div>
            <div style="font-size: 0.75rem; color: var(--slate-400);">Customer pickups scheduled</div>
          </div>
        </div>
      </div>

      <div style="padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.78125rem; color: var(--slate-400);">
        💡 Each slot limits simultaneous traffic at your stall to prevent lines and maintain premium shopper satisfaction.
      </div>
    </div>
  </div>

  <!-- Pickup Slots Schedule Table -->
  <div class="farmer-card">
    <div class="farmer-card-header">
      <div class="farmer-card-title-group">
        <h3 class="farmer-card-title">
          <i data-lucide="calendar-days" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
          <span>Upcoming Market Pickup Windows</span>
        </h3>
        <span class="farmer-card-subtitle">Manage availability and order capacity thresholds for upcoming market dates</span>
      </div>
      <button type="button" class="farmer-btn-pill" onclick="openAddSlotModal()">
        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
        <span>Add Slot</span>
      </button>
    </div>

    <?php if (empty($slots)): ?>
      <div style="text-align: center; padding: 4rem 1.5rem; color: var(--slate-400);">
        <i data-lucide="calendar-x" style="width: 48px; height: 48px; margin-bottom: 0.75rem; color: var(--slate-500);"></i>
        <h4 style="color: var(--text-primary); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.35rem;">No Pickup Slots Found</h4>
        <p style="font-size: 0.875rem; max-width: 450px; margin: 0 auto 1.5rem;">
          You need active pickup slots so shoppers can select a collection time window when placing a pre-order.
        </p>
        <button type="button" class="farmer-btn-primary" onclick="openAddSlotModal()">
          <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
          <span>Create Your First Pickup Slot</span>
        </button>
      </div>
    <?php else: ?>
      <div class="farmer-table-responsive">
        <table class="farmer-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Market Stall Bay</th>
              <th>Time Window</th>
              <th>Booked / Max Capacity</th>
              <th>Capacity Utilization</th>
              <th>Status</th>
              <th style="text-align: right;">Slot Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($slots as $slot): ?>
              <?php
                $pct = ($slot['max_orders'] > 0) ? min(100, round(($slot['booked_count'] / $slot['max_orders']) * 100)) : 0;
              ?>
              <tr>
                <!-- Slot Date -->
                <td>
                  <strong style="color: var(--text-primary); font-size: 0.9375rem;">
                    <?= date('D, M j, Y', strtotime($slot['slot_date'])) ?>
                  </strong>
                  <?php if ($slot['slot_date'] === date('Y-m-d')): ?>
                    <span class="farmer-badge-role" style="font-size: 0.65rem; margin-left: 0.4rem;">Today</span>
                  <?php endif; ?>
                </td>

                <!-- Market Name & Bay -->
                <td>
                  <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">
                    <?= htmlspecialchars($slot['market_name']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--slate-400);">
                    <?= htmlspecialchars($slot['stall_number_location'] ?: 'Main Bay') ?>
                  </div>
                </td>

                <!-- Time Window -->
                <td>
                  <div style="font-weight: 700; color: var(--primary-400); font-size: 0.875rem; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="clock" style="width: 13px; height: 13px;"></i>
                    <span><?= date('g:i A', strtotime($slot['start_time'])) ?> - <?= date('g:i A', strtotime($slot['end_time'])) ?></span>
                  </div>
                </td>

                <!-- Capacity Numbers -->
                <td>
                  <span style="font-weight: 700; color: var(--text-primary); font-size: 0.9375rem;">
                    <?= $slot['booked_count'] ?> / <?= $slot['max_orders'] ?>
                  </span>
                  <span style="font-size: 0.75rem; color: var(--slate-400);">orders</span>
                </td>

                <!-- Capacity Progress Bar -->
                <td style="min-width: 140px;">
                  <div style="width: 100%; height: 8px; background: var(--bg-surface-elevated); border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-color);">
                    <div style="width: <?= $pct ?>%; height: 100%; background: <?= $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : 'var(--primary-500)') ?>; border-radius: var(--radius-full); transition: width 0.3s ease;"></div>
                  </div>
                  <div style="font-size: 0.6875rem; color: var(--slate-400); margin-top: 0.25rem;">
                    <?= $pct ?>% full
                  </div>
                </td>

                <!-- Status Pill -->
                <td>
                  <?php if ($slot['status'] === 'available'): ?>
                    <span class="farmer-status-badge badge-ready">Open</span>
                  <?php elseif ($slot['status'] === 'full'): ?>
                    <span class="farmer-status-badge badge-placed">Full</span>
                  <?php else: ?>
                    <span class="farmer-status-badge badge-completed">Closed</span>
                  <?php endif; ?>
                </td>

                <!-- Quick Actions -->
                <td style="text-align: right;">
                  <div class="farmer-action-group" style="justify-content: flex-end;">
                    <?php if ($slot['status'] === 'available'): ?>
                      <button type="button" class="farmer-btn-action btn-action-decline" onclick="toggleSlotStatus(<?= $slot['pickup_slot_id'] ?>, 'closed')" title="Close this slot to new orders">
                        <i data-lucide="lock" style="width: 13px; height: 13px;"></i>
                        <span>Close</span>
                      </button>
                    <?php else: ?>
                      <button type="button" class="farmer-btn-action btn-action-accept" onclick="toggleSlotStatus(<?= $slot['pickup_slot_id'] ?>, 'available')" title="Re-open this slot">
                        <i data-lucide="unlock" style="width: 13px; height: 13px;"></i>
                        <span>Re-open</span>
                      </button>
                    <?php endif; ?>
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

<!-- Modal: Add New Pickup Slot -->
<div class="farmer-modal-backdrop" id="addSlotModal">
  <div class="farmer-modal" style="max-width: 520px;">
    <div class="farmer-modal-header">
      <h4 class="farmer-modal-title">
        <i data-lucide="calendar-plus" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
        <span>Create Pickup Slot Window</span>
      </h4>
      <button type="button" class="farmer-modal-close-btn" onclick="closeAddSlotModal()">✕</button>
    </div>
    <form id="addSlotForm" onsubmit="submitNewSlot(event)">
      <div class="farmer-modal-body">
        <input type="hidden" name="action" value="create">

        <!-- Stall Selection -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="slotStallId">Select Market Stall Bay <span style="color:#ef4444;">*</span></label>
          <select class="farmer-form-select" id="slotStallId" name="stall_id" required>
            <?php foreach ($stalls as $st): ?>
              <option value="<?= $st['stall_id'] ?>">
                <?= htmlspecialchars($st['market_name']) ?> (<?= htmlspecialchars($st['stall_number_location'] ?: 'Main Bay') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date Selection -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="slotDate">Pickup Date <span style="color:#ef4444;">*</span></label>
          <input type="date" class="farmer-form-input" id="slotDate" name="slot_date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
        </div>

        <!-- Time Range -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="farmer-form-group">
            <label class="farmer-form-label" for="slotStartTime">Start Time <span style="color:#ef4444;">*</span></label>
            <input type="time" class="farmer-form-input" id="slotStartTime" name="start_time" value="08:00" required>
          </div>
          <div class="farmer-form-group">
            <label class="farmer-form-label" for="slotEndTime">End Time <span style="color:#ef4444;">*</span></label>
            <input type="time" class="farmer-form-input" id="slotEndTime" name="end_time" value="10:30" required>
          </div>
        </div>

        <!-- Max Orders Capacity -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="slotMaxOrders">Max Orders Capacity <span style="color:#ef4444;">*</span></label>
          <input type="number" min="1" max="100" class="farmer-form-input" id="slotMaxOrders" name="max_orders" value="15" required>
          <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.25rem;">
            Slot will automatically mark as "Full" once this number of pre-orders is reached.
          </div>
        </div>
      </div>

      <div class="farmer-modal-footer">
        <button type="button" class="farmer-btn-secondary" onclick="closeAddSlotModal()">Cancel</button>
        <button type="submit" class="farmer-btn-primary" id="saveSlotBtn">
          <span>Publish Pickup Window</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddSlotModal() {
  document.getElementById('addSlotModal').classList.add('active');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAddSlotModal() {
  document.getElementById('addSlotModal').classList.remove('active');
}

// Submit Cutoff Time Form
function submitCutoffTime(e) {
  e.preventDefault();
  const btn = document.getElementById('cutoffSubmitBtn');
  btn.disabled = true;

  const formData = new FormData(document.getElementById('cutoffForm'));

  fetch('<?= BASE_URL ?>/farmer/api/save_cutoff.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Cutoff Saved', data.message);
      setTimeout(() => location.reload(), 700);
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Could not update cut-off time.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Server Error', 'Please check server connection.');
  });
}

// Submit New Pickup Slot
function submitNewSlot(e) {
  e.preventDefault();
  const btn = document.getElementById('saveSlotBtn');
  btn.disabled = true;

  const formData = new FormData(document.getElementById('addSlotForm'));

  fetch('<?= BASE_URL ?>/farmer/api/save_slot.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Slot Created', data.message);
      closeAddSlotModal();
      setTimeout(() => location.reload(), 700);
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Could not create pickup slot.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Network Error', 'Please try again.');
  });
}

// Toggle Slot Status (Open / Close)
function toggleSlotStatus(slotId, status) {
  const formData = new FormData();
  formData.append('action', 'toggle_status');
  formData.append('slot_id', slotId);
  formData.append('status', status);

  fetch('<?= BASE_URL ?>/farmer/api/save_slot.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Slot Updated', data.message);
      setTimeout(() => location.reload(), 600);
    } else {
      FarmerApp.showToast('error', 'Error', data.message);
    }
  })
  .catch(err => {
    FarmerApp.showToast('error', 'Network Error', 'Could not update slot.');
  });
}
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
