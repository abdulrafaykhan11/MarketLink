<?php
/**
 * MarketLink - Admin Farmers Markets Management & Interactive Geo Hubs
 * SRS Section 1.6: Admin can add, edit, or remove farmers markets, including name, address,
 * operating days, timings, and map coordinates using OpenStreetMap / Leaflet.
 */

$pageTitle = 'Farmers Markets';
$activeNav = 'markets';
require_once __DIR__ . '/includes/admin_header.php';

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

<!-- Overview Map -->
<div class="admin-card" style="margin-bottom:2rem;">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title">
        <i data-lucide="map" style="color:var(--admin-cyan);"></i>
        Geographic Distribution &amp; Pickup Hubs
      </h3>
      <p class="admin-card-subtitle">Live OpenStreetMap — click a pin to see market details</p>
    </div>
  </div>
  <div style="height:420px;width:100%;border-radius:0 0 var(--radius-xl) var(--radius-xl);overflow:hidden;" id="adminMarketsMap"></div>
</div>

<!-- Markets Table -->
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
          <th>Location &amp; City</th>
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
            <td colspan="7" style="text-align:center;padding:3rem;color:var(--admin-text-subtle);">
              No markets configured. Click &ldquo;Establish New Market&rdquo; above.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($markets as $m): ?>
            <tr>
              <td>
                <div style="font-weight:700;font-size:.95rem;color:var(--admin-text-main);"><?= htmlspecialchars($m['market_name']) ?></div>
                <div style="font-size:.75rem;color:var(--admin-text-subtle);">ID #<?= $m['market_id'] ?> &bull; Added <?= date('M d, Y', strtotime($m['created_at'])) ?></div>
              </td>
              <td>
                <div style="font-size:.85rem;color:var(--admin-text-main);"><?= htmlspecialchars($m['address']) ?></div>
                <div style="font-size:.75rem;color:var(--admin-text-subtle);"><?= htmlspecialchars($m['city']) ?>, <?= htmlspecialchars($m['state']) ?> <?= htmlspecialchars($m['postal_code']) ?></div>
              </td>
              <td>
                <span style="font-family:monospace;font-size:.75rem;color:var(--admin-accent);background:rgba(99,102,241,.1);padding:.2rem .5rem;border-radius:4px;">
                  <?= number_format($m['latitude'], 5) ?>, <?= number_format($m['longitude'], 5) ?>
                </span>
              </td>
              <td>
                <div style="font-weight:600;font-size:.8125rem;color:var(--admin-emerald);"><?= htmlspecialchars($m['operating_days']) ?></div>
                <div style="font-size:.75rem;color:var(--admin-text-subtle);"><?= htmlspecialchars($m['operating_hours']) ?></div>
              </td>
              <td>
                <span style="font-weight:700;font-size:.85rem;">
                  <i data-lucide="store" style="width:14px;height:14px;vertical-align:middle;color:var(--admin-emerald);"></i>
                  <?= $m['assigned_stalls_count'] ?>
                </span>
              </td>
              <td>
                <span class="status-pill <?= htmlspecialchars($m['status']) ?>"><?= ucfirst($m['status']) ?></span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:.4rem;justify-content:flex-end;">
                  <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" onclick='editMarket(<?= json_encode($m) ?>)'>
                    <i data-lucide="edit-3"></i> Edit
                  </button>
                  <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="deleteMarket(<?= $m['market_id'] ?>)">
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

<!-- =========================================================
     Modal: Create / Edit Market
     ========================================================= -->
<div class="admin-modal-backdrop" id="marketModalBackdrop"
     style="overflow-y:auto; align-items:flex-start; padding:1rem;">
  <div class="admin-modal-card" style="max-width:740px;width:100%;margin:auto;max-height:none;">
    <div class="admin-modal-header">
      <h3 id="marketModalTitle">Establish New Farmers Market</h3>
      <button type="button" class="admin-modal-close" onclick="closeMarketModal()">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form id="marketForm" onsubmit="saveMarket(event)">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="market_id" id="m_id" value="0">

      <!-- Scrollable body — Save button always visible via sticky footer -->
      <div class="admin-modal-body" style="overflow-y:auto;max-height:calc(100vh - 160px);padding:1.5rem;">

        <div class="admin-form-group">
          <label class="admin-form-label">Market Name *</label>
          <input type="text" name="market_name" id="m_name" class="admin-form-control" required
                 placeholder="e.g. Liberty Market Farmers Hub">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Street Address *</label>
          <input type="text" name="address" id="m_address" class="admin-form-control" required
                 placeholder="e.g. Shop 12, Liberty Market, Gulberg III">
        </div>

        <!-- Province -> City -> Postal -->
        <div style="display:grid;grid-template-columns:1fr 1fr 130px;gap:1rem;align-items:start;">

          <div class="admin-form-group">
            <label class="admin-form-label">Province / State *</label>
            <select id="m_state" name="state" class="admin-form-control" onchange="onProvinceChange()" required>
              <option value="">-- Select Province --</option>
              <option value="Punjab">Punjab</option>
              <option value="Sindh">Sindh</option>
              <option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option>
              <option value="Balochistan">Balochistan</option>
              <option value="Azad Kashmir">Azad Kashmir</option>
              <option value="Gilgit-Baltistan">Gilgit-Baltistan</option>
              <option value="Islamabad Capital Territory">Islamabad Capital Territory</option>
            </select>
          </div>

          <div class="admin-form-group" style="position:relative;">
            <label class="admin-form-label">City *</label>
            <input type="text" id="m_city_input" class="admin-form-control"
                   placeholder="Select province first..." readonly onclick="toggleCityDropdown()">
            <input type="hidden" name="city" id="m_city">

            <div id="pkCityDropdown" class="pk-city-dd">
              <div class="pk-city-search-wrap">
                <input type="text" id="pkCitySearch" class="pk-city-search"
                       placeholder="Search city..." oninput="filterPkCities(this.value)" autocomplete="off">
              </div>
              <ul id="pkCityList" class="pk-city-list"></ul>
            </div>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Postal Code</label>
            <input type="text" name="postal_code" id="m_postal" class="admin-form-control" placeholder="54000">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">Operating Days *</label>
            <input type="text" name="operating_days" id="m_days" class="admin-form-control" required
                   placeholder="e.g. Saturday, Sunday">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Operating Hours *</label>
            <input type="text" name="operating_hours" id="m_hours" class="admin-form-control" required
                   placeholder="e.g. 8:00 AM - 2:00 PM">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 130px;gap:1rem;align-items:start;">
          <div class="admin-form-group">
            <label class="admin-form-label">Latitude *</label>
            <input type="number" step="0.000001" name="latitude" id="m_lat" class="admin-form-control"
                   required placeholder="31.5204" oninput="syncPickerFromInputs()">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Longitude *</label>
            <input type="number" step="0.000001" name="longitude" id="m_lng" class="admin-form-control"
                   required placeholder="74.3587" oninput="syncPickerFromInputs()">
          </div>
          <div class="admin-form-group">
            <label class="admin-form-label">Status</label>
            <select name="status" id="m_status" class="admin-form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Pin-Drop Map -->
        <div class="admin-form-group">
          <label class="admin-form-label" style="display:flex;align-items:center;gap:.5rem;">
            <i data-lucide="crosshair" style="width:15px;height:15px;color:var(--admin-accent);"></i>
            Precise Pin-Drop Location Picker
            <span style="font-size:.7rem;color:var(--admin-text-subtle);font-weight:400;margin-left:auto;">
              Click map or drag the pin — coordinates auto-fill above
            </span>
          </label>
          <div id="modalPickerMap" style="height:280px;width:100%;border-radius:var(--radius-lg);
               border:1.5px solid var(--admin-card-border);overflow:hidden;background:#1a2e24;"></div>
          <div style="display:flex;align-items:center;gap:.5rem;margin-top:.5rem;flex-wrap:wrap;">
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" onclick="centerPickerOnPakistan()">
              <i data-lucide="flag" style="width:13px;height:13px;"></i> Reset to Pakistan
            </button>
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" onclick="geocodeCity(false)">
              <i data-lucide="search" style="width:13px;height:13px;"></i> Find Selected City
            </button>
            <span id="pinCoordDisplay" style="font-family:monospace;font-size:.78rem;color:var(--admin-accent);
              background:rgba(99,102,241,.12);padding:.25rem .6rem;border-radius:6px;margin-left:auto;">
              No pin set
            </span>
          </div>
        </div>

      </div><!-- /admin-modal-body -->

      <div class="admin-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeMarketModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary" id="saveMarketBtn">
          <i data-lucide="save"></i> Save Market Hub
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Scoped Styles -->
<style>
/* Pakistan City Dropdown */
.pk-city-dd {
  display: none;
  position: absolute;
  top: calc(100% + 4px); left: 0; right: 0;
  background: var(--bg-surface, #0e2a22);
  border: 1.5px solid var(--admin-accent);
  border-radius: var(--radius-lg);
  box-shadow: 0 8px 30px rgba(0,0,0,.5);
  z-index: 9999;
  max-height: 230px;
  overflow: hidden;
  flex-direction: column;
}
.pk-city-dd.open { display: flex; }
.pk-city-search-wrap {
  padding: .45rem .6rem;
  border-bottom: 1px solid var(--admin-table-border);
  flex-shrink: 0;
}
.pk-city-search {
  width: 100%; background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.1); border-radius: 6px;
  color: var(--admin-text-main); padding: .35rem .6rem;
  font-size: .8rem; outline: none; box-sizing: border-box;
}
.pk-city-search:focus { border-color: var(--admin-accent); }
.pk-city-list {
  list-style: none; margin: 0; padding: .35rem 0;
  overflow-y: auto; flex: 1;
}
.pk-city-list li {
  padding: .42rem 1rem; font-size: .85rem;
  color: var(--admin-text-main); cursor: pointer;
  transition: background .13s;
}
.pk-city-list li:hover { background: rgba(99,102,241,.18); color: var(--admin-accent); }
.pk-city-list li.no-results { color: var(--admin-text-subtle); font-style: italic; cursor: default; }

/* Short-screen fix: backdrop scrollable */
@media (max-height: 750px) {
  .admin-modal-backdrop { align-items: flex-start !important; padding-top: .5rem !important; }
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
/* ============================================================
   Pakistan Province -> City Data
   ============================================================ */
const PK_CITIES = {
  "Punjab": [
    "Lahore","Faisalabad","Rawalpindi","Gujranwala","Multan","Sialkot","Bahawalpur",
    "Sargodha","Sheikhupura","Jhang","Rahim Yar Khan","Gujrat","Kasur","Okara",
    "Sahiwal","Mianwali","Chiniot","Jhelum","Hafizabad","Khanewal","Pakpattan",
    "Attock","Chakwal","Mandi Bahauddin","Narowal","Vehari","Lodhran","Khushab",
    "Bhakkar","Layyah","Muzaffargarh","Dera Ghazi Khan","Toba Tek Singh",
    "Nankana Sahib","Kamoke","Wah Cantonment","Taxila","Murree"
  ],
  "Sindh": [
    "Karachi","Hyderabad","Sukkur","Larkana","Nawabshah","Mirpur Khas","Khairpur",
    "Jacobabad","Shikarpur","Dadu","Thatta","Badin","Sanghar","Tharparkar",
    "Umerkot","Ghotki","Kashmore","Matiari","Tando Allahyar","Tando Muhammad Khan"
  ],
  "Khyber Pakhtunkhwa": [
    "Peshawar","Abbottabad","Mardan","Mingora","Kohat","Bannu","Dera Ismail Khan",
    "Swabi","Nowshera","Charsadda","Mansehra","Haripur","Buner","Dir","Chitral",
    "Karak","Hangu","Lakki Marwat","Tank","Swat","Malakand"
  ],
  "Balochistan": [
    "Quetta","Gwadar","Turbat","Khuzdar","Chaman","Zhob","Loralai",
    "Dera Murad Jamali","Nushki","Dalbandin","Panjgur","Kharan","Mastung","Kalat","Surab"
  ],
  "Azad Kashmir": ["Muzaffarabad","Mirpur","Rawalakot","Bagh","Kotli","Bhimber","Haveli"],
  "Gilgit-Baltistan": ["Gilgit","Skardu","Hunza","Chilas","Ghanche","Ghizer","Astore","Diamer","Nagar"],
  "Islamabad Capital Territory": ["Islamabad"]
};

let currentCities = [];

function onProvinceChange() {
  const prov = document.getElementById('m_state').value;
  currentCities = PK_CITIES[prov] || [];
  document.getElementById('m_city_input').value = '';
  document.getElementById('m_city').value = '';
  document.getElementById('m_city_input').placeholder = prov ? 'Click to select city...' : 'Select province first...';
  document.getElementById('pkCityDropdown').classList.remove('open');
  if (prov) geocodeCity(true);
}

function toggleCityDropdown() {
  const prov = document.getElementById('m_state').value;
  if (!prov) { showAdminToast('Please select a province first.', 'error'); return; }
  const dd = document.getElementById('pkCityDropdown');
  if (dd.classList.contains('open')) {
    dd.classList.remove('open');
  } else {
    renderCityList(currentCities);
    dd.classList.add('open');
    setTimeout(() => document.getElementById('pkCitySearch').focus(), 50);
  }
}

function renderCityList(cities) {
  const ul = document.getElementById('pkCityList');
  ul.innerHTML = '';
  if (!cities.length) {
    ul.innerHTML = '<li class="no-results">No cities found</li>';
    return;
  }
  cities.forEach(city => {
    const li = document.createElement('li');
    li.textContent = city;
    li.onclick = () => selectCity(city);
    ul.appendChild(li);
  });
}

function filterPkCities(q) {
  renderCityList(currentCities.filter(c => c.toLowerCase().includes(q.toLowerCase())));
}

function selectCity(city) {
  document.getElementById('m_city_input').value = city;
  document.getElementById('m_city').value = city;
  document.getElementById('pkCityDropdown').classList.remove('open');
  document.getElementById('pkCitySearch').value = '';
  geocodeCity(false);
}

document.addEventListener('click', function(e) {
  const inp = document.getElementById('m_city_input');
  const dd  = document.getElementById('pkCityDropdown');
  if (dd && inp && !inp.contains(e.target) && !dd.contains(e.target)) {
    dd.classList.remove('open');
  }
});

/* ============================================================
   Pin-Drop Map inside Modal
   ============================================================ */
let pickerMap = null, pickerMarker = null;
const PK_CENTER = [30.3753, 69.3451];

function initPickerMap() {
  if (pickerMap) { pickerMap.invalidateSize(); return; }

  pickerMap = L.map('modalPickerMap', { zoomControl: true }).setView(PK_CENTER, 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '(c) OpenStreetMap contributors'
  }).addTo(pickerMap);

  pickerMap.on('click', e => setPickerPin(e.latlng.lat, e.latlng.lng, true));

  const lat = parseFloat(document.getElementById('m_lat').value);
  const lng = parseFloat(document.getElementById('m_lng').value);
  if (!isNaN(lat) && !isNaN(lng) && (lat !== 0 || lng !== 0)) {
    setPickerPin(lat, lng, false);
    pickerMap.setView([lat, lng], 14);
  }
}

function makePinIcon() {
  return L.divIcon({
    className: '',
    html: '<div style="width:26px;height:26px;background:linear-gradient(135deg,#6366f1,#10b981);border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 12px rgba(99,102,241,.6);cursor:grab;"></div>',
    iconSize: [26, 26], iconAnchor: [13, 26]
  });
}

function setPickerPin(lat, lng, updateInputs) {
  lat = Math.round(lat * 1000000) / 1000000;
  lng = Math.round(lng * 1000000) / 1000000;

  if (pickerMarker) {
    pickerMarker.setLatLng([lat, lng]);
  } else {
    pickerMarker = L.marker([lat, lng], { icon: makePinIcon(), draggable: true }).addTo(pickerMap);
    pickerMarker.on('dragend', function() {
      const p = pickerMarker.getLatLng();
      setPickerPin(p.lat, p.lng, true);
    });
  }

  if (updateInputs) {
    document.getElementById('m_lat').value = lat;
    document.getElementById('m_lng').value = lng;
  }
  document.getElementById('pinCoordDisplay').textContent = 'Pin: ' + lat + ', ' + lng;
}

function syncPickerFromInputs() {
  if (!pickerMap) return;
  const lat = parseFloat(document.getElementById('m_lat').value);
  const lng = parseFloat(document.getElementById('m_lng').value);
  if (!isNaN(lat) && !isNaN(lng)) {
    setPickerPin(lat, lng, false);
    pickerMap.setView([lat, lng], 14);
  }
}

function centerPickerOnPakistan() {
  if (pickerMap) pickerMap.setView(PK_CENTER, 5);
}

async function geocodeCity(provinceOnly) {
  const city = document.getElementById('m_city').value;
  const prov = document.getElementById('m_state').value;
  if (!prov) return;
  const q = (provinceOnly || !city) ? prov + ', Pakistan' : city + ', ' + prov + ', Pakistan';
  try {
    const r = await fetch(
      'https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) +
      '&format=json&limit=1&countrycodes=pk',
      { headers: { 'Accept-Language': 'en' } }
    );
    const d = await r.json();
    if (d && d[0] && pickerMap) {
      const lat = parseFloat(d[0].lat), lng = parseFloat(d[0].lon);
      pickerMap.setView([lat, lng], provinceOnly ? 9 : 13);
      setPickerPin(lat, lng, true);
    }
  } catch(e) { console.warn('Geocode failed', e); }
}

/* ============================================================
   Main Overview Map with Named Markers
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
  const el = document.getElementById('adminMarketsMap');
  if (!el) return;

  const marketsData = <?= json_encode($markets) ?>;
  const defLat = marketsData.length ? parseFloat(marketsData[0].latitude)  : 30.3753;
  const defLng = marketsData.length ? parseFloat(marketsData[0].longitude) : 69.3451;

  const map = L.map('adminMarketsMap').setView([defLat, defLng], marketsData.length === 1 ? 13 : 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const bounds = [];
  marketsData.forEach(function(m) {
    const lat = parseFloat(m.latitude), lng = parseFloat(m.longitude);
    if (isNaN(lat) || isNaN(lng)) return;

    const color = (m.status === 'active') ? '#10b981' : '#6b7280';
    const name  = m.market_name.length > 20 ? m.market_name.substring(0, 18) + '..' : m.market_name;

    const icon = L.divIcon({
      className: '',
      html: '<div style="display:flex;flex-direction:column;align-items:center;">' +
              '<div style="width:20px;height:20px;background:' + color + ';border:3px solid #fff;' +
              'border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 10px rgba(0,0,0,.5);"></div>' +
              '<div style="margin-top:4px;background:rgba(14,42,34,.92);border:1.5px solid ' + color + ';' +
              'color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;' +
              'white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.4);text-align:center;">' +
              name + '</div></div>',
      iconSize: [140, 52],
      iconAnchor: [70, 25],
      popupAnchor: [0, -30]
    });

    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
    marker.bindPopup(
      '<div style="font-family:sans-serif;min-width:200px;">' +
        '<strong style="color:#0f172a;font-size:14px;">' + m.market_name + '</strong>' +
        '<div style="font-size:12px;color:#64748b;margin:4px 0;">' + m.address + '</div>' +
        '<div style="font-size:11px;color:#10b981;font-weight:700;">' + m.operating_days + ' (' + m.operating_hours + ')</div>' +
        '<div style="font-size:11px;color:#6366f1;margin-top:4px;">Coords: ' +
          parseFloat(m.latitude).toFixed(5) + ', ' + parseFloat(m.longitude).toFixed(5) +
        '</div>' +
      '</div>'
    );
    bounds.push([lat, lng]);
  });

  if (bounds.length > 1) map.fitBounds(bounds, { padding: [50, 50] });
});

/* ============================================================
   Modal Open / Close / Save / Delete
   ============================================================ */
function openMarketModal() {
  document.getElementById('marketModalTitle').textContent = 'Establish New Farmers Market';
  document.getElementById('m_id').value         = '0';
  document.getElementById('m_name').value       = '';
  document.getElementById('m_address').value    = '';
  document.getElementById('m_city_input').value = '';
  document.getElementById('m_city').value       = '';
  document.getElementById('m_state').value      = '';
  document.getElementById('m_postal').value     = '';
  document.getElementById('m_days').value       = 'Saturday, Sunday';
  document.getElementById('m_hours').value      = '8:00 AM - 2:00 PM';
  document.getElementById('m_lat').value        = '';
  document.getElementById('m_lng').value        = '';
  document.getElementById('m_status').value     = 'active';
  document.getElementById('pinCoordDisplay').textContent   = 'No pin set';
  document.getElementById('m_city_input').placeholder      = 'Select province first...';
  currentCities = [];

  document.getElementById('marketModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();

  setTimeout(function() {
    initPickerMap();
    if (pickerMap) {
      pickerMap.invalidateSize();
      pickerMap.setView(PK_CENTER, 5);
      if (pickerMarker) { pickerMap.removeLayer(pickerMarker); pickerMarker = null; }
    }
  }, 220);
}

function editMarket(m) {
  document.getElementById('marketModalTitle').textContent = 'Edit Farmers Market Hub';
  document.getElementById('m_id').value         = m.market_id;
  document.getElementById('m_name').value       = m.market_name;
  document.getElementById('m_address').value    = m.address;
  document.getElementById('m_state').value      = m.state || '';
  currentCities = PK_CITIES[m.state] || [];
  document.getElementById('m_city_input').value = m.city || '';
  document.getElementById('m_city').value       = m.city || '';
  document.getElementById('m_city_input').placeholder = m.state ? 'Click to select city...' : 'Select province first...';
  document.getElementById('m_postal').value     = m.postal_code || '';
  document.getElementById('m_days').value       = m.operating_days;
  document.getElementById('m_hours').value      = m.operating_hours;
  document.getElementById('m_lat').value        = m.latitude;
  document.getElementById('m_lng').value        = m.longitude;
  document.getElementById('m_status').value     = m.status;

  const lat = parseFloat(m.latitude), lng = parseFloat(m.longitude);
  document.getElementById('pinCoordDisplay').textContent = (!isNaN(lat) && !isNaN(lng))
    ? 'Pin: ' + lat.toFixed(6) + ', ' + lng.toFixed(6) : 'No pin set';

  document.getElementById('marketModalBackdrop').classList.add('open');
  if (typeof lucide !== 'undefined') lucide.createIcons();

  setTimeout(function() {
    initPickerMap();
    if (pickerMap) {
      pickerMap.invalidateSize();
      if (!isNaN(lat) && !isNaN(lng)) {
        pickerMap.setView([lat, lng], 14);
        setPickerPin(lat, lng, false);
      }
    }
  }, 220);
}

function closeMarketModal() {
  document.getElementById('marketModalBackdrop').classList.remove('open');
}

async function saveMarket(e) {
  e.preventDefault();
  if (!document.getElementById('m_city').value.trim()) {
    showAdminToast('Please select a city from the dropdown.', 'error'); return;
  }
  const fd  = new FormData(document.getElementById('marketForm'));
  const btn = document.getElementById('saveMarketBtn');
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;animation:spin 1s linear infinite;vertical-align:middle;"></i> Saving...';
  if (typeof lucide !== 'undefined') lucide.createIcons();

  try {
    const res  = await fetch('<?= BASE_URL ?>/admin/api/market_action.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      closeMarketModal();
      setTimeout(function() { window.location.reload(); }, 600);
    } else {
      showAdminToast(data.message || 'Save failed', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i data-lucide="save"></i> Save Market Hub';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  } catch(err) {
    showAdminToast('Network error', 'error');
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="save"></i> Save Market Hub';
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}

async function deleteMarket(marketId) {
  if (!confirm('Are you sure you want to delete or deactivate this farmers market?')) return;
  try {
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('market_id', marketId);
    const res  = await fetch('<?= BASE_URL ?>/admin/api/market_action.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      showAdminToast(data.message, 'success');
      setTimeout(function() { window.location.reload(); }, 600);
    } else {
      showAdminToast(data.message || 'Delete failed', 'error');
    }
  } catch(err) {
    showAdminToast('Network error while deleting market', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
