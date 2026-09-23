<?php
/**
 * MarketLink - Browse Markets & Farmers (Interactive Map)
 * Fully satisfies SRS requirements: OpenStreetMap (Leaflet) markers, stall lists, operating days, directions.
 */

$pageTitle = 'Markets & Farmers Map';
$activePage = 'markets';
require_once __DIR__ . '/includes/customer_header.php';

// Fetch all active markets
$mStmt = $pdo->query("SELECT * FROM markets WHERE status = 'active' ORDER BY market_id ASC");
$markets = $mStmt->fetchAll();

// Fetch stalls & farmers grouped by market
$stallStmt = $pdo->query("SELECT fms.*, fp.stall_name, fp.contact_person, fp.business_phone, fp.description as farmer_desc,
                                 m.market_id, m.market_name,
                                 (SELECT COUNT(*) FROM favorite_farmers fav WHERE fav.customer_id = {$currentUserId} AND fav.farmer_id = fp.farmer_id) as is_fav
                          FROM farmer_market_stalls fms
                          JOIN farmer_profiles fp ON fms.farmer_id = fp.farmer_id
                          JOIN markets m ON fms.market_id = m.market_id
                          WHERE fms.status = 'active'
                          ORDER BY fms.stall_number_location ASC");
$allStalls = $stallStmt->fetchAll();

// Group stalls by market_id
$stallsByMarket = [];
foreach ($allStalls as $st) {
    $stallsByMarket[$st['market_id']][] = $st;
}
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>📍</span> Farmers Markets & Stalls
    </h1>
    <p class="page-subheading">
      Explore local weekend markets, locate farmer stalls on the map, and get pickup directions
    </p>
  </div>
</div>

<!-- Embedded Interactive OpenStreetMap (Leaflet) -->
<div class="market-map-container" id="marketMap"></div>

<!-- JSON payload for Leaflet markers in customer.js -->
<script type="application/json" id="marketLocationsData">
<?= json_encode($markets, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>
</script>

<!-- Markets & Participating Farmers Grid -->
<div class="page-header-row" style="margin-top:2rem; margin-bottom:1.25rem;">
  <div>
    <h2 class="page-heading" style="font-size:1.35rem;">
      <span>🏡</span> Participating Farmers Markets (<?= count($markets) ?> Locations)
    </h2>
    <p class="page-subheading">Browse operating days, directions, and verified growers at each pickup destination</p>
  </div>
</div>

<div class="market-cards-grid">
  <?php foreach ($markets as $m): ?>
    <?php $stalls = $stallsByMarket[$m['market_id']] ?? []; ?>
    <div class="market-info-card">
      <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.75rem;">
        <div>
          <span style="font-size:0.7rem; font-weight:700; color:var(--emerald-400); text-transform:uppercase; letter-spacing:0.05em;">Verified Market Location</span>
          <h3 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; color:var(--text-primary); margin:0.25rem 0;">
            <?= htmlspecialchars($m['market_name']) ?>
          </h3>
        </div>
        <span style="background:rgba(16,185,129,0.12); color:var(--emerald-400); padding:0.25rem 0.6rem; border-radius:var(--radius-full); font-size:0.72rem; font-weight:700;">
          Active
        </span>
      </div>

      <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:0.85rem; line-height:1.45;">
        📍 <?= htmlspecialchars($m['address']) ?>, <?= htmlspecialchars($m['city']) ?>
      </div>

      <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.8rem;">
        <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
          <span style="color:var(--text-muted);">Operating Days:</span>
          <strong style="color:var(--text-primary);"><?= htmlspecialchars($m['operating_days']) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between;">
          <span style="color:var(--text-muted);">Pickup Windows:</span>
          <strong style="color:var(--emerald-400);"><?= htmlspecialchars($m['operating_hours']) ?></strong>
        </div>
      </div>

      <!-- Participating Farmers at this Market -->
      <div style="margin-bottom:1.25rem; flex:1;">
        <div style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.65rem;">
          Growers at this Market (<?= count($stalls) ?>)
        </div>

        <?php if (!empty($stalls)): ?>
          <div style="display:flex; flex-direction:column; gap:0.5rem;">
            <?php foreach ($stalls as $st): ?>
              <div style="background:var(--bg-surface-elevated); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:0.65rem 0.85rem; display:flex; align-items:center; justify-content:space-between;">
                <div>
                  <div style="font-weight:700; font-size:0.85rem; color:var(--text-primary);">
                    <?= htmlspecialchars($st['stall_name']) ?>
                  </div>
                  <div style="font-size:0.72rem; color:var(--text-muted);">
                    📍 <?= htmlspecialchars($st['stall_number_location'] ?? 'Stall') ?> • <?= htmlspecialchars($st['contact_person']) ?>
                  </div>
                </div>

                <div style="display:flex; align-items:center; gap:0.4rem;">
                  <button type="button" class="product-fav-btn js-fav-toggle <?= $st['is_fav'] ? 'is-favorite' : '' ?>" 
                          data-type="farmer" data-id="<?= $st['farmer_id'] ?>" style="position:static; width:28px; height:28px; font-size:0.9rem;"
                          title="Save Favorite Farmer" aria-label="Save Favorite Farmer">
                    <?= $st['is_fav'] ? '❤️' : '🤍' ?>
                  </button>
                  <a href="<?= BASE_URL ?>/customer/products.php?market_id=<?= $m['market_id'] ?>" 
                     style="background:var(--emerald-500); color:#fff; padding:0.35rem 0.65rem; border-radius:var(--radius-sm); font-size:0.72rem; font-weight:700; text-decoration:none;">
                    Stall Items ➔
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="font-size:0.8rem; color:var(--text-muted); font-style:italic;">No stalls assigned for this market day yet.</p>
        <?php endif; ?>
      </div>

      <!-- Action Links: Directions & Stalls -->
      <div style="display:flex; gap:0.65rem; padding-top:1rem; border-top:1px solid var(--border-subtle);">
        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $m['latitude'] ?>,<?= $m['longitude'] ?>" 
           target="_blank" rel="noopener noreferrer" class="btn-secondary" style="flex:1; text-align:center; text-decoration:none; padding:0.5rem; font-size:0.8rem; display:flex; align-items:center; justify-content:center; gap:0.35rem;">
          <span>🗺️</span> Get Directions
        </a>
        <a href="<?= BASE_URL ?>/customer/products.php?market_id=<?= $m['market_id'] ?>" 
           class="btn-primary" style="flex:1; text-align:center; text-decoration:none; padding:0.5rem; font-size:0.8rem; display:flex; align-items:center; justify-content:center; gap:0.35rem;">
          <span>🛒</span> Shop Market
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
