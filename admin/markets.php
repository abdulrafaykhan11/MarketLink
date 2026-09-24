<?php
/**
 * MarketLink - Admin Farmers Markets Management & Interactive Geo Hubs
 * SRS Section 1.6: Admin can add, edit, or remove farmers markets, including name, address, 
 * operating days, timings, and map coordinates or embedded map links using OpenStreetMap / Leaflet.
 */

$pageTitle = 'Farmers Markets';
$activeNav = 'markets';
require_once __DIR__ . '/includes/admin_header.php';

// Fetch all markets with stalls count
$sql = "SELECT m.*, 
               COUNT(fms.stall_id) as assigned_stalls_count
        FROM markets m
        LEFT JOIN farmer_market_stalls fms ON m.market_id = fms.market_id
        GROUP BY m.market_id
        ORDER BY m.status ASC, m.market_name ASC";
$markets = $pdo->query($sql)->fetchAll();
?>

<div class="admin-page-header">
  <div class="admin-page-title-group">
    <h1>
      <i data-lucide="map-pin" style="color:var(--admin-accent);"></i>
      Farmers Markets &amp; Physical Pickup Hubs
    </h1>
    <p>Establish, geo-locate, and manage community farmers markets and physical pickup locations.</p>
  </div>

  <div class="admin-header-actions">
    <button type="button" class="admin-btn admin-btn-primary" onclick="openMarketModal()">
      <i data-lucide="plus-circle"></i>
      <span>Establish New Market</span>
    </button>
  </div>
</div>

<!-- Interactive Leaflet Map Showcase -->
<div class="admin-card" style="margin-bottom:2rem;">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="map" style="color:var(--admin-cyan);"></i>
        Geographic Distribution &amp; Pickup Hubs
      </h3>
      <p class="admin-card-subtitle">Live OpenStreetMap rendering of active community farmers market locations</p>
    </div>
  </div>
  <div style="height:380px; width:100%; border-radius:0 0 var(--radius-xl) var(--radius-xl); overflow:hidden;" id="adminMarketsMap"></div>
</div>

<!-- Markets Directory Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="building" style="color:var(--admin-emerald);"></i>
        Established Markets (<?= count($markets) ?>)
      </h3>
      <p class="admin-card-subtitle">All physical locations where producer stalls congregate</p>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Market Hub</th>
          <th>Location Address &amp; City</th>
          <th>Coordinates</th>
          <th>Operating Schedule</th>
          <th>Stalls</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($markets)): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:3rem; color:var(--admin-text-subtle);">
              No markets configured in database yet. Click "Establish New Market" above.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($markets as $m): ?>
            <tr>
              <td>
                <div style="font-weight:700; font-size:0.95rem; color:var(--admin-text-main);">
                  <?= htmlspecialchars($m['market_name']) ?>
                </div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  ID #<?= $m['market_id'] ?> &bull; Added <?= date('M d, Y', strtotime($m['created_at'])) ?>
                </div>
              </td>
              <td>
                <div style="font-size:0.85rem; color:var(--admin-text-main);"><?= htmlspecialchars($m['address']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);">
                  <?= htmlspecialchars($m['city']) ?>, <?= htmlspecialchars($m['state']) ?> <?= htmlspecialchars($m['postal_code']) ?>
                </div>
              </td>
              <td>
                <span style="font-family:monospace; font-size:0.75rem; color:var(--admin-accent); background:rgba(99,102,241,0.1); padding:0.2rem 0.5rem; border-radius:4px;">
                  <?= number_format($m['latitude'], 4) ?>, <?= number_format($m['longitude'], 4) ?>
                </span>
              </td>
              <td>
                <div style="font-weight:600; font-size:0.8125rem; color:var(--admin-emerald);"><?= htmlspecialchars($m['operating_days']) ?></div>
                <div style="font-size:0.75rem; color:var(--admin-text-subtle);"><?= htmlspecialchars($m['operating_hours']) ?></div>
              </td>
              <td>
                <span style="font-weight:700; font-size:0.85rem;">
                  🏪 <?= $m['assigned_stalls_count'] ?>
                </span>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($m['status']) ?>">
                  <?= ucfirst($m['status']) ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex; gap:0.4rem; justify-content:flex-end;">
                  <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" 
                          onclick='editMarket(<?= json_encode($m) ?>)'>
                    <i data-lucide="edit-3"></i> Edit
                  </button>
                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" 
                          onclick="deleteMarket(<?= $m['market_id'] ?>)">
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

<!-- Modal Dialog: Create / Edit Market -->
<div class="admin-modal-backdrop" id="marketModalBackdrop">
  <div class="admin-modal-card" style="max-width:700px;">
    <div class="admin-modal-header">
      <h3 id="marketModalTitle">Establish New Farmers Market</h3>
      <button type="button" class="admin-modal-close" onclick="closeMarketModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form id="marketForm" onsubmit="saveMarket(event)">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="market_id" id="m_id" value="0">

      <div class="admin-modal-body">
        <div class="admin-form-group">
          <label class="admin-form-label">Market Name *</label>
          <input type="text" name="market_name" id="m_name" class="admin-form-control" required placeholder="e.g. Downtown Green Plaza Farmers Market">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Street Address *</label>
          <input type="text" name="address" id="m_address" class="admin-form-control" required placeholder="e.g. 100 Main Street Plaza, Hub Center">
        </div>

        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">City *</label>
            <input type="text" name="city" id="m_city" class="admin-form-control" value="Metropolis" required>
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">State</label>
            <input type="text" name="state" id="m_state" class="admin-form-control" value="State">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Postal Code</label>
            <input type="text" name="postal_code" id="m_postal" class="admin-form-control" placeholder="10001">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">Operating Days *</label>
            <input type="text" name="operating_days" id="m_days" class="admin-form-control" required placeholder="e.g. Saturday, Sunday">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Operating Hours *</label>
            <input type="text" name="operating_hours" id="m_hours" class="admin-form-control" required placeholder="e.g. 8:00 AM - 2:00 PM">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">Latitude *</label>
            <input type="number" step="0.00000001" name="latitude" id="m_lat" class="admin-form-control" required value="40.7128">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Longitude *</label>
            <input type="number" step="0.00000001" name="longitude" id="m_lng" class="admin-form-control" required value="-74.0060">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Status</label>
            <select name="status" id="m_status" class="admin-form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <div class="admin-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeMarketModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i data-lucide="save"></i> Save Market Hub
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Leaflet Map Initialization
document.addEventListener('DOMContentLoaded', function() {
  const mapElement = document.getElementById('adminMarketsMap');
  if (!mapElement) return;

  const marketsData = <?= json_encode($markets) ?>;
  
  // Center on first market or default coordinates
  const defaultLat = marketsData.length ? parseFloat(marketsData[0].latitude) : 40.7128;
  const defaultLng = marketsData.length ? parseFloat(marketsData[0].longitude) : -74.0060;

  const map = L.map('adminMarketsMap').setView([defaultLat, defaultLng], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const markers = [];
  marketsData.forEach(m => {
    const lat = parseFloat(m.latitude);
    const lng = parseFloat(m.longitude);
    if (!isNaN(lat) && !isNaN(lng)) {
      const marker = L.marker([lat, lng]).addTo(map);
      marker.bindPopup(`
        <div style="font-family:sans-serif; min-width:180px;">
          <strong style="color:#0f172a; font-size:14px;">${m.market_name}</strong>
          <div style="font-size:12px; color:#64748b; margin:4px 0;">${m.address}</div>
          <div style="font-size:11px; color:#10b981; font-weight:700;">🕒 ${m.operating_days} (${m.operating_hours})</div>
        </div>
      `);
      markers.push([lat, lng]);
    }
  });

  if (markers.length > 1) {
    map.fitBounds(markers, { padding: [40, 40] });
  }
});

// Modal Logic
function openMarketModal() {
  document.getElementById('marketModalTitle').textContent = 'Establish New Farmers Market';
  document.getElementById('m_id').value = '0';
  document.getElementById('m_name').value = '';
  document.getElementById('m_address').value = '';
  document.getElementById('m_city').value = 'Metropolis';
  document.getElementById('m_state').value = 'State';
  document.getElementById('m_postal').value = '';
  document.getElementById('m_days').value = 'Saturday, Sunday';
  document.getElementById('m_hours').value = '8:00 AM - 2:00 PM';
  document.getElementById('m_lat').value = '40.712800';
  document.getElementById('m_lng').value = '-74.006000';
  document.getElementById('m_status').value = 'active';

  document.getElementById('marketModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function editMarket(m) {
  document.getElementById('marketModalTitle').textContent = 'Edit Farmers Market Hub';
  document.getElementById('m_id').value = m.market_id;
  document.getElementById('m_name').value = m.market_name;
  document.getElementById('m_address').value = m.address;
  document.getElementById('m_city').value = m.city;
  document.getElementById('m_state').value = m.state;
  document.getElementById('m_postal').value = m.postal_code || '';
  document.getElementById('m_days').value = m.operating_days;
  document.getElementById('m_hours').value = m.operating_hours;
  document.getElementById('m_lat').value = m.latitude;
  document.getElementById('m_lng').value = m.longitude;
  document.getElementById('m_status').value = m.status;

  document.getElementById('marketModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeMarketModal() {
  document.getElementById('marketModalBackdrop').classList.remove('open');
}

async function saveMarket(e) {
  e.preventDefault();
  const form = document.getElementById('marketForm');
  const fd = new FormData(form);

  try {
    const res = await fetch('<?= BASE_URL ?>/admin/api/market_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      closeMarketModal();
      setTimeout(() => window.location.reload(), 600);
    } else {
      showAdminToast(data.message || 'Save failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network communication error', 'error');
  }
}

async function deleteMarket(marketId) {
  if (!confirm('Are you sure you want to delete or deactivate this farmers market?')) return;

  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('market_id', marketId);

    const res = await fetch('<?= BASE_URL ?>/admin/api/market_action.php', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showAdminToast(data.message || 'Delete failed', 'error');
    }
  } catch (err) {
    showAdminToast('Network error while deleting market', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
