<?php
/**
 * MarketLink - Stall Detail Page
 * Shows: stall hero, map, reviews, published products, pickup slots, reserve CTA
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$pdo = getDBConnection();

$farmer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($farmer_id <= 0) { header('Location: ' . BASE_URL . '/'); exit; }

// ── Stall / Farmer Profile ─────────────────────────────────────────────────────
$stallStmt = $pdo->prepare("
    SELECT fp.*, u.username, u.email, u.profile_image, u.phone_number, u.created_at as member_since
    FROM farmer_profiles fp
    JOIN users u ON fp.farmer_id = u.user_id
    WHERE fp.farmer_id = ? AND fp.approval_status = 'approved'
    LIMIT 1
");
$stallStmt->execute([$farmer_id]);
$stall = $stallStmt->fetch();
if (!$stall) { header('Location: ' . BASE_URL . '/'); exit; }

// ── Market / Location ──────────────────────────────────────────────────────────
$marketStmt = $pdo->prepare("
    SELECT fms.stall_number_location, fms.operating_days, fms.stall_id,
           m.market_name, m.address as market_address, m.city, m.operating_hours,
           m.latitude as market_lat, m.longitude as market_lng
    FROM farmer_market_stalls fms
    JOIN markets m ON fms.market_id = m.market_id
    WHERE fms.farmer_id = ? AND fms.status = 'active'
    LIMIT 1
");
$marketStmt->execute([$farmer_id]);
$marketInfo = $marketStmt->fetch();

$mapLat = !empty($stall['latitude'])  ? (float)$stall['latitude']  : (!empty($marketInfo['market_lat'])  ? (float)$marketInfo['market_lat']  : 31.5204);
$mapLng = !empty($stall['longitude']) ? (float)$stall['longitude'] : (!empty($marketInfo['market_lng']) ? (float)$marketInfo['market_lng'] : 74.3587);

// ── Reviews ────────────────────────────────────────────────────────────────────
$reviewsStmt = $pdo->prepare("
    SELECT fr.rating, fr.review_comment, fr.farmer_response, fr.created_at,
           cp.full_name as customer_name, u.profile_image as customer_avatar
    FROM farmer_reviews fr
    JOIN customer_profiles cp ON fr.customer_id = cp.customer_id
    JOIN users u ON cp.customer_id = u.user_id
    WHERE fr.farmer_id = ? AND fr.is_moderated = 1
    ORDER BY fr.created_at DESC LIMIT 12
");
$reviewsStmt->execute([$farmer_id]);
$reviews = $reviewsStmt->fetchAll();

$statsStmt = $pdo->prepare("
    SELECT COUNT(*) as total_reviews, ROUND(AVG(rating),2) as avg_rating,
           SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) as r5,
           SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) as r4,
           SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) as r3,
           SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) as r2,
           SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) as r1
    FROM farmer_reviews WHERE farmer_id = ? AND is_moderated = 1
");
$statsStmt->execute([$farmer_id]);
$rs = $statsStmt->fetch();
$avgRating    = (float)($rs['avg_rating'] ?? 0);
$totalReviews = (int)($rs['total_reviews'] ?? 0);

// ── Products ───────────────────────────────────────────────────────────────────
$productsStmt = $pdo->prepare("
    SELECT p.product_id, p.product_name, p.description, p.unit, p.image_url,
           pc.category_name,
           MIN(wi.price) as min_price, MAX(wi.price) as max_price,
           SUM(wi.is_available) as available_days
    FROM products p
    JOIN product_categories pc ON p.category_id = pc.category_id
    LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id
    WHERE p.farmer_id = ?
    GROUP BY p.product_id ORDER BY p.created_at DESC
");
$productsStmt->execute([$farmer_id]);
$products = $productsStmt->fetchAll();

// ── Pickup Slots ───────────────────────────────────────────────────────────────
$slotsStmt = $pdo->prepare("
    SELECT ps.pickup_slot_id, ps.slot_date, ps.start_time, ps.end_time,
           ps.max_orders, ps.status,
           (ps.max_orders - COALESCE(COUNT(o.order_id),0)) as remaining
    FROM pickup_slots ps
    JOIN farmer_market_stalls fms ON ps.stall_id = fms.stall_id
    LEFT JOIN orders o ON ps.pickup_slot_id = o.pickup_slot_id
        AND o.order_status NOT IN ('cancelled','declined')
    WHERE fms.farmer_id = ? AND ps.slot_date >= CURDATE() AND ps.status = 'available'
    GROUP BY ps.pickup_slot_id
    ORDER BY ps.slot_date ASC, ps.start_time ASC LIMIT 8
");
$slotsStmt->execute([$farmer_id]);
$pickupSlots = $slotsStmt->fetchAll();

// Min price helper
$minPriceVal = 0;
$allPrices = array_filter(array_column($products, 'min_price'), fn($v) => $v > 0);
if (!empty($allPrices)) $minPriceVal = min($allPrices);

$pageTitle = htmlspecialchars($stall['stall_name']) . ' — Local Producer Stall';
require_once __DIR__ . '/includes/header.php';
?>
<!-- Leaflet CSS — loaded BEFORE any styles so our overrides win -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
/* ═══════════════════════════════════════════════════════════════════════════
   STALL DETAIL — uses every token from main.css + home.css exactly
   ═══════════════════════════════════════════════════════════════════════════ */

/* ── Hero ──────────────────────────────────────────────────────────────────── */
.sd-hero {
  padding-top: 88px; /* navbar height */
  min-height: 380px;
  position: relative;
  display: flex;
  align-items: flex-end;
  background:
    radial-gradient(ellipse 65% 70% at 25% 60%, rgba(56,189,248,.15) 0%, transparent 55%),
    radial-gradient(ellipse 55% 65% at 78% 25%, rgba(74,222,128,.13) 0%, transparent 55%),
    linear-gradient(150deg, #051424 0%, #081d2e 35%, #08221b 75%, #051914 100%);
  border-bottom: 1px solid var(--border-color);
  overflow: hidden;
}
.sd-hero::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--border-hover), transparent);
}
.sd-hero-inner {
  position: relative; z-index: 2;
  max-width: 1280px; margin: 0 auto;
  padding: 2.5rem 2rem 3rem;
  width: 100%;
}
.sd-breadcrumb {
  display: flex; align-items: center; gap: .5rem;
  font-size: .8rem; color: var(--text-muted);
  margin-bottom: 2rem;
}
.sd-breadcrumb a {
  color: var(--primary-400); text-decoration: none;
  transition: color var(--transition-fast);
}
.sd-breadcrumb a:hover { color: var(--primary-300); }
.sd-breadcrumb .sep { opacity: .4; }

.sd-hero-flex {
  display: flex; align-items: flex-start;
  gap: 2rem; flex-wrap: wrap;
}
.sd-avatar-wrap {
  width: 88px; height: 88px; flex-shrink: 0;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-600), var(--sky-500));
  border: 3px solid var(--border-hover);
  box-shadow: 0 0 30px rgba(74,222,128,.35), var(--shadow-glow-green);
  display: flex; align-items: center; justify-content: center;
  font-size: 2.4rem; overflow: hidden;
}
.sd-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }

.sd-hero-meta { flex: 1; min-width: 0; }
.sd-verified {
  display: inline-flex; align-items: center; gap: .4rem;
  background: rgba(74,222,128,.12);
  border: 1px solid rgba(74,222,128,.4);
  color: var(--primary-300);
  font-family: var(--font-heading);
  font-size: .72rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .08em;
  padding: .28rem .85rem; border-radius: var(--radius-full);
  margin-bottom: .9rem;
}
.sd-title {
  font-family: var(--font-heading);
  font-size: clamp(1.9rem, 3.5vw, 2.9rem);
  font-weight: 800;
  line-height: 1.15; letter-spacing: -.02em;
  color: var(--text-primary);
  margin: 0 0 .5rem;
}
.sd-subtitle {
  font-size: .9375rem;
  color: var(--text-secondary);
  margin-bottom: 1rem;
}
.sd-subtitle strong { color: var(--primary-400); }

.sd-hero-stats {
  display: flex; align-items: center;
  gap: 1.5rem; flex-wrap: wrap;
}
.sd-stat {
  display: flex; align-items: center; gap: .4rem;
  font-size: .8125rem; color: var(--text-muted);
  font-family: var(--font-heading); font-weight: 600;
}
.sd-stat strong { color: var(--text-primary); font-weight: 800; }
.sd-stat .star { color: var(--accent-gold); }
.sd-stat .dot {
  width: 4px; height: 4px; border-radius: 50%;
  background: var(--border-hover); flex-shrink: 0;
}

/* Reserve hero CTA */
.sd-hero-cta {
  flex-shrink: 0; align-self: center;
  margin-left: auto;
}
.btn-sd-reserve,
a.btn-sd-reserve,
button.btn-sd-reserve {
  display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
  font-family: var(--font-heading); font-weight: 800; font-size: 1rem;
  padding: .9rem 2rem; border-radius: var(--radius-md);
  text-decoration: none; border: 1px solid rgba(255,255,255,.25) !important;
  cursor: pointer;
  box-shadow: 0 6px 24px rgba(22,163,74,.4) !important;
  transition: all var(--transition-base);
  white-space: nowrap;
}
.btn-sd-reserve:hover,
a.btn-sd-reserve:hover,
button.btn-sd-reserve:hover {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 10px 32px rgba(34,197,94,.5) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

/* ── Page Body ────────────────────────────────────────────────────────────── */
.sd-body {
  background: var(--bg-primary);
  background-image:
    radial-gradient(ellipse at 10% 20%, rgba(56,189,248,.08) 0%, transparent 45%),
    radial-gradient(ellipse at 90% 80%, rgba(74,222,128,.07) 0%, transparent 45%);
  background-attachment: fixed;
  min-height: 100vh;
  padding-bottom: 6rem;
}

.sd-grid {
  max-width: 1280px; margin: 0 auto;
  padding: 2.5rem 2rem;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 2rem;
  align-items: start;
}
@media (max-width: 1080px) {
  .sd-grid { grid-template-columns: 1fr; }
  .sd-hero-cta { margin-left: 0; }
}
@media (max-width: 640px) {
  .sd-hero-flex { flex-direction: column; gap: 1.25rem; }
  .sd-grid { padding: 1.25rem 1rem; gap: 1.25rem; }
}

/* ── Panel Cards ──────────────────────────────────────────────────────────── */
.sd-panel {
  background: var(--bg-card);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 2rem;
  margin-bottom: 2rem;
  transition: border-color var(--transition-base);
}
.sd-panel:last-child { margin-bottom: 0; }
.sd-panel:hover { border-color: rgba(56,189,248,.3); }

.sd-panel-title {
  font-family: var(--font-heading);
  font-size: 1.15rem; font-weight: 800;
  color: var(--text-primary);
  margin: 0 0 1.5rem;
  display: flex; align-items: center; gap: .6rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}
.sd-panel-title .title-icon { font-size: 1.1rem; }

/* ── MAP ─────────────────────────────────────────────────────────────────── */
#sd-map {
  width: 100%;
  height: 320px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
  overflow: hidden;
  background: #061524; /* prevent flash */
}
.sd-map-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-top: 1.25rem;
}
.sd-map-item {
  display: flex; align-items: flex-start; gap: .75rem;
  padding: .85rem 1rem;
  background: var(--bg-surface);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  transition: border-color var(--transition-fast);
}
.sd-map-item:hover { border-color: var(--border-hover); }
.sd-map-icon { font-size: 1.25rem; flex-shrink: 0; margin-top: 1px; }
.sd-map-label { font-size: .72rem; color: var(--text-muted); margin-bottom: 2px; font-family: var(--font-heading); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.sd-map-val  { font-size: .875rem; color: var(--text-primary); font-weight: 600; }
.sd-map-sub  { font-size: .775rem; color: var(--text-muted); margin-top: 1px; }
.sd-gmaps-link {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: .775rem; color: var(--sky-400);
  text-decoration: none; margin-top: .5rem;
  transition: color var(--transition-fast);
}
.sd-gmaps-link:hover { color: var(--sky-300); }

/* Leaflet popup — dark theme match */
.leaflet-popup-content-wrapper {
  background: var(--bg-surface-elevated, #13392f) !important;
  border: 1px solid var(--border-hover) !important;
  border-radius: var(--radius-md) !important;
  box-shadow: 0 20px 40px rgba(0,0,0,.5) !important;
  color: var(--text-primary) !important;
}
.leaflet-popup-tip-container .leaflet-popup-tip {
  background: var(--bg-surface-elevated, #13392f) !important;
}
.leaflet-popup-close-button { color: var(--text-muted) !important; }
.leaflet-container { font-family: var(--font-body) !important; }

/* ── PRODUCTS ────────────────────────────────────────────────────────────── */
.sd-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
  gap: 1.25rem;
}
.sd-product-card {
  background: var(--bg-surface);
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
  cursor: pointer;
  transition: all var(--transition-base);
  position: relative;
}
.sd-product-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--primary-500), var(--sky-400));
  transform: scaleX(0); transform-origin: left;
  transition: transform var(--transition-base);
}
.sd-product-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -10px rgba(0,0,0,.5), 0 0 30px rgba(74,222,128,.12);
}
.sd-product-card:hover::before { transform: scaleX(1); }

.sd-prod-img {
  height: 148px;
  background: linear-gradient(135deg, var(--bg-surface-elevated), var(--bg-secondary));
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; position: relative; overflow: hidden;
}
.sd-prod-img img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform var(--transition-smooth);
}
.sd-product-card:hover .sd-prod-img img { transform: scale(1.06); }
.sd-prod-cat {
  position: absolute; top: .6rem; left: .6rem;
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  color: var(--sky-300);
  font-family: var(--font-heading);
  font-size: .68rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .05em;
  padding: .22rem .65rem; border-radius: var(--radius-full);
  border: 1px solid var(--border-sky);
}
.sd-prod-body { padding: 1rem; }
.sd-prod-name {
  font-family: var(--font-heading);
  font-weight: 700; font-size: .9375rem;
  color: var(--text-primary);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  margin-bottom: .2rem;
}
.sd-prod-unit { font-size: .775rem; color: var(--text-muted); margin-bottom: .6rem; }
.sd-prod-price {
  font-family: var(--font-heading);
  font-size: 1rem; font-weight: 800;
  color: var(--primary-400);
}
.sd-prod-price .unit-small { font-size: .72rem; color: var(--text-muted); font-weight: 400; }
.sd-prod-avail {
  display: inline-flex; align-items: center; gap: .3rem;
  margin-top: .5rem;
  font-size: .7rem; font-weight: 700;
  color: var(--primary-300);
  background: rgba(34,197,94,.1);
  border: 1px solid rgba(74,222,128,.25);
  padding: .15rem .6rem; border-radius: var(--radius-full);
}
.sd-empty-state {
  text-align: center; padding: 3rem 1rem; grid-column: 1/-1;
}
.sd-empty-icon { font-size: 3.5rem; margin-bottom: .75rem; opacity: .6; }
.sd-empty-text { color: var(--text-muted); font-size: .9375rem; }

/* ── REVIEWS ────────────────────────────────────────────────────────────── */
.sd-rating-summary {
  display: flex; align-items: center;
  gap: 2.5rem; flex-wrap: wrap;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}
.sd-big-score { text-align: center; flex-shrink: 0; }
.sd-score-num {
  font-family: var(--font-heading);
  font-size: 4rem; font-weight: 800; line-height: 1;
  background: linear-gradient(135deg, var(--primary-300), var(--sky-300));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.sd-score-stars {
  display: flex; justify-content: center; gap: 3px;
  margin: .35rem 0 .3rem;
}
.sd-score-stars .s { font-size: 1.1rem; }
.sd-score-total { font-size: .8rem; color: var(--text-muted); font-weight: 600; }
.sd-rating-bars { flex: 1; min-width: 200px; }
.sd-bar-row {
  display: flex; align-items: center; gap: .75rem;
  margin-bottom: .45rem;
}
.sd-bar-lbl { font-size: .8rem; color: var(--text-muted); width: 18px; text-align: right; font-family: var(--font-heading); font-weight: 700; }
.sd-bar-track {
  flex: 1; height: 7px;
  background: var(--bg-surface);
  border-radius: var(--radius-full); overflow: hidden;
  border: 1px solid var(--border-color);
}
.sd-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-500), var(--sky-400));
  border-radius: var(--radius-full);
}
.sd-bar-cnt { font-size: .75rem; color: var(--text-muted); width: 22px; }

.sd-reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px,1fr));
  gap: 1.25rem;
}
.sd-review-card {
  background: var(--bg-surface);
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.35rem;
  transition: all var(--transition-base);
}
.sd-review-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0,0,0,.3);
}
.sd-review-top {
  display: flex; align-items: center; gap: .75rem;
  margin-bottom: 1rem;
}
.sd-rev-avatar {
  width: 42px; height: 42px; border-radius: 50%;
  background: linear-gradient(135deg, var(--bg-surface-elevated), var(--bg-secondary));
  border: 2px solid var(--border-color);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; overflow: hidden; flex-shrink: 0;
}
.sd-rev-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sd-rev-name { font-family: var(--font-heading); font-weight: 700; font-size: .875rem; color: var(--text-primary); }
.sd-rev-date { font-size: .75rem; color: var(--text-muted); margin-top: 1px; }
.sd-rev-stars { display: flex; gap: 2px; margin-bottom: .65rem; }
.sd-rev-stars .s { font-size: .875rem; }
.sd-rev-text { font-size: .875rem; color: var(--text-secondary); line-height: 1.6; }
.sd-rev-reply {
  margin-top: 1rem;
  background: rgba(74,222,128,.07);
  border-left: 3px solid var(--primary-500);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  padding: .65rem .9rem;
  font-size: .8125rem; color: var(--text-secondary); line-height: 1.5;
}
.sd-rev-reply strong { color: var(--primary-400); }

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sd-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
.sd-sb-card {
  background: var(--bg-card);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  transition: border-color var(--transition-base);
}
.sd-sb-card:hover { border-color: rgba(56,189,248,.3); }
.sd-sb-title {
  font-family: var(--font-heading);
  font-size: 1rem; font-weight: 800;
  color: var(--text-primary);
  margin: 0 0 1.15rem;
  display: flex; align-items: center; gap: .5rem;
}

/* Reserve card */
.sd-reserve-card {
  background:
    radial-gradient(circle at top right, rgba(74,222,128,.15) 0%, transparent 60%),
    var(--bg-card);
  border-color: rgba(74,222,128,.35);
  position: relative; overflow: hidden;
}
.sd-reserve-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--primary-500), var(--sky-400));
}
.sd-price-hint {
  font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.1rem;
  line-height: 1.5;
}
.sd-price-hint strong { color: var(--primary-400); font-size: 1rem; }
.btn-sd-main,
a.btn-sd-main,
button.btn-sd-main {
  display: flex; align-items: center; justify-content: center; gap: .55rem;
  width: 100%; padding: .95rem 1.25rem;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
  font-family: var(--font-heading); font-weight: 800; font-size: 1rem;
  border: 1px solid rgba(255,255,255,.2) !important;
  border-radius: var(--radius-md);
  cursor: pointer; text-decoration: none; text-align: center;
  box-shadow: 0 6px 20px rgba(22,163,74,.35) !important;
  transition: all var(--transition-base);
  margin-bottom: .75rem;
}
.btn-sd-main:hover,
a.btn-sd-main:hover,
button.btn-sd-main:hover {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(34,197,94,.5) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}
.btn-sd-ghost,
a.btn-sd-ghost,
button.btn-sd-ghost {
  display: flex; align-items: center; justify-content: center; gap: .55rem;
  width: 100%; padding: .85rem 1.25rem;
  background: rgba(255,255,255,.06) !important;
  color: #f0fdf4 !important;
  -webkit-text-fill-color: #f0fdf4 !important;
  font-family: var(--font-heading); font-weight: 700; font-size: .9375rem;
  border: 1.5px solid var(--border-color) !important;
  border-radius: var(--radius-md);
  cursor: pointer; text-decoration: none; text-align: center;
  transition: all var(--transition-base);
  margin-bottom: .75rem;
}
.btn-sd-ghost:hover,
a.btn-sd-ghost:hover,
button.btn-sd-ghost:hover {
  border-color: #4ade80 !important;
  background: rgba(34,197,94,.12) !important;
  color: #4ade80 !important;
  -webkit-text-fill-color: #4ade80 !important;
}
.sd-reserve-note {
  text-align: center; font-size: .75rem;
  color: var(--text-muted);
}

/* Pickup Slots */
.sd-slot-item {
  background: var(--bg-surface);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: .85rem 1rem;
  margin-bottom: .6rem;
  display: flex; align-items: center;
  justify-content: space-between; gap: .75rem;
  transition: border-color var(--transition-fast);
}
.sd-slot-item:hover { border-color: var(--border-hover); }
.sd-slot-date { font-family: var(--font-heading); font-weight: 700; font-size: .8125rem; color: var(--text-primary); }
.sd-slot-time { font-size: .75rem; color: var(--text-muted); margin-top: 2px; }
.sd-slot-pill {
  font-size: .72rem; font-weight: 800;
  font-family: var(--font-heading);
  padding: .22rem .65rem; border-radius: var(--radius-full);
  white-space: nowrap;
}
.sd-slot-pill.open     { background: rgba(34,197,94,.12); color: var(--primary-300); border: 1px solid rgba(74,222,128,.3); }
.sd-slot-pill.limited  { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.3); }
.sd-slot-pill.full     { background: rgba(239,68,68,.1); color: #fca5a5; border: 1px solid rgba(239,68,68,.2); }

/* About Details List */
.sd-detail-list { list-style: none; padding: 0; margin: 0; }
.sd-detail-list li {
  display: flex; align-items: flex-start; gap: .75rem;
  padding: .65rem 0;
  border-bottom: 1px solid var(--border-color);
  font-size: .875rem;
}
.sd-detail-list li:last-child { border-bottom: none; }
.sd-dl-icon { font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
.sd-dl-label { font-size: .72rem; color: var(--text-muted); font-family: var(--font-heading); font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 1px; }
.sd-dl-val { color: var(--text-primary); font-weight: 600; }

/* Dark mode map styling */
[data-theme="dark"] #sd-map .leaflet-tile-pane,
:root:not([data-theme="light"]) #sd-map .leaflet-tile-pane {
  filter: brightness(0.68) invert(1) contrast(2.8) hue-rotate(200deg) saturate(0.35) brightness(0.85);
}
[data-theme="light"] #sd-map .leaflet-tile-pane {
  filter: none;
}

/* ==========================================================================
   Light Theme Overrides — High Contrast & Flawless Typography
   ========================================================================== */
[data-theme="light"] .sd-hero {
  background:
    radial-gradient(ellipse 65% 70% at 25% 60%, rgba(14,165,233,.12) 0%, transparent 55%),
    radial-gradient(ellipse 55% 65% at 78% 25%, rgba(34,197,94,.10) 0%, transparent 55%),
    linear-gradient(150deg, #f0f9ff 0%, #f4fbf7 60%, #f0fdf4 100%);
  border-bottom: 1px solid var(--border-color);
}
[data-theme="light"] .sd-body { background: var(--bg-primary); background-attachment: fixed; }

/* Panels & Cards */
[data-theme="light"] .sd-panel,
[data-theme="light"] .sd-sb-card {
  background: #ffffff !important;
  border-color: #e2e8f0 !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
}
[data-theme="light"] .sd-panel-title,
[data-theme="light"] .sd-sb-title {
  color: #0f172a !important;
  border-bottom-color: #e2e8f0 !important;
}
[data-theme="light"] .sd-title { color: #0f172a !important; }
[data-theme="light"] .sd-subtitle { color: #334155 !important; }
[data-theme="light"] .sd-subtitle strong { color: #15803d !important; }
[data-theme="light"] .sd-verified {
  background: #dcfce7 !important;
  color: #15803d !important;
  border-color: #86efac !important;
}
[data-theme="light"] .sd-stat { color: #64748b !important; }
[data-theme="light"] .sd-stat strong { color: #0f172a !important; }

/* Breadcrumbs & Links */
[data-theme="light"] .sd-breadcrumb { color: #64748b !important; }
[data-theme="light"] .sd-breadcrumb a { color: #15803d !important; font-weight: 700 !important; }
[data-theme="light"] .sd-breadcrumb a:hover { color: #166534 !important; }
[data-theme="light"] .sd-gmaps-link { color: #0284c7 !important; font-weight: 700 !important; }
[data-theme="light"] .sd-gmaps-link:hover { color: #0369a1 !important; }

/* Top Navbar Overrides on Stall Page */
[data-theme="light"] .site-header .nav-link {
  color: #0f172a !important;
  text-shadow: none !important;
  font-weight: 600 !important;
}
[data-theme="light"] .site-header .nav-link:hover {
  color: #16a34a !important;
}
[data-theme="light"] .site-header .nav-link-login {
  color: #0f172a !important;
  -webkit-text-fill-color: #0f172a !important;
  background: rgba(0, 0, 0, 0.05) !important;
  border-color: #cbd5e1 !important;
  text-shadow: none !important;
  font-weight: 700 !important;
}
[data-theme="light"] .site-header .nav-link-login:hover {
  color: #15803d !important;
  -webkit-text-fill-color: #15803d !important;
  background: #f0fdf4 !important;
  border-color: #86efac !important;
}
[data-theme="light"] .site-header .theme-toggle-btn {
  color: #0f172a !important;
  background: #ffffff !important;
  border-color: #cbd5e1 !important;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08) !important;
}
[data-theme="light"] .site-header .btn-join-nav {
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

/* Light Mode Buttons */
[data-theme="light"] .btn-sd-reserve,
[data-theme="light"] a.btn-sd-reserve,
[data-theme="light"] button.btn-sd-reserve,
[data-theme="light"] .btn-sd-main,
[data-theme="light"] a.btn-sd-main,
[data-theme="light"] button.btn-sd-main {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.35) !important;
}
[data-theme="light"] .btn-sd-reserve:hover,
[data-theme="light"] a.btn-sd-reserve:hover,
[data-theme="light"] button.btn-sd-reserve:hover,
[data-theme="light"] .btn-sd-main:hover,
[data-theme="light"] a.btn-sd-main:hover,
[data-theme="light"] button.btn-sd-main:hover {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
  box-shadow: 0 8px 24px rgba(34, 197, 94, 0.45) !important;
}
[data-theme="light"] .btn-sd-ghost,
[data-theme="light"] a.btn-sd-ghost,
[data-theme="light"] button.btn-sd-ghost {
  background: #ffffff !important;
  color: #0f172a !important;
  -webkit-text-fill-color: #0f172a !important;
  border-color: #cbd5e1 !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
}
[data-theme="light"] .btn-sd-ghost:hover,
[data-theme="light"] a.btn-sd-ghost:hover,
[data-theme="light"] button.btn-sd-ghost:hover {
  background: #f0fdf4 !important;
  color: #15803d !important;
  -webkit-text-fill-color: #15803d !important;
  border-color: #16a34a !important;
}

/* Products in Light Mode */
[data-theme="light"] .sd-product-card {
  background: #ffffff !important;
  border-color: #e2e8f0 !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
}
[data-theme="light"] .sd-product-card:hover {
  border-color: #86efac !important;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08), 0 0 20px rgba(34, 197, 94, 0.15) !important;
}
[data-theme="light"] .sd-prod-name { color: #0f172a !important; }
[data-theme="light"] .sd-prod-unit { color: #64748b !important; }
[data-theme="light"] .sd-prod-price { color: #15803d !important; }
[data-theme="light"] .sd-prod-price .unit-small { color: #64748b !important; }
[data-theme="light"] .sd-prod-cat {
  background: rgba(255, 255, 255, 0.95) !important;
  color: #0284c7 !important;
  border-color: #bae6fd !important;
}
[data-theme="light"] .sd-prod-avail {
  background: #dcfce7 !important;
  color: #15803d !important;
  border-color: #86efac !important;
}

/* Reviews in Light Mode */
[data-theme="light"] .sd-review-card {
  background: #ffffff !important;
  border-color: #e2e8f0 !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
}
[data-theme="light"] .sd-rev-name { color: #0f172a !important; }
[data-theme="light"] .sd-rev-date { color: #64748b !important; }
[data-theme="light"] .sd-rev-text { color: #334155 !important; }
[data-theme="light"] .sd-rev-reply {
  background: #f0fdf4 !important;
  border-left-color: #16a34a !important;
  color: #166534 !important;
}
[data-theme="light"] .sd-rev-reply strong { color: #15803d !important; }
[data-theme="light"] .sd-score-num {
  background: linear-gradient(135deg, #15803d, #0284c7);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
[data-theme="light"] .sd-score-total { color: #64748b !important; }
[data-theme="light"] .sd-bar-track { background: #f1f5f9 !important; border-color: #e2e8f0 !important; }
[data-theme="light"] .sd-bar-lbl,
[data-theme="light"] .sd-bar-cnt { color: #64748b !important; }

/* Map & Sidebar items in Light Mode */
[data-theme="light"] .sd-map-item,
[data-theme="light"] .sd-slot-item {
  background: #f8fafc !important;
  border-color: #e2e8f0 !important;
}
[data-theme="light"] .sd-map-label,
[data-theme="light"] .sd-dl-label { color: #64748b !important; }
[data-theme="light"] .sd-map-val,
[data-theme="light"] .sd-dl-val { color: #0f172a !important; }
[data-theme="light"] .sd-map-sub { color: #64748b !important; }
[data-theme="light"] .sd-slot-date { color: #0f172a !important; }
[data-theme="light"] .sd-slot-time { color: #64748b !important; }
[data-theme="light"] .sd-price-hint { color: #475569 !important; }
[data-theme="light"] .sd-price-hint strong { color: #15803d !important; }
[data-theme="light"] .sd-slot-pill.open {
  background: #dcfce7 !important;
  color: #15803d !important;
  border-color: #86efac !important;
}
[data-theme="light"] .sd-slot-pill.limited {
  background: #fef3c7 !important;
  color: #b45309 !important;
  border-color: #fde68a !important;
}
[data-theme="light"] .sd-slot-pill.full {
  background: #fee2e2 !important;
  color: #b91c1c !important;
  border-color: #fca5a5 !important;
}

/* Map Popups & Controls */
[data-theme="light"] #sd-map { border-color: #cbd5e1 !important; }
[data-theme="light"] .leaflet-popup-content-wrapper {
  background: #ffffff !important;
  color: #0f172a !important;
  border: 1px solid #cbd5e1 !important;
  box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}
[data-theme="light"] .leaflet-popup-tip { background: #ffffff !important; }
[data-theme="light"] .leaflet-bar a {
  background-color: #ffffff !important;
  color: #0f172a !important;
  border-bottom: 1px solid #cbd5e1 !important;
}
[data-theme="light"] .leaflet-bar a:hover {
  background-color: #f1f5f9 !important;
  color: #16a34a !important;
}
[data-theme="light"] .leaflet-container .leaflet-control-attribution {
  background: rgba(255, 255, 255, 0.85) !important;
  color: #475569 !important;
}
[data-theme="light"] .leaflet-container .leaflet-control-attribution a {
  color: #0284c7 !important;
}
</style>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════════════════════ -->
<section class="sd-hero">
  <div class="sd-hero-inner">
    <nav class="sd-breadcrumb" aria-label="breadcrumb">
      <a href="<?= BASE_URL ?>/">Home</a>
      <span class="sep">›</span>
      <a href="<?= BASE_URL ?>/#local-stalls">Local Stalls</a>
      <span class="sep">›</span>
      <span><?= htmlspecialchars($stall['stall_name']) ?></span>
    </nav>

    <div class="sd-hero-flex">
      <!-- Avatar -->
      <div class="sd-avatar-wrap">
        <?php if (!empty($stall['profile_image'])): ?>
          <img src="<?= htmlspecialchars(resolveImageUrl($stall['profile_image'])) ?>"
               alt="<?= htmlspecialchars($stall['contact_person']) ?>"
               onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/logo.svg';">
        <?php else: ?>
          🌾
        <?php endif; ?>
      </div>

      <!-- Meta -->
      <div class="sd-hero-meta">
        <span class="sd-verified">✓ Certified Producer</span>
        <h1 class="sd-title"><?= htmlspecialchars($stall['stall_name']) ?></h1>
        <p class="sd-subtitle">
          Run by <strong><?= htmlspecialchars($stall['contact_person']) ?></strong>
          <?php if ($marketInfo): ?>
            &nbsp;·&nbsp; <?= htmlspecialchars($marketInfo['market_name']) ?>
            <?php if (!empty($marketInfo['stall_number_location'])): ?>
              (<?= htmlspecialchars($marketInfo['stall_number_location']) ?>)
            <?php endif; ?>
          <?php endif; ?>
        </p>
        <div class="sd-hero-stats">
          <?php if ($totalReviews > 0): ?>
            <div class="sd-stat">
              <span class="star">★</span>
              <strong><?= number_format($avgRating, 1) ?></strong>
              <span>(<?= $totalReviews ?> reviews)</span>
            </div>
            <span class="dot"></span>
          <?php endif; ?>
          <?php if (count($products) > 0): ?>
            <div class="sd-stat">🌿 <strong><?= count($products) ?></strong> <span>Products</span></div>
            <span class="dot"></span>
          <?php endif; ?>
          <?php if (!empty($stall['order_cutoff_time'])): ?>
            <div class="sd-stat">⏱ Cutoff: <strong><?= date('g:i A', strtotime($stall['order_cutoff_time'])) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- CTA -->
      <div class="sd-hero-cta">
        <?php if (isLoggedIn() && $_SESSION['role'] === 'customer'): ?>
          <a href="<?= BASE_URL ?>/customer/products.php?farmer=<?= $farmer_id ?>" class="btn-sd-reserve">
            🛒 Browse & Reserve
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="btn-sd-reserve">
            🌾 Reserve a Slot →
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════
     BODY GRID
════════════════════════════════════════════════════════════════════════════ -->
<div class="sd-body">
<div class="sd-grid">

  <!-- ── LEFT COLUMN ─────────────────────────────────────────────────────── -->
  <div>

    <!-- ① MAP ─────────────────────────────────────────────────────────────── -->
    <div class="sd-panel">
      <h2 class="sd-panel-title"><span class="title-icon">📍</span> Stall Location</h2>
      <div id="sd-map"></div>

      <div class="sd-map-info">
        <?php if (!empty($stall['address'])): ?>
          <div class="sd-map-item">
            <span class="sd-map-icon">🏡</span>
            <div>
              <div class="sd-map-label">Farm Address</div>
              <div class="sd-map-val"><?= htmlspecialchars($stall['address']) ?></div>
              <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($stall['address']) ?>"
                 target="_blank" rel="noopener" class="sd-gmaps-link">
                Open in Maps ↗
              </a>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($marketInfo): ?>
          <div class="sd-map-item">
            <span class="sd-map-icon">🏪</span>
            <div>
              <div class="sd-map-label">Market Stall</div>
              <div class="sd-map-val"><?= htmlspecialchars($marketInfo['market_name']) ?></div>
              <?php if (!empty($marketInfo['market_address'])): ?>
                <div class="sd-map-sub"><?= htmlspecialchars($marketInfo['market_address']) ?><?= !empty($marketInfo['city']) ? ', ' . htmlspecialchars($marketInfo['city']) : '' ?></div>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($marketInfo['operating_days'])): ?>
            <div class="sd-map-item">
              <span class="sd-map-icon">📅</span>
              <div>
                <div class="sd-map-label">Market Days</div>
                <div class="sd-map-val"><?= htmlspecialchars($marketInfo['operating_days']) ?></div>
                <?php if (!empty($marketInfo['operating_hours'])): ?>
                  <div class="sd-map-sub"><?= htmlspecialchars($marketInfo['operating_hours']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ② PRODUCTS ─────────────────────────────────────────────────────────── -->
    <div class="sd-panel">
      <h2 class="sd-panel-title"><span class="title-icon">🌿</span> Products at This Stall</h2>

      <?php if (!empty($products)): ?>
        <div class="sd-products-grid">
          <?php foreach ($products as $p):
            $cat = strtolower($p['category_name'] ?? '');
            $emoji = str_contains($cat,'vegetable') ? '🥦'
                   : (str_contains($cat,'fruit')    ? '🍎'
                   : (str_contains($cat,'dairy')    ? '🥛'
                   : (str_contains($cat,'honey')    ? '🍯'
                   : (str_contains($cat,'herb')     ? '🌿'
                   : (str_contains($cat,'grain')    ? '🌾'
                   : (str_contains($cat,'egg')      ? '🥚' : '🥕'))))));

            // Build image path — products stored in uploads/products/
            $imgSrc = '';
            if (!empty($p['image_url'])) {
              // Handle both full-path and filename-only
              if (str_starts_with($p['image_url'], 'http')) {
                $imgSrc = $p['image_url'];
              } elseif (str_contains($p['image_url'], '/')) {
                $imgSrc = BASE_URL . '/' . ltrim($p['image_url'], '/');
              } else {
                $imgSrc = BASE_URL . '/uploads/products/' . $p['image_url'];
              }
            }
          ?>
            <div class="sd-product-card"
                 onclick="window.location='<?= BASE_URL ?>/customer/product_detail.php?id=<?= (int)$p['product_id'] ?>'">
              <div class="sd-prod-img">
                <?php if ($imgSrc): ?>
                  <img src="<?= htmlspecialchars($imgSrc) ?>"
                       alt="<?= htmlspecialchars($p['product_name']) ?>"
                       onerror="this.parentElement.innerHTML='<span style=\'font-size:3rem\'><?= $emoji ?></span>'">
                <?php else: ?>
                  <?= $emoji ?>
                <?php endif; ?>
                <span class="sd-prod-cat"><?= htmlspecialchars($p['category_name'] ?? 'Produce') ?></span>
              </div>
              <div class="sd-prod-body">
                <div class="sd-prod-name"><?= htmlspecialchars($p['product_name']) ?></div>
                <div class="sd-prod-unit">Per <?= htmlspecialchars($p['unit']) ?></div>
                <?php if ($p['min_price'] > 0): ?>
                  <div class="sd-prod-price">
                    Rs. <?= number_format((float)$p['min_price'], 0) ?>
                    <?php if ($p['max_price'] != $p['min_price']): ?>
                      – <?= number_format((float)$p['max_price'], 0) ?>
                    <?php endif; ?>
                    <span class="unit-small">/<?= htmlspecialchars($p['unit']) ?></span>
                  </div>
                <?php endif; ?>
                <?php if ($p['available_days'] > 0): ?>
                  <div class="sd-prod-avail">
                    ✓ <?= (int)$p['available_days'] ?> day<?= $p['available_days'] > 1 ? 's' : '' ?>/week
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="sd-empty-state">
          <div class="sd-empty-icon">🌱</div>
          <p class="sd-empty-text">Products haven't been listed yet. Check back soon!</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- ③ REVIEWS ──────────────────────────────────────────────────────────── -->
    <div class="sd-panel">
      <h2 class="sd-panel-title"><span class="title-icon">⭐</span> Community Reviews</h2>

      <?php if ($totalReviews > 0): ?>
        <div class="sd-rating-summary">
          <!-- Big Score -->
          <div class="sd-big-score">
            <div class="sd-score-num"><?= number_format($avgRating, 1) ?></div>
            <div class="sd-score-stars">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="s" style="color:<?= $s <= round($avgRating) ? '#fbbf24' : 'rgba(255,255,255,.18)' ?>">★</span>
              <?php endfor; ?>
            </div>
            <div class="sd-score-total"><?= $totalReviews ?> verified reviews</div>
          </div>
          <!-- Bars -->
          <div class="sd-rating-bars">
            <?php foreach ([5=>$rs['r5'],4=>$rs['r4'],3=>$rs['r3'],2=>$rs['r2'],1=>$rs['r1']] as $stars=>$count):
              $pct = $totalReviews > 0 ? round($count / $totalReviews * 100) : 0;
            ?>
              <div class="sd-bar-row">
                <span class="sd-bar-lbl"><?= $stars ?></span>
                <div class="sd-bar-track">
                  <div class="sd-bar-fill" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="sd-bar-cnt"><?= (int)$count ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="sd-reviews-grid">
          <?php foreach ($reviews as $rev): ?>
            <div class="sd-review-card">
              <div class="sd-review-top">
                <div class="sd-rev-avatar">
                  <?php if (!empty($rev['customer_avatar'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($rev['customer_avatar']) ?>"
                         alt="" onerror="this.parentElement.innerText='👤'">
                  <?php else: ?>
                    👤
                  <?php endif; ?>
                </div>
                <div>
                  <div class="sd-rev-name"><?= htmlspecialchars($rev['customer_name']) ?></div>
                  <div class="sd-rev-date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></div>
                </div>
              </div>
              <div class="sd-rev-stars">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                  <span class="s" style="color:<?= $s <= $rev['rating'] ? '#fbbf24' : 'rgba(255,255,255,.18)' ?>">★</span>
                <?php endfor; ?>
              </div>
              <?php if (!empty($rev['review_comment'])): ?>
                <p class="sd-rev-text"><?= nl2br(htmlspecialchars($rev['review_comment'])) ?></p>
              <?php endif; ?>
              <?php if (!empty($rev['farmer_response'])): ?>
                <div class="sd-rev-reply">
                  <strong>🌾 Farmer replied:</strong>
                  <?= htmlspecialchars($rev['farmer_response']) ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="sd-empty-state">
          <div class="sd-empty-icon">💬</div>
          <p class="sd-empty-text">No verified reviews yet. Be the first to reserve and share your experience!</p>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /left col -->

  <!-- ── SIDEBAR ──────────────────────────────────────────────────────────── -->
  <div class="sd-sidebar">

    <!-- Reserve CTA -->
    <div class="sd-sb-card sd-reserve-card">
      <div class="sd-sb-title">🌾 Reserve From This Stall</div>
      <?php if ($minPriceVal > 0): ?>
        <p class="sd-price-hint">
          Products starting from<br>
          <strong>Rs. <?= number_format($minPriceVal, 0) ?></strong> / unit
        </p>
      <?php endif; ?>

      <?php if (isLoggedIn() && $_SESSION['role'] === 'customer'): ?>
        <a href="<?= BASE_URL ?>/customer/products.php?farmer=<?= $farmer_id ?>" class="btn-sd-main">
          🛒 Browse &amp; Add to Cart
        </a>
      <?php elseif (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['role']) ?>/dashboard.php" class="btn-sd-main">
          ➔ Go to Dashboard
        </a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode('/MarketLink/stall.php?id=' . $farmer_id) ?>"
           class="btn-sd-main">
          🔑 Sign In to Reserve
        </a>
        <a href="<?= BASE_URL ?>/register.php" class="btn-sd-ghost">
          ✨ Create Free Account
        </a>
      <?php endif; ?>
      <p class="sd-reserve-note">Free cancellation up to 2 hours before cutoff</p>
    </div>

    <!-- Pickup Slots -->
    <div class="sd-sb-card">
      <div class="sd-sb-title">⏰ Upcoming Pickup Slots</div>
      <?php if (!empty($pickupSlots)): ?>
        <?php foreach ($pickupSlots as $slot):
          $rem = (int)$slot['remaining'];
          $cls = $rem <= 0 ? 'full' : ($rem <= 3 ? 'limited' : 'open');
          $lbl = $rem <= 0 ? 'Full' : ($rem <= 3 ? $rem . ' left' : $rem . ' open');
        ?>
          <div class="sd-slot-item">
            <div>
              <div class="sd-slot-date"><?= date('D, M j', strtotime($slot['slot_date'])) ?></div>
              <div class="sd-slot-time">
                <?= date('g:i A', strtotime($slot['start_time'])) ?> –
                <?= date('g:i A', strtotime($slot['end_time'])) ?>
              </div>
            </div>
            <span class="sd-slot-pill <?= $cls ?>"><?= $lbl ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="font-size:.875rem;color:var(--text-muted);text-align:center;padding:.75rem 0;">
          No upcoming slots right now. Check back soon!
        </p>
      <?php endif; ?>
    </div>

    <!-- About the Farmer -->
    <div class="sd-sb-card">
      <div class="sd-sb-title">👨‍🌾 About the Farmer</div>
      <?php if (!empty($stall['description'])): ?>
        <p style="font-size:.875rem;color:var(--text-secondary);line-height:1.65;margin-bottom:1.15rem;">
          <?= nl2br(htmlspecialchars($stall['description'])) ?>
        </p>
      <?php endif; ?>
      <ul class="sd-detail-list">
        <?php if (!empty($stall['username'])): ?>
          <li>
            <span class="sd-dl-icon">@</span>
            <div>
              <div class="sd-dl-label">Username</div>
              <div class="sd-dl-val">@<?= htmlspecialchars($stall['username']) ?></div>
            </div>
          </li>
        <?php endif; ?>
        <?php if (!empty($stall['business_phone'])): ?>
          <li>
            <span class="sd-dl-icon">📞</span>
            <div>
              <div class="sd-dl-label">Contact Phone</div>
              <div class="sd-dl-val"><?= htmlspecialchars($stall['business_phone']) ?></div>
            </div>
          </li>
        <?php endif; ?>
        <?php if (!empty($stall['business_email'])): ?>
          <li>
            <span class="sd-dl-icon">✉️</span>
            <div>
              <div class="sd-dl-label">Email</div>
              <div class="sd-dl-val"><?= htmlspecialchars($stall['business_email']) ?></div>
            </div>
          </li>
        <?php endif; ?>
        <?php if (!empty($stall['order_cutoff_time'])): ?>
          <li>
            <span class="sd-dl-icon">⏰</span>
            <div>
              <div class="sd-dl-label">Order Cutoff</div>
              <div class="sd-dl-val"><?= date('g:i A', strtotime($stall['order_cutoff_time'])) ?></div>
            </div>
          </li>
        <?php endif; ?>
        <?php if (!empty($stall['member_since'])): ?>
          <li>
            <span class="sd-dl-icon">📅</span>
            <div>
              <div class="sd-dl-label">Member Since</div>
              <div class="sd-dl-val"><?= date('F Y', strtotime($stall['member_since'])) ?></div>
            </div>
          </li>
        <?php endif; ?>
      </ul>
    </div>

  </div><!-- /sidebar -->

</div><!-- /sd-grid -->
</div><!-- /sd-body -->

<!-- Leaflet JS — loaded before site_footer and footer -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
  'use strict';

  /* ── Map coordinates from PHP ── */
  var LAT  = <?= json_encode($mapLat) ?>;
  var LNG  = <?= json_encode($mapLng) ?>;
  var NAME = <?= json_encode(htmlspecialchars($stall['stall_name'])) ?>;
  var ADDR = <?= json_encode(htmlspecialchars(!empty($stall['address']) ? $stall['address'] : ($marketInfo['market_address'] ?? ''))) ?>;
  var GMAPS = 'https://www.google.com/maps?q=' + LAT + ',' + LNG;

  var mapContainer = document.getElementById('sd-map');
  if (!mapContainer || typeof L === 'undefined') return;

  /* Initialize Map centered on stall location */
  var map = L.map('sd-map', {
    center: [LAT, LNG],
    zoom: 14,
    scrollWheelZoom: false,
    zoomControl: true
  });

  /* OpenStreetMap Tiles — 100% Free, NO API Key needed */
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
  }).addTo(map);

  /* Custom animated marker */
  var markerHtml = [
    '<div style="',
      'width:48px;height:48px;',
      'background:linear-gradient(135deg,#16a34a,#0ea5e9);',
      'border:3px solid #ffffff;',
      'border-radius:50% 50% 50% 0;',
      'transform:rotate(-45deg);',
      'box-shadow:0 6px 20px rgba(22,163,74,.55),0 0 0 6px rgba(34,197,94,.2);',
      'display:flex;align-items:center;justify-content:center;',
    '">',
      '<span style="transform:rotate(45deg);font-size:20px;line-height:1;">🌾</span>',
    '</div>'
  ].join('');

  var greenIcon = L.divIcon({
    html: markerHtml,
    className: '',
    iconSize: [48, 48],
    iconAnchor: [24, 48],
    popupAnchor: [0, -52]
  });

  var popupContent = [
    '<div style="font-family:\'Plus Jakarta Sans\',sans-serif;padding:6px 2px;min-width:180px;">',
      '<div style="font-weight:800;font-size:1rem;color:#16a34a;margin-bottom:4px;">' + NAME + '</div>',
      '<div style="font-size:.8125rem;color:inherit;opacity:.85;margin-bottom:8px;">' + (ADDR || 'Local Farmers Market') + '</div>',
      '<a href="' + GMAPS + '" target="_blank" rel="noopener"',
        ' style="font-size:.8rem;color:#0284c7;text-decoration:underline;font-weight:700;">',
        'Open in Google Maps ↗',
      '</a>',
    '</div>'
  ].join('');

  L.marker([LAT, LNG], { icon: greenIcon })
    .addTo(map)
    .bindPopup(popupContent, { maxWidth: 260 })
    .openPopup();

  /* Force map size recalculation */
  window.addEventListener('load', function () { map.invalidateSize(); });
  window.addEventListener('resize', function () { map.invalidateSize(); });
  window.addEventListener('themeChanged', function () { map.invalidateSize(); });
  setTimeout(function () { map.invalidateSize(); }, 250);
  setTimeout(function () { map.invalidateSize(); }, 1000);
})();
</script>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
