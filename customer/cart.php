<?php
/**
 * MarketLink - Customer Pre-Order Basket & Pickup Reservation
 * Fully satisfies SRS Section 1.6: Pre-orders against Farmer stock, pickup date/slot selection, pay-at-stall.
 */

$pageTitle = 'Pre-Order Basket';
$activePage = 'cart';
require_once __DIR__ . '/includes/customer_header.php';

// 1. Fetch current cart items
$stmt = $pdo->prepare("SELECT ci.cart_item_id, ci.product_id, ci.stall_id, ci.quantity, ci.added_at,
                              p.product_name, p.unit, p.image_url,
                              wi.price, wi.stock_quantity,
                              fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone,
                              m.market_id, m.market_name, m.address as market_address,
                              fms.stall_number_location
                       FROM cart_items ci
                       JOIN products p ON ci.product_id = p.product_id
                       JOIN (
                           SELECT product_id, stall_id, MAX(price) AS price, MAX(stock_quantity) AS stock_quantity
                           FROM weekly_inventory
                           GROUP BY product_id, stall_id
                       ) wi ON ci.product_id = wi.product_id AND ci.stall_id = wi.stall_id
                       JOIN farmer_market_stalls fms ON ci.stall_id = fms.stall_id
                       JOIN farmer_profiles fp ON fms.farmer_id = fp.farmer_id
                       JOIN markets m ON fms.market_id = m.market_id
                       WHERE ci.customer_id = :uid
                       GROUP BY ci.cart_item_id
                       ORDER BY ci.stall_id, ci.cart_item_id ASC");
$stmt->execute([':uid' => $currentUserId]);
$cartItems = $stmt->fetchAll();

// Group items by stall
$itemsByStall = [];
$firstStallId = 0;
$grandTotal = 0;

foreach ($cartItems as $it) {
    $itemsByStall[$it['stall_id']][] = $it;
    $grandTotal += ($it['quantity'] * $it['price']);
    if (!$firstStallId) $firstStallId = $it['stall_id'];
}

// 2. Fetch available pickup slots for every stall in the basket.
$pickupSlotsByStall = [];
if (!empty($itemsByStall)) {
    $slotStmt = $pdo->prepare("SELECT pickup_slot_id, slot_date, start_time, end_time
                               FROM pickup_slots
                               WHERE stall_id = :sid AND status = 'available' AND slot_date >= CURDATE()
                               ORDER BY slot_date ASC, start_time ASC");
    foreach (array_keys($itemsByStall) as $stallId) {
        $slotStmt->execute([':sid' => $stallId]);
        $pickupSlotsByStall[$stallId] = $slotStmt->fetchAll();
    }
}
?>

<!-- Page Heading -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>🛒</span> Pre-Order Basket
    </h1>
    <p class="page-subheading">
      Review your farm harvest items and select your market day pickup reservation
    </p>
  </div>
</div>

<?php if (!empty($cartItems)): ?>
  <div class="cart-layout-grid">

    <!-- Left Column: Basket Items by Stall -->
    <div>
      <?php foreach ($itemsByStall as $stallId => $items): ?>
        <?php $stallMeta = $items[0]; ?>
        <div class="portal-card" style="margin-bottom:1.5rem;">
          <div class="portal-card-header">
            <div>
              <span style="font-size:0.72rem; color:var(--emerald-400); font-weight:700; text-transform:uppercase;">Farmer Stall</span>
              <h3 class="portal-card-title">
                🏡 <?= htmlspecialchars($stallMeta['stall_name']) ?>
              </h3>
              <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.2rem;">
                📍 <?= htmlspecialchars($stallMeta['market_name']) ?> (<?= htmlspecialchars($stallMeta['stall_number_location'] ?? 'Stall') ?>)
              </div>
            </div>
            <a href="<?= BASE_URL ?>/customer/products.php?market_id=<?= $stallMeta['market_id'] ?>" class="portal-card-link" style="font-size:0.8rem;">
              + Add More Harvest
            </a>
          </div>

          <div class="cart-table-responsive">
          <table class="cart-items-table">
            <thead>
              <tr>
                <th>Produce</th>
                <th>Price / Unit</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th style="text-align:right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <?php $sub = $item['quantity'] * $item['price']; ?>
                <tr id="cart-row-<?= $item['cart_item_id'] ?>">
                  <td>
                    <div class="cart-item-info">
                      <img src="<?= htmlspecialchars(resolveImageUrl($item['image_url'])) ?>" alt="" class="cart-item-thumb" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
                      <div>
                        <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);">
                          <?= htmlspecialchars($item['product_name']) ?>
                        </div>
                        <div style="font-size:0.72rem; color:var(--emerald-400); font-weight:600;">
                          In Stock (<?= (int)$item['stock_quantity'] ?> <?= htmlspecialchars($item['unit']) ?> available)
                        </div>
                      </div>
                    </div>
                  </td>

                  <td style="font-weight:600; color:var(--text-secondary); font-size:0.85rem;">
                    Rs. <?= number_format($item['price'], 2) ?>
                  </td>

                  <td>
                    <div class="cart-qty-ctrl">
                      <button type="button" class="cart-qty-btn" onclick="updateCartItemQty(<?= $item['cart_item_id'] ?>, -1)">-</button>
                      <input type="text" id="cart-qty-<?= $item['cart_item_id'] ?>" value="<?= $item['quantity'] ?>" readonly class="cart-qty-num" style="width:30px; text-align:center; background:none; border:none; color:var(--text-primary);">
                      <button type="button" class="cart-qty-btn" onclick="updateCartItemQty(<?= $item['cart_item_id'] ?>, 1)">+</button>
                    </div>
                  </td>

                  <td style="font-weight:700; color:var(--text-primary); font-size:0.9rem;" id="cart-subtotal-<?= $item['cart_item_id'] ?>">
                    Rs. <?= number_format($sub, 2) ?>
                  </td>

                  <td style="text-align:right;">
                    <button type="button" onclick="removeCartItem(<?= $item['cart_item_id'] ?>)" 
                            style="background:none; border:none; color:#ef4444; font-size:1.1rem; cursor:pointer; padding:0.25rem;" 
                            title="Remove item" aria-label="Remove item">
                      🗑️
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Right Column: Pre-Order Pickup Slot & Checkout -->
    <div class="cart-summary-card">
      <h3 style="font-family:var(--font-heading); font-size:1.25rem; font-weight:800; color:var(--text-primary); margin:0 0 1rem 0;">
        Pickup Reservation
      </h3>

      <!-- SRS In-Person Payment Notice -->
      <div class="pickup-notice-box">
        <div style="font-weight:700; color:var(--emerald-400); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.35rem;">
          <span>ℹ️</span> Pay At Pickup Policy (Per SRS)
        </div>
        No online credit card fees! Your pre-order reserves fresh stock directly with the farmer. Payment is settled in cash or stall QR upon pickup at market.
      </div>

      <!-- Checkout Form -->
      <form id="preOrderCheckoutForm">
        <?php foreach ($itemsByStall as $stallId => $items): ?>
          <?php
            $stallMeta = $items[0];
            $stallSlots = $pickupSlotsByStall[$stallId] ?? [];
            $defaultPickupDate = !empty($stallSlots) ? $stallSlots[0]['slot_date'] : '';
          ?>
          <div class="pickup-stall-schedule" style="margin-bottom:1.5rem; padding:1rem; border:1px solid var(--border-color); border-radius:var(--radius-md);">
            <div style="font-size:0.85rem; font-weight:800; color:var(--text-primary); margin-bottom:1rem;">
              <?= htmlspecialchars($stallMeta['stall_name']) ?> pickup
            </div>
        <!-- Date Selector -->
        <div style="margin-bottom:1.25rem;">
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
            Select Pickup Date *
          </label>
          <input type="date" name="pickup_date[<?= (int)$stallId ?>]"
                 min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($defaultPickupDate) ?>"
                 required class="topbar-search-input checkout-pickup-date" data-stall-id="<?= (int)$stallId ?>" style="border-radius:var(--radius-md);">
        </div>

        <!-- Time Slot Selector -->
        <div style="margin-bottom:1.5rem;">
          <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.4rem;">
            Farmer Stall Pickup Window *
          </label>
          <select name="pickup_slot_id[<?= (int)$stallId ?>]" required class="topbar-search-input checkout-pickup-slot" data-stall-id="<?= (int)$stallId ?>" style="border-radius:var(--radius-md);">
            <?php foreach ($stallSlots as $ps): ?>
                <option value="<?= $ps['pickup_slot_id'] ?>" data-slot-date="<?= htmlspecialchars($ps['slot_date']) ?>">
                  <?= date('D, M d', strtotime($ps['slot_date'])) ?> — <?= date('h:i A', strtotime($ps['start_time'])) ?> to <?= date('h:i A', strtotime($ps['end_time'])) ?>
                </option>
            <?php endforeach; ?>
          </select>
          <p class="pickup-slot-date-hint" style="margin:0.45rem 0 0; font-size:0.78rem; color:var(--text-muted);" role="status"></p>
        </div>
          </div>
        <?php endforeach; ?>

        <!-- Totals Row -->
        <div style="border-top:1px solid var(--border-color); padding-top:1.25rem; margin-bottom:1.5rem;">
          <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.9rem; color:var(--text-secondary);">
            <span>Harvest Subtotal</span>
            <span id="cart-subtotal-display">Rs. <?= number_format($grandTotal, 2) ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.9rem; color:var(--text-secondary);">
            <span>Market Reservation Fee</span>
            <span style="color:var(--emerald-400); font-weight:700;">FREE</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:1.25rem; font-weight:800; color:var(--text-primary); border-top:1px dashed var(--border-color); padding-top:0.75rem;">
            <span>Total Payable At Stall</span>
            <span style="color:var(--emerald-400);" id="cart-grand-total">Rs. <?= number_format($grandTotal, 2) ?></span>
          </div>
        </div>

        <button type="submit" id="btnPlacePreOrder" class="btn-primary" style="width:100%; padding:0.85rem; font-size:1rem; font-weight:800; display:flex; align-items:center; justify-content:center; gap:0.5rem; cursor:pointer;">
          <span>🌿</span> Confirm & Place Pre-Order
        </button>
      </form>
    </div>

  </div>
<?php else: ?>
  <div class="portal-card" style="text-align:center; padding:4rem 1.5rem;">
    <div style="font-size:4rem; margin-bottom:1rem;">🧺</div>
    <h3 style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;">Your Pre-Order Basket is Empty</h3>
    <p style="color:var(--text-muted); font-size:0.95rem; max-width:480px; margin:0 auto 1.75rem;">
      You have not reserved any fresh produce yet. Browse our local farmer stalls to pick fresh organic vegetables, fruits, honey, and dairy.
    </p>
    <a href="<?= BASE_URL ?>/customer/products.php" class="btn-primary" style="display:inline-block; text-decoration:none; padding:0.75rem 2rem; font-size:1rem;">
      🥬 Explore Fresh Produce Now
    </a>
  </div>
<?php endif; ?>

<script>
function syncPickupWindowsToDate(dateInput) {
  const schedule = dateInput.closest('.pickup-stall-schedule');
  const slotSelect = schedule?.querySelector('.checkout-pickup-slot');
  const hint = schedule?.querySelector('.pickup-slot-date-hint');
  if (!slotSelect) return;

  const matchingOptions = Array.from(slotSelect.options).filter(option => {
    const matches = option.dataset.slotDate === dateInput.value;
    option.hidden = !matches;
    option.disabled = !matches;
    return matches;
  });

  if (matchingOptions.length) {
    slotSelect.disabled = false;
    if (slotSelect.selectedOptions[0]?.dataset.slotDate !== dateInput.value) {
      slotSelect.value = matchingOptions[0].value;
    }
    slotSelect.setCustomValidity('');
    if (hint) hint.textContent = matchingOptions.length + ' pickup window' + (matchingOptions.length === 1 ? '' : 's') + ' available for this date.';
  } else {
    slotSelect.value = '';
    slotSelect.disabled = true;
    slotSelect.setCustomValidity('Please select a date with an available pickup window.');
    if (hint) hint.textContent = 'No pickup window is available for this date. Please choose another date.';
  }
}

document.querySelectorAll('.checkout-pickup-date').forEach(dateInput => {
  dateInput.addEventListener('change', () => syncPickupWindowsToDate(dateInput));
  syncPickupWindowsToDate(dateInput);
});

document.getElementById('preOrderCheckoutForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const hasInvalidSchedule = Array.from(document.querySelectorAll('.checkout-pickup-date')).some(dateInput => {
    const slotSelect = dateInput.closest('.pickup-stall-schedule')?.querySelector('.checkout-pickup-slot');
    if (!dateInput.value || slotSelect?.selectedOptions[0]?.dataset.slotDate !== dateInput.value) {
      syncPickupWindowsToDate(dateInput);
      return true;
    }
    return false;
  });
  if (hasInvalidSchedule) {
    showPortalToast('Please select a pickup window for the same pickup date.', 'error');
    return;
  }
  const btn = document.getElementById('btnPlacePreOrder');
  btn.disabled = true;
  btn.innerHTML = '<span>⏳</span> Processing Pre-Order...';

  const formData = new FormData(this);
  formData.append('action', 'checkout');

  fetch('<?= BASE_URL ?>/customer/api/cart.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showPortalToast(data.message || 'Pre-order placed successfully! 🌿', 'success');
        setTimeout(() => {
          window.location.href = data.redirect_url || '<?= BASE_URL ?>/customer/orders.php';
        }, 800);
      } else {
        showPortalToast(data.message || 'Could not place pre-order.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span>🌿</span> Confirm & Place Pre-Order';
      }
    })
    .catch(() => {
      showPortalToast('Server error during pre-order checkout.', 'error');
      btn.disabled = false;
      btn.innerHTML = '<span>🌿</span> Confirm & Place Pre-Order';
    });
});
</script>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
