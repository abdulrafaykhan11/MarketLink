<?php
/**
 * MarketLink - Customer Reviews and Ratings
 * Fully implements SRS Section 1.6: 1-5 star ratings for farmers and products, and farmer responses.
 */

$pageTitle = 'Reviews & Ratings';
$activePage = 'reviews';
require_once __DIR__ . '/includes/customer_header.php';

$selectedOrderId = (int)($_GET['order_id'] ?? 0);

// 1. Fetch Completed Orders that need reviews
$completedStmt = $pdo->prepare("SELECT o.order_id, o.order_number, o.created_at, o.pickup_date, o.farmer_id,
                                       fp.stall_name, fp.contact_person, m.market_name,
                                       (SELECT COUNT(*) FROM farmer_reviews fr WHERE fr.order_id = o.order_id AND fr.customer_id = :uid1) as is_reviewed
                                FROM orders o
                                JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                                JOIN markets m ON o.market_id = m.market_id
                                WHERE o.customer_id = :uid2 AND o.order_status = 'completed'
                                ORDER BY o.order_id DESC");
$completedStmt->execute([':uid1' => $currentUserId, ':uid2' => $currentUserId]);
$completedOrders = $completedStmt->fetchAll();

// 2. Fetch Submitted Reviews by this customer (with farmer responses)
$revStmt = $pdo->prepare("SELECT fr.*, fp.stall_name, fp.contact_person, o.order_number, m.market_name
                          FROM farmer_reviews fr
                          JOIN farmer_profiles fp ON fr.farmer_id = fp.farmer_id
                          JOIN orders o ON fr.order_id = o.order_id
                          JOIN markets m ON o.market_id = m.market_id
                          WHERE fr.customer_id = :uid
                          ORDER BY fr.review_id DESC");
$revStmt->execute([':uid' => $currentUserId]);
$myReviews = $revStmt->fetchAll();

// If an order_id was specified in URL, fetch its items for the review form
$activeReviewOrder = null;
$orderProducts = [];
if ($selectedOrderId > 0) {
    foreach ($completedOrders as $co) {
        if ($co['order_id'] == $selectedOrderId) {
            $activeReviewOrder = $co;
            break;
        }
    }
    if ($activeReviewOrder) {
        $pStmt = $pdo->prepare("SELECT oi.product_id, oi.quantity, p.product_name, p.unit, p.image_url 
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.product_id
                                WHERE oi.order_id = :oid");
        $pStmt->execute([':oid' => $selectedOrderId]);
        $orderProducts = $pStmt->fetchAll();
    }
}
?>

<!-- Page Header -->
<div class="page-header-row">
  <div>
    <h1 class="page-heading">
      <span>⭐</span> Reviews & Ratings
    </h1>
    <p class="page-subheading">
      Rate your fresh produce, share feedback with local growers, and read farmer responses
    </p>
  </div>
</div>

<!-- Active Review Form (if an order is selected for review) -->
<?php if ($activeReviewOrder): ?>
  <div class="portal-card" style="margin-bottom:2.5rem; border:2px solid var(--emerald-500); box-shadow:var(--shadow-lg);">
    <div class="portal-card-header">
      <div>
        <span style="font-size:0.75rem; color:var(--emerald-400); font-weight:700; text-transform:uppercase;">Rate Completed Order</span>
        <h3 class="portal-card-title">
          Feedback for <?= htmlspecialchars($activeReviewOrder['stall_name']) ?> (#<?= htmlspecialchars($activeReviewOrder['order_number']) ?>)
        </h3>
      </div>
      <a href="<?= BASE_URL ?>/customer/reviews.php" class="portal-card-link" style="color:var(--text-muted);">✕ Close</a>
    </div>

    <form id="submitReviewForm">
      <input type="hidden" name="order_id" value="<?= $activeReviewOrder['order_id'] ?>">
      <input type="hidden" name="farmer_rating" id="farmerRatingInput" value="5">

      <!-- Overall Farmer Stall Rating -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">
          How was your overall experience with <?= htmlspecialchars($activeReviewOrder['stall_name']) ?>? *
        </label>
        
        <div class="star-rating-picker" data-target-input="farmerRatingInput" style="margin-bottom:1rem;">
          <span class="star active">★</span>
          <span class="star active">★</span>
          <span class="star active">★</span>
          <span class="star active">★</span>
          <span class="star active">★</span>
        </div>

        <div>
          <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--text-muted); margin-bottom:0.35rem;">
            Stall & Pickup Comments:
          </label>
          <textarea name="farmer_comment" class="topbar-search-input" style="border-radius:var(--radius-md); height:85px; resize:none;" 
                    placeholder="Share how fresh the harvest was, timeliness of pickup, or words of encouragement for the grower..." required></textarea>
        </div>
      </div>

      <!-- Individual Product Reviews -->
      <?php if (!empty($orderProducts)): ?>
        <h4 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:1rem;">
          Produce Quality Ratings (Optional)
        </h4>

        <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1.5rem;">
          <?php foreach ($orderProducts as $prod): ?>
            <div style="background:var(--bg-surface-elevated); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1rem; display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prod['image_url']) ?>" alt="" style="width:48px; height:48px; border-radius:var(--radius-md); object-fit:cover;">
              <div style="flex:1; min-width:180px;">
                <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);"><?= htmlspecialchars($prod['product_name']) ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted);">Quantity: <?= $prod['quantity'] ?> <?= htmlspecialchars($prod['unit']) ?></div>
              </div>

              <!-- Product Star Picker -->
              <div>
                <input type="hidden" name="product_ratings[<?= $prod['product_id'] ?>]" id="prodRatingInput_<?= $prod['product_id'] ?>" value="5">
                <div class="star-rating-picker" data-target-input="prodRatingInput_<?= $prod['product_id'] ?>" style="font-size:1.35rem;">
                  <span class="star active">★</span>
                  <span class="star active">★</span>
                  <span class="star active">★</span>
                  <span class="star active">★</span>
                  <span class="star active">★</span>
                </div>
              </div>

              <!-- Product Short Comment -->
              <input type="text" name="product_comments[<?= $prod['product_id'] ?>]" class="topbar-search-input" 
                     placeholder="Taste, freshness, texture..." style="flex:2; min-width:200px; padding:0.45rem 0.85rem; border-radius:var(--radius-md);">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
        <a href="<?= BASE_URL ?>/customer/reviews.php" class="btn-secondary" style="text-decoration:none; padding:0.6rem 1.25rem;">Cancel</a>
        <button type="submit" id="btnSubmitReview" class="btn-primary" style="padding:0.6rem 1.75rem; cursor:pointer;">
          <span>⭐</span> Submit Feedback
        </button>
      </div>
    </form>
  </div>
<?php endif; ?>

<!-- Section 1: Orders Eligible for Review -->
<div class="portal-card" style="margin-bottom:2rem;">
  <div class="portal-card-header">
    <h3 class="portal-card-title">
      <span>📦</span> Orders Awaiting Feedback
    </h3>
    <span style="font-size:0.8rem; color:var(--text-muted);">Completed pre-orders</span>
  </div>

  <?php
    $unreviewedOrders = array_filter($completedOrders, fn($o) => !$o['is_reviewed']);
  ?>

  <?php if (!empty($unreviewedOrders)): ?>
    <div style="display:flex; flex-direction:column; gap:0.85rem;">
      <?php foreach ($unreviewedOrders as $ord): ?>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1rem 1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
          <div>
            <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary);">
              Order #<?= htmlspecialchars($ord['order_number']) ?> — <?= htmlspecialchars($ord['stall_name']) ?>
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.2rem;">
              Picked up on <?= date('M d, Y', strtotime($ord['pickup_date'])) ?> at <?= htmlspecialchars($ord['market_name']) ?>
            </div>
          </div>

          <a href="<?= BASE_URL ?>/customer/reviews.php?order_id=<?= $ord['order_id'] ?>" class="btn-primary" style="text-decoration:none; padding:0.45rem 1rem; font-size:0.8rem; display:inline-flex; align-items:center; gap:0.35rem;">
            <span>⭐</span> Write Review
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="font-size:0.85rem; color:var(--text-muted); font-style:italic; margin:0;">
      All your completed orders have been reviewed! New eligible orders will appear here once picked up.
    </p>
  <?php endif; ?>
</div>

<!-- Section 2: My Submitted Reviews & Farmer Responses -->
<div class="portal-card">
  <div class="portal-card-header">
    <h3 class="portal-card-title">
      <span>💬</span> My Submitted Reviews & Farmer Responses (<?= count($myReviews) ?>)
    </h3>
  </div>

  <?php if (!empty($myReviews)): ?>
    <div style="display:flex; flex-direction:column; gap:1.25rem;">
      <?php foreach ($myReviews as $rev): ?>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); padding:1.25rem;">
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.5rem; flex-wrap:wrap; gap:0.5rem;">
            <div>
              <strong style="color:var(--text-primary); font-size:0.95rem;"><?= htmlspecialchars($rev['stall_name']) ?></strong>
              <span style="font-size:0.75rem; color:var(--text-muted); margin-left:0.5rem;">(Pre-Order #<?= htmlspecialchars($rev['order_number']) ?>)</span>
            </div>
            
            <div style="display:flex; align-items:center; gap:0.4rem;">
              <span style="color:#f59e0b; font-size:1.15rem; letter-spacing:0.1em;">
                <?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?>
              </span>
              <span style="font-size:0.75rem; color:var(--text-muted);">
                <?= date('M d, Y', strtotime($rev['created_at'])) ?>
              </span>
            </div>
          </div>

          <!-- Customer Comment -->
          <p style="font-size:0.875rem; color:var(--text-secondary); line-height:1.5; margin:0 0 0.85rem 0;">
            "<?= htmlspecialchars($rev['review_comment']) ?>"
          </p>

          <!-- Farmer Response (if available) -->
          <?php if (!empty($rev['farmer_response'])): ?>
            <div style="background:rgba(16,185,129,0.1); border-left:3.5px solid var(--emerald-500); border-radius:var(--radius-md); padding:0.85rem 1rem; margin-top:0.75rem;">
              <div style="font-size:0.75rem; font-weight:700; color:var(--emerald-400); margin-bottom:0.25rem; display:flex; justify-content:space-between;">
                <span>💬 Grower Reply from <?= htmlspecialchars($rev['stall_name']) ?></span>
                <span style="font-size:0.7rem; color:var(--text-muted); font-weight:normal;">
                  <?= !empty($rev['response_date']) ? date('M d, Y', strtotime($rev['response_date'])) : '' ?>
                </span>
              </div>
              <p style="font-size:0.825rem; color:var(--text-primary); margin:0; line-height:1.45;">
                <?= htmlspecialchars($rev['farmer_response']) ?>
              </p>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="text-align:center; padding:2rem 1rem;">
      <p style="font-size:0.9rem; color:var(--text-muted); margin:0;">
        You have not submitted any reviews yet. Complete a pre-order pickup to share your feedback!
      </p>
    </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('submitReviewForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmitReview');
  btn.disabled = true;
  btn.textContent = 'Submitting...';

  const formData = new FormData(this);

  fetch('<?= BASE_URL ?>/customer/api/review.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showPortalToast(data.message, 'success');
        setTimeout(() => {
          window.location.href = '<?= BASE_URL ?>/customer/reviews.php';
        }, 800);
      } else {
        showPortalToast(data.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Submit Feedback';
      }
    })
    .catch(() => {
      showPortalToast('Server error submitting review.', 'error');
      btn.disabled = false;
      btn.textContent = 'Submit Feedback';
    });
});
</script>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
