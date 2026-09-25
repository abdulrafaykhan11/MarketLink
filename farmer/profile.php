<?php
/**
 * MarketLink - Farmer Stall Profile, Photo Management & Interactive Map Pinning
 * SRS: Compulsory profile photo, editable lat/long, fullscreen map on dbl-click
 */

$pageTitle = 'Stall Profile & Location Map';
$activePage = 'profile';
require_once __DIR__ . '/includes/farmer_header.php';

$lat = (float)($farmer['latitude'] ?? 24.8607);
$lng = (float)($farmer['longitude'] ?? 67.0011);
$profileImage = $farmer['profile_image'] ?? '';
$hasPhoto = !empty($profileImage);
$profileImageUrl = $hasPhoto ? resolveImageUrl($profileImage) : '';
?>

<div class="farmer-content">

  <!-- Header -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Farm Stall Profile &amp; Location
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        Manage your public stall identity, profile photo, and GPS location pin.
      </p>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <?php if (!$hasPhoto): ?>
        <span style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #f87171; font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: var(--radius-full); display: inline-flex; align-items: center; gap: 0.4rem;">
          <i data-lucide="alert-triangle" style="width:13px;height:13px;"></i> Photo Required
        </span>
      <?php endif; ?>
      <span class="farmer-status-badge <?= ($approvalStatus === 'approved') ? 'badge-ready' : 'badge-placed' ?>">
        <?= ($approvalStatus === 'approved') ? '✓ Verified Producer' : '⏳ Review In Progress' ?>
      </span>
    </div>
  </div>

  <!-- ========== PROFILE PHOTO CARD ========== -->
  <div class="farmer-card" style="margin-bottom: 2rem;">
    <div class="farmer-card-header">
      <div class="farmer-card-title-group">
        <h3 class="farmer-card-title">
          <i data-lucide="camera" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
          <span>Profile Photo <span style="color:#ef4444; font-size:0.75rem; font-weight:600; margin-left:0.4rem;">Required</span></span>
        </h3>
        <span class="farmer-card-subtitle">Your face shown to customers on the marketplace — builds trust and authenticity</span>
      </div>
    </div>

    <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
      <!-- Current Photo Preview -->
      <div style="position: relative; flex-shrink: 0;">
        <div id="photoPreview" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid <?= $hasPhoto ? 'var(--primary-500)' : 'rgba(239,68,68,0.6)' ?>; background: var(--bg-surface-elevated); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; color: var(--primary-400); font-family: var(--font-heading);">
          <?php if ($hasPhoto): ?>
            <img id="photoPreviewImg" src="<?= htmlspecialchars($profileImageUrl) ?>" alt="<?= htmlspecialchars($displayName) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/logo-dark.svg';">
          <?php else: ?>
            <span id="photoInitial"><?= strtoupper(substr($displayName, 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($hasPhoto): ?>
          <div style="position: absolute; bottom: 4px; right: 4px; width: 26px; height: 26px; background: var(--primary-500); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface);">
            <i data-lucide="check" style="width:13px;height:13px;color:#fff;"></i>
          </div>
        <?php else: ?>
          <div style="position: absolute; bottom: 4px; right: 4px; width: 26px; height: 26px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface);">
            <i data-lucide="alert-triangle" style="width:12px;height:12px;color:#fff;"></i>
          </div>
        <?php endif; ?>
      </div>

      <!-- Upload Controls -->
      <div style="flex: 1; min-width: 250px;">
        <?php if (!$hasPhoto): ?>
          <div style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.84375rem; color: #f87171;">
            <strong>⚠ Profile photo is compulsory.</strong> Without a photo, customers cannot verify your identity. Please upload a clear face photo.
          </div>
        <?php endif; ?>

        <div id="photoDropZone" onclick="document.getElementById('profilePhotoInput').click()" style="border: 2px dashed var(--border-color); border-radius: var(--radius-lg); padding: 1.5rem 1.25rem; text-align: center; cursor: pointer; transition: all 0.2s; position: relative;" onmouseover="this.style.borderColor='var(--primary-500)'; this.style.background='rgba(34,197,94,0.04)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='transparent'">
          <i data-lucide="upload-cloud" style="width:28px;height:28px;color:var(--primary-500);margin-bottom:0.5rem;"></i>
          <div style="font-weight:600;color:var(--text-primary);font-size:0.875rem;margin-bottom:0.25rem;">Click to upload profile photo</div>
          <div style="font-size:0.75rem;color:var(--slate-400);">JPG, PNG, WEBP up to 5MB</div>
          <input type="file" id="profilePhotoInput" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;" onchange="previewAndUploadPhoto(this)">
        </div>

        <div id="photoUploadProgress" style="display:none;margin-top:0.75rem;">
          <div style="height:4px;background:var(--bg-surface-elevated);border-radius:99px;overflow:hidden;">
            <div id="photoProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,var(--primary-500),var(--sky-400));transition:width 0.3s;border-radius:99px;"></div>
          </div>
          <div id="photoProgressText" style="font-size:0.75rem;color:var(--slate-400);margin-top:0.35rem;text-align:center;">Uploading...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== BUSINESS INFO + MAP GRID ========== -->
  <form id="profileForm" onsubmit="submitProfileForm(event)">
    <div class="farmer-grid-2-col" style="margin-bottom: 2rem;">

      <!-- Left Column: Business Details Form -->
      <div class="farmer-card">
        <div class="farmer-card-header">
          <div class="farmer-card-title-group">
            <h3 class="farmer-card-title">
              <i data-lucide="store" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
              <span>Farm &amp; Business Information</span>
            </h3>
            <span class="farmer-card-subtitle">Public details shown to customers on the marketplace directory</span>
          </div>
        </div>

        <!-- Stall Name -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="stallName">Farm / Stall Name <span style="color:#ef4444;">*</span></label>
          <input type="text" class="farmer-form-input" id="stallName" name="stall_name" value="<?= htmlspecialchars($stallName) ?>" required>
        </div>

        <!-- Contact Person & Phone -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="farmer-form-group">
            <label class="farmer-form-label" for="contactPerson">Contact Person <span style="color:#ef4444;">*</span></label>
            <input type="text" class="farmer-form-input" id="contactPerson" name="contact_person" value="<?= htmlspecialchars($displayName) ?>" required>
          </div>

          <div class="farmer-form-group">
            <label class="farmer-form-label" for="businessPhone">Business Phone <span style="color:#ef4444;">*</span></label>
            <input type="text" class="farmer-form-input" id="businessPhone" name="business_phone" value="<?= htmlspecialchars($farmer['business_phone'] ?: $farmer['phone_number']) ?>" required>
          </div>
        </div>

        <!-- Business Email -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="businessEmail">Business Email</label>
          <input type="email" class="farmer-form-input" id="businessEmail" name="business_email" value="<?= htmlspecialchars($farmer['business_email'] ?: $farmer['email']) ?>">
        </div>

        <!-- Description / Farm Story -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="desc">Farm Story &amp; Organic Growing Practices</label>
          <textarea class="farmer-form-textarea" id="desc" name="description" rows="4" placeholder="Tell customers about your soil, heirloom crops, family heritage, pesticide-free practices..."><?= htmlspecialchars($farmer['description'] ?: '') ?></textarea>
        </div>

        <!-- Physical Farm Address -->
        <div class="farmer-form-group">
          <label class="farmer-form-label" for="address">Farm / Base Facility Address <span style="color:#ef4444;">*</span></label>
          <input type="text" class="farmer-form-input" id="address" name="address" value="<?= htmlspecialchars($farmer['address'] ?: 'Agri Zone, Plot 88') ?>" required>
        </div>
      </div>

      <!-- Right Column: Interactive OpenStreetMap & GPS Coordinates -->
      <div class="farmer-card" style="display: flex; flex-direction: column;">
        <div class="farmer-card-header">
          <div class="farmer-card-title-group">
            <h3 class="farmer-card-title">
              <i data-lucide="map-pin" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
              <span>GPS Location Pin</span>
            </h3>
            <span class="farmer-card-subtitle">Click map or drag marker · Enter coords manually · <strong>Double-click map</strong> to go fullscreen</span>
          </div>
        </div>

        <!-- Leaflet Map Container -->
        <div style="position: relative; margin-bottom: 1.25rem;">
          <div id="farmMap" style="height: 260px; width: 100%; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border-color); cursor: crosshair;"></div>
          <!-- Fullscreen hint badge -->
          <div id="mapFullscreenHint" style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.65); backdrop-filter: blur(6px); color: #fff; font-size: 0.6875rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: var(--radius-md); pointer-events: none; display: flex; align-items: center; gap: 0.35rem; z-index: 999;">
            <i data-lucide="maximize-2" style="width:11px;height:11px;"></i> Dbl-click for fullscreen
          </div>
        </div>

        <!-- Editable Lat & Long Inputs -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div class="farmer-form-group" style="margin-bottom: 0;">
            <label class="farmer-form-label" for="latitude">
              <i data-lucide="crosshair" style="width:13px;height:13px;color:var(--primary-500);vertical-align:middle;"></i>
              Latitude
            </label>
            <input type="number" step="0.000001" class="farmer-form-input" id="latitude" name="latitude" value="<?= htmlspecialchars($lat) ?>" placeholder="e.g. 24.860700" oninput="syncMapFromInputs()" style="font-family: monospace;">
          </div>
          <div class="farmer-form-group" style="margin-bottom: 0;">
            <label class="farmer-form-label" for="longitude">
              <i data-lucide="crosshair" style="width:13px;height:13px;color:var(--sky-400);vertical-align:middle;"></i>
              Longitude
            </label>
            <input type="number" step="0.000001" class="farmer-form-input" id="longitude" name="longitude" value="<?= htmlspecialchars($lng) ?>" placeholder="e.g. 67.001100" oninput="syncMapFromInputs()" style="font-family: monospace;">
          </div>
        </div>

        <div style="font-size: 0.78125rem; color: var(--slate-400); margin-top: auto; display: flex; align-items: flex-start; gap: 0.4rem;">
          <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;margin-top:1px;"></i>
          <span>Type latitude/longitude manually or click the map to reposition. Double-click opens fullscreen map for precise pinning.</span>
        </div>
      </div>
    </div>

    <!-- Assigned Market Stalls & Bays Overview -->
    <div class="farmer-card" style="margin-bottom: 2rem;">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="building" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
            <span>Registered Market Stall Locations &amp; Bays</span>
          </h3>
          <span class="farmer-card-subtitle">Physical farmers markets where your stall is officially assigned</span>
        </div>
      </div>

      <?php if (empty($assignedStalls)): ?>
        <p style="color: var(--slate-400); font-size: 0.875rem; text-align: center; padding: 2rem 0;">
          No physical market stall assignments yet. The MarketLink platform admin assigns physical bays upon verification.
        </p>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
          <?php foreach ($assignedStalls as $st): ?>
            <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <strong style="color: var(--text-primary); font-size: 1rem;"><?= htmlspecialchars($st['market_name']) ?></strong>
                <span class="farmer-badge-role" style="font-size: 0.65rem;">Active</span>
              </div>
              <div style="font-size: 0.84375rem; color: var(--slate-300); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                <i data-lucide="map-pin" style="width: 14px; height: 14px; color: var(--primary-500);"></i>
                <span><?= htmlspecialchars($st['stall_number_location'] ?: 'Produce Bay') ?></span>
              </div>
              <div style="font-size: 0.8125rem; color: var(--primary-400); font-weight: 600;">
                📅 Operating Days: <?= htmlspecialchars($st['operating_days'] ?: 'Saturday, Sunday') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Save Changes Action Bar -->
    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 2rem;">
      <button type="submit" class="farmer-btn-primary" id="saveProfileBtn" style="padding: 0.75rem 2rem; font-size: 0.9375rem;">
        <i data-lucide="check" style="width: 17px; height: 17px;"></i>
        <span>Save Farm Stall Profile</span>
      </button>
    </div>
  </form>

</div>

<!-- ===== FULLSCREEN MAP MODAL ===== -->
<div id="fullscreenMapModal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.92); flex-direction:column;">
  <!-- Modal Toolbar -->
  <div style="background:var(--bg-surface); border-bottom:1px solid var(--border-color); padding:0.85rem 1.25rem; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; z-index:10001;">
    <div style="display:flex;align-items:center;gap:0.75rem;">
      <i data-lucide="map-pin" style="width:20px;height:20px;color:var(--primary-500);"></i>
      <div>
        <div style="font-family:var(--font-heading);font-weight:700;color:var(--text-primary);font-size:1rem;">Select Your Farm Location</div>
        <div style="font-size:0.75rem;color:var(--slate-400);">Click anywhere on the map to drop a pin at your exact farm location</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:0.75rem;">
      <div style="background:var(--bg-surface-elevated);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0.5rem 1rem;display:flex;align-items:center;gap:0.65rem;font-family:monospace;font-size:0.8125rem;color:var(--text-primary);">
        <i data-lucide="crosshair" style="width:14px;height:14px;color:var(--primary-500);"></i>
        <span id="fullscreenLatDisplay">Lat: --</span>
        &nbsp;|&nbsp;
        <span id="fullscreenLngDisplay">Lng: --</span>
      </div>
      <button type="button" onclick="confirmFullscreenLocation()" class="farmer-btn-primary" style="padding:0.5rem 1.15rem;font-size:0.875rem;">
        <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
        <span>Confirm Location</span>
      </button>
      <button type="button" onclick="closeFullscreenMap()" style="background:transparent;border:1px solid var(--border-color);color:var(--slate-400);padding:0.5rem 0.85rem;border-radius:var(--radius-md);cursor:pointer;font-size:0.875rem;display:flex;align-items:center;gap:0.4rem;">
        <i data-lucide="x" style="width:15px;height:15px;"></i>
        Close
      </button>
    </div>
  </div>
  <!-- Fullscreen Map Container -->
  <div id="fullscreenMap" style="flex:1;width:100%;min-height:0;"></div>
</div>

<!-- Leaflet Map Initialization Script -->
<script>
let map, marker, fullMap, fullMarker;
let pendingFullLat = null, pendingFullLng = null;

document.addEventListener('DOMContentLoaded', function() {
  const initialLat = <?= $lat ?>;
  const initialLng = <?= $lng ?>;

  // ---- Small Profile Map ----
  map = L.map('farmMap', { zoomControl: true }).setView([initialLat, initialLng], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
  marker.bindPopup('<b><?= htmlspecialchars(addslashes($stallName)) ?></b><br>Drag marker or click map to update.').openPopup();

  // Drag end
  marker.on('dragend', function() {
    const c = marker.getLatLng();
    updateCoords(c.lat, c.lng);
  });

  // Single click on map = move marker
  map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    updateCoords(e.latlng.lat, e.latlng.lng);
  });

  // Double-click on map = open fullscreen
  map.on('dblclick', function(e) {
    L.DomEvent.stopPropagation(e);
    openFullscreenMap(e.latlng.lat, e.latlng.lng);
  });
});

function updateCoords(lat, lng) {
  document.getElementById('latitude').value = parseFloat(lat).toFixed(6);
  document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
}

// Sync map when user types in the lat/lng fields
function syncMapFromInputs() {
  const lat = parseFloat(document.getElementById('latitude').value);
  const lng = parseFloat(document.getElementById('longitude').value);
  if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
    const latlng = L.latLng(lat, lng);
    marker.setLatLng(latlng);
    map.setView(latlng, map.getZoom());
  }
}

// ---- Fullscreen Map ----
function openFullscreenMap(initLat, initLng) {
  const modal = document.getElementById('fullscreenMapModal');
  modal.style.display = 'flex';

  // Use current map coords if not supplied
  if (initLat == null) {
    initLat = parseFloat(document.getElementById('latitude').value) || <?= $lat ?>;
    initLng = parseFloat(document.getElementById('longitude').value) || <?= $lng ?>;
  }

  if (!fullMap) {
    fullMap = L.map('fullscreenMap').setView([initLat, initLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(fullMap);

    fullMarker = L.marker([initLat, initLng], { draggable: true }).addTo(fullMap);
    fullMarker.bindPopup('<b>Your Farm Location</b><br>Drag to fine-tune position.');

    fullMarker.on('dragend', function() {
      const c = fullMarker.getLatLng();
      pendingFullLat = c.lat;
      pendingFullLng = c.lng;
      updateFullscreenDisplay(c.lat, c.lng);
    });

    fullMap.on('click', function(e) {
      fullMarker.setLatLng(e.latlng);
      pendingFullLat = e.latlng.lat;
      pendingFullLng = e.latlng.lng;
      updateFullscreenDisplay(e.latlng.lat, e.latlng.lng);
    });
  } else {
    const latlng = L.latLng(initLat, initLng);
    fullMarker.setLatLng(latlng);
    fullMap.setView(latlng, 14);
    fullMap.invalidateSize();
  }

  pendingFullLat = initLat;
  pendingFullLng = initLng;
  updateFullscreenDisplay(initLat, initLng);

  // Force Leaflet to recalculate tile sizes after modal display
  setTimeout(() => fullMap.invalidateSize(), 100);
}

function updateFullscreenDisplay(lat, lng) {
  document.getElementById('fullscreenLatDisplay').textContent = 'Lat: ' + parseFloat(lat).toFixed(6);
  document.getElementById('fullscreenLngDisplay').textContent = 'Lng: ' + parseFloat(lng).toFixed(6);
}

function confirmFullscreenLocation() {
  if (pendingFullLat !== null && pendingFullLng !== null) {
    updateCoords(pendingFullLat, pendingFullLng);
    if (marker) marker.setLatLng([pendingFullLat, pendingFullLng]);
    if (map) map.setView([pendingFullLat, pendingFullLng], 14);
    FarmerApp.showToast('success', 'Location Set', 'GPS coordinates updated from fullscreen map.');
  }
  closeFullscreenMap();
}

function closeFullscreenMap() {
  document.getElementById('fullscreenMapModal').style.display = 'none';
}

// Close fullscreen on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeFullscreenMap();
});

// Profile Form Submit
function submitProfileForm(e) {
  e.preventDefault();
  const btn = document.getElementById('saveProfileBtn');
  btn.disabled = true;

  const formData = new FormData(document.getElementById('profileForm'));

  fetch('<?= BASE_URL ?>/farmer/api/save_profile.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Profile Updated', data.message);
      setTimeout(() => location.reload(), 800);
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Could not update profile.');
    }
  })
  .catch(() => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Network Error', 'Please check server connection.');
  });
}

// Profile Photo Upload
function previewAndUploadPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  // Local preview
  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
    preview.style.border = '3px solid var(--primary-500)';
  };
  reader.readAsDataURL(file);

  // Upload
  const progress = document.getElementById('photoUploadProgress');
  const bar = document.getElementById('photoProgressBar');
  const txt = document.getElementById('photoProgressText');
  progress.style.display = 'block';

  const formData = new FormData();
  formData.append('profile_photo', file);

  // Simulate progress for UX
  let prog = 0;
  const progInterval = setInterval(() => {
    prog = Math.min(prog + 15, 85);
    bar.style.width = prog + '%';
  }, 120);

  fetch('<?= BASE_URL ?>/farmer/api/upload_profile_photo.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    clearInterval(progInterval);
    bar.style.width = '100%';
    if (data.status === 'success') {
      txt.textContent = '✓ Photo uploaded successfully!';
      txt.style.color = 'var(--primary-400)';
      FarmerApp.showToast('success', 'Photo Saved', 'Your profile photo has been updated.');
      setTimeout(() => { progress.style.display = 'none'; bar.style.width = '0%'; }, 2500);
    } else {
      txt.textContent = '✗ ' + (data.message || 'Upload failed');
      txt.style.color = '#f87171';
      FarmerApp.showToast('error', 'Upload Failed', data.message || 'Could not save photo.');
    }
  })
  .catch(() => {
    clearInterval(progInterval);
    txt.textContent = '✗ Network error — try again';
    txt.style.color = '#f87171';
    FarmerApp.showToast('error', 'Error', 'Server connection failed.');
  });
}
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
