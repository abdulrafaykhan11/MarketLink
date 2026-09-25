<?php
/**
 * MarketLink - Farmer Customer Reviews & Response Center
 * SRS Specification: Farmers can view and optionally respond to customer reviews left on their products
 */

$pageTitle = 'Customer Reviews & Ratings';
$activePage = 'reviews';
require_once __DIR__ . '/includes/farmer_header.php';

// 1. Fetch Star Rating Distribution and Overview
$ratingStatsStmt = $pdo->prepare("SELECT 
    COUNT(*) as total_reviews,
    COALESCE(AVG(rating), 5.0) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1,
    SUM(CASE WHEN farmer_response IS NULL OR farmer_response = '' THEN 1 ELSE 0 END) as unanswered_count
    FROM farmer_reviews WHERE farmer_id = :fid");
$ratingStatsStmt->execute([':fid' => $farmerId]);
$stats = $ratingStatsStmt->fetch();

$totalReviews = (int)$stats['total_reviews'];
$avgRating = round((float)$stats['avg_rating'], 1);
$unansweredCount = (int)$stats['unanswered_count'];

// 2. Parse Filter
$filter = trim($_GET['filter'] ?? 'all');
$where = ["fr.farmer_id = :fid"];
$params = [':fid' => $farmerId];

if ($filter === 'unanswered') {
    $where[] = "(fr.farmer_response IS NULL OR fr.farmer_response = '')";
} elseif ($filter === '5') {
    $where[] = "fr.rating = 5";
} elseif ($filter === '4') {
    $where[] = "fr.rating = 4";
} elseif ($filter === 'low') {
    $where[] = "fr.rating <= 3";
}

$whereSql = implode(' AND ', $where);

// 3. Fetch Stall Reviews
$reviewsStmt = $pdo->prepare("SELECT fr.*, cp.full_name as customer_name, o.order_number, o.pickup_date 
                              FROM farmer_reviews fr 
                              JOIN customer_profiles cp ON fr.customer_id = cp.customer_id 
                              JOIN orders o ON fr.order_id = o.order_id 
                              WHERE {$whereSql} 
                              ORDER BY fr.created_at DESC");
$reviewsStmt->execute($params);
$reviews = $reviewsStmt->fetchAll();

// 4. Fetch Product-specific Reviews
$prodReviewsStmt = $pdo->prepare("SELECT pr.*, cp.full_name as customer_name, p.product_name, p.image_url, o.order_number 
                                  FROM product_reviews pr 
                                  JOIN products p ON pr.product_id = p.product_id 
                                  JOIN customer_profiles cp ON pr.customer_id = cp.customer_id 
                                  JOIN orders o ON pr.order_id = o.order_id 
                                  WHERE p.farmer_id = :fid 
                                  ORDER BY pr.created_at DESC LIMIT 10");
$prodReviewsStmt->execute([':fid' => $farmerId]);
$productReviews = $prodReviewsStmt->fetchAll();
?>

<div class="farmer-content">

  <!-- Header -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
        Customer Reviews &amp; Stall Ratings
      </h1>
      <p style="color: var(--slate-400); font-size: 0.875rem;">
        View feedback from customers who picked up your harvest, and respond directly to build community trust.
      </p>
    </div>
  </div>

  <!-- Rating Summary Overview Card -->
  <div class="farmer-card" style="margin-bottom: 2rem;">
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2.5rem; align-items: center;">
      <!-- Big Score -->
      <div style="text-align: center; border-right: 1px solid var(--border-color); padding-right: 2rem;">
        <div style="font-family: var(--font-heading); font-size: 3.75rem; font-weight: 800; color: var(--text-primary); line-height: 1;">
          <?= $avgRating ?>
        </div>
        <div style="font-size: 1.5rem; color: #fbbf24; margin: 0.5rem 0;">
          <?= str_repeat('★', (int)round($avgRating)) ?><?= str_repeat('☆', 5 - (int)round($avgRating)) ?>
        </div>
        <div style="font-size: 0.875rem; color: var(--slate-400);">
          Based on <strong><?= $totalReviews ?></strong> verified shopper rating<?= $totalReviews !== 1 ? 's' : '' ?>
        </div>
      </div>

      <!-- Star Distribution Bars -->
      <div style="display: flex; flex-direction: column; gap: 0.6rem;">
        <?php for ($s = 5; $s >= 1; $s--): ?>
          <?php
            $cnt = (int)($stats["star_{$s}"] ?? 0);
            $pct = ($totalReviews > 0) ? round(($cnt / $totalReviews) * 100) : 0;
          ?>
          <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.8125rem;">
            <div style="width: 50px; font-weight: 600; color: var(--text-primary);"><?= $s ?> stars</div>
            <div style="flex: 1; height: 10px; background: var(--bg-surface-elevated); border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-color);">
              <div style="width: <?= $pct ?>%; height: 100%; background: #fbbf24; border-radius: var(--radius-full);"></div>
            </div>
            <div style="width: 40px; text-align: right; color: var(--slate-400);"><?= $cnt ?></div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- Filter Pills Bar -->
  <div style="display: flex; align-items: center; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
    <a href="?filter=all" class="farmer-btn-pill <?= ($filter === 'all') ? 'active' : '' ?>">
      <span>All Reviews (<?= $totalReviews ?>)</span>
    </a>
    <a href="?filter=unanswered" class="farmer-btn-pill <?= ($filter === 'unanswered') ? 'active' : '' ?>" style="<?= $unansweredCount > 0 ? 'border-color: #f59e0b; color: #fbbf24;' : '' ?>">
      <span>Unanswered (<?= $unansweredCount ?>)</span>
      <?php if ($unansweredCount > 0): ?>
        <span class="pulse-dot-amber" style="margin-left: 0.25rem;"></span>
      <?php endif; ?>
    </a>
    <a href="?filter=5" class="farmer-btn-pill <?= ($filter === '5') ? 'active' : '' ?>">
      <span>5 Stars (<?= (int)$stats['star_5'] ?>)</span>
    </a>
    <a href="?filter=4" class="farmer-btn-pill <?= ($filter === '4') ? 'active' : '' ?>">
      <span>4 Stars (<?= (int)$stats['star_4'] ?>)</span>
    </a>
    <a href="?filter=low" class="farmer-btn-pill <?= ($filter === 'low') ? 'active' : '' ?>">
      <span>3 Stars &amp; Below (<?= (int)$stats['star_3'] + (int)$stats['star_2'] + (int)$stats['star_1'] ?>)</span>
    </a>
  </div>

  <!-- Reviews List -->
  <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 3rem;">
    <?php if (empty($reviews)): ?>
      <div class="farmer-card" style="text-align: center; padding: 4rem 1.5rem; color: var(--slate-400);">
        <i data-lucide="message-square-off" style="width: 48px; height: 48px; margin-bottom: 0.75rem; color: var(--slate-500);"></i>
        <h4 style="color: var(--text-primary); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.35rem;">No Reviews in this Category</h4>
        <p style="font-size: 0.875rem;">
          Try selecting another filter above to view past customer reviews.
        </p>
      </div>
    <?php else: ?>
      <?php foreach ($reviews as $rev): ?>
        <div class="farmer-card" style="padding: 1.5rem;">
          <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--sky-500) 0%, var(--primary-500) 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem;">
                <?= strtoupper(substr($rev['customer_name'], 0, 1)) ?>
              </div>
              <div>
                <strong style="color: var(--text-primary); font-size: 0.9375rem;"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                <div style="font-size: 0.75rem; color: var(--slate-400);">
                  Verified Pickup • Order #<?= htmlspecialchars($rev['order_number']) ?>
                </div>
              </div>
            </div>

            <div style="text-align: right;">
              <div style="color: #fbbf24; font-size: 1.1rem; line-height: 1;">
                <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
              </div>
              <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.25rem;">
                <?= date('F j, Y', strtotime($rev['created_at'])) ?>
              </div>
            </div>
          </div>

          <!-- Customer Comment -->
          <p style="font-size: 0.9375rem; color: var(--slate-200); line-height: 1.6; margin-bottom: 1rem; background: var(--bg-surface-elevated); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
            "<?= htmlspecialchars($rev['review_comment'] ?: 'Great harvest produce and smooth market collection.') ?>"
          </p>

          <!-- Farmer Official Response Area -->
          <?php if (!empty($rev['farmer_response'])): ?>
            <div style="background: rgba(34, 197, 94, 0.08); border-left: 3.5px solid var(--primary-500); padding: 1rem 1.25rem; border-radius: 0 var(--radius-lg) var(--radius-lg) 0; margin-top: 0.75rem;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                <span class="farmer-badge-role" style="font-size: 0.65rem;">
                  <i data-lucide="check" style="width: 12px; height: 12px;"></i> Official Producer Response
                </span>
                <span style="font-size: 0.75rem; color: var(--slate-400);">
                  <?= !empty($rev['response_date']) ? date('M j, Y', strtotime($rev['response_date'])) : '' ?>
                </span>
              </div>
              <div style="font-size: 0.875rem; color: var(--primary-300); line-height: 1.5;">
                <?= htmlspecialchars($rev['farmer_response']) ?>
              </div>
            </div>
          <?php else: ?>
            <div style="display: flex; justify-content: flex-end; margin-top: 0.75rem;">
              <button type="button" class="farmer-btn-primary" onclick="openReplyModal(<?= $rev['review_id'] ?>, '<?= htmlspecialchars(addslashes($rev['customer_name'])) ?>', '<?= htmlspecialchars(addslashes($rev['review_comment'])) ?>')" style="padding: 0.5rem 1rem; font-size: 0.8125rem;">
                <i data-lucide="corner-down-right" style="width: 15px; height: 15px;"></i>
                <span>Reply to Customer</span>
              </button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Produce-Specific Customer Reviews -->
  <?php if (!empty($productReviews)): ?>
    <div class="farmer-card">
      <div class="farmer-card-header">
        <div class="farmer-card-title-group">
          <h3 class="farmer-card-title">
            <i data-lucide="tag" style="width: 20px; height: 20px; color: var(--sky-400);"></i>
            <span>Reviews on Specific Produce Items</span>
          </h3>
          <span class="farmer-card-subtitle">Ratings specifically tagged to individual crops</span>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
        <?php foreach ($productReviews as $pr): ?>
          <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.15rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
              <div style="width: 36px; height: 36px; border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface);">
                <img src="<?= htmlspecialchars(resolveImageUrl($pr['image_url'] ?? '', BASE_URL . '/assets/images/cat-vegetables.svg')) ?>"
                     alt="<?= htmlspecialchars($pr['product_name']) ?>"
                     style="width: 100%; height: 100%; object-fit: cover;"
                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/cat-vegetables.svg';">
              </div>
              <div>
                <strong style="color: var(--text-primary); font-size: 0.875rem;"><?= htmlspecialchars($pr['product_name']) ?></strong>
                <div style="font-size: 0.75rem; color: #fbbf24;">
                  <?= str_repeat('★', (int)$pr['rating']) ?><?= str_repeat('☆', 5 - (int)$pr['rating']) ?>
                </div>
              </div>
            </div>
            <p style="font-size: 0.8125rem; color: var(--slate-300); line-height: 1.5; margin: 0;">
              "<?= htmlspecialchars($pr['comment']) ?>"
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- Modal: Reply to Customer Review -->
<div class="farmer-modal-backdrop" id="reviewReplyModal">
  <div class="farmer-modal">
    <div class="farmer-modal-header">
      <h4 class="farmer-modal-title">
        <i data-lucide="message-circle" style="width: 20px; height: 20px; color: var(--primary-500);"></i>
        <span>Reply to Customer Review</span>
      </h4>
      <button type="button" class="farmer-modal-close-btn" onclick="closeReplyModal()">✕</button>
    </div>
    <form id="replyForm" onsubmit="submitFarmerReply(event)">
      <div class="farmer-modal-body">
        <input type="hidden" name="review_id" id="replyReviewId">
        <div style="background: var(--bg-surface-elevated); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary);" id="replyCustomerName"></div>
          <div style="font-size: 0.8125rem; color: var(--slate-300); margin-top: 0.25rem;" id="replyCustomerComment"></div>
        </div>

        <div class="farmer-form-group">
          <label class="farmer-form-label" for="replyText">Your Producer Response <span style="color:#ef4444;">*</span></label>
          <textarea class="farmer-form-textarea" id="replyText" name="farmer_response" rows="4" placeholder="Thank the customer or address their note with courteous professionalism..." required></textarea>
        </div>
      </div>
      <div class="farmer-modal-footer">
        <button type="button" class="farmer-btn-secondary" onclick="closeReplyModal()">Cancel</button>
        <button type="submit" class="farmer-btn-primary" id="replySubmitBtn">
          <span>Publish Official Reply</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openReplyModal(reviewId, customerName, comment) {
  document.getElementById('replyReviewId').value = reviewId;
  document.getElementById('replyCustomerName').textContent = customerName;
  document.getElementById('replyCustomerComment').textContent = `"${comment}"`;
  document.getElementById('reviewReplyModal').classList.add('active');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeReplyModal() {
  document.getElementById('reviewReplyModal').classList.remove('active');
}

function submitFarmerReply(e) {
  e.preventDefault();
  const form = document.getElementById('replyForm');
  const btn = document.getElementById('replySubmitBtn');
  btn.disabled = true;

  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/farmer/api/save_reply.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    if (data.status === 'success') {
      FarmerApp.showToast('success', 'Reply Saved', data.message);
      closeReplyModal();
      setTimeout(() => location.reload(), 700);
    } else {
      FarmerApp.showToast('error', 'Error', data.message || 'Could not post reply.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    FarmerApp.showToast('error', 'Network Error', 'Please check connection.');
  });
}
</script>

<?php require_once __DIR__ . '/includes/farmer_footer.php'; ?>
