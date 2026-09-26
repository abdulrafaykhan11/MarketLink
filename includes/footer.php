<?php
/**
 * MarketLink - Footer & Interactive Modals Template
 */
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
$flashWarning = getFlash('warning');
$flashInfo    = getFlash('info');
?>

<!-- Terms & Conditions Modal -->
<div class="modal-backdrop" id="termsModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal-window">
    <div class="modal-header">
      <div class="modal-header-title">
        <div class="modal-header-icon">📜</div>
        <div>
          <div class="modal-title" id="modalTitle">MarketLink Terms & Agreement</div>
          <div class="modal-subtitle">Last updated: September 2026 • Platform Version 2.4</div>
        </div>
      </div>
      <button type="button" class="modal-close-btn" data-close-modal aria-label="Close dialog">&times;</button>
    </div>

    <div class="modal-body">
      <div class="modal-callout">
        <strong>Direct Marketplace Commitment:</strong> MarketLink connects conscious buyers directly to local agriculture stalls. By continuing, you agree to uphold community transparency, respect pickup schedules, and practice ethical commerce.
      </div>

      <h4>1. Acceptance of Community Agreement</h4>
      <p>By creating an account as a <strong>Customer</strong> or a <strong>Farmer/Producer</strong> on MarketLink, you acknowledge and agree to comply with our operating rules, quality standards, and digital communication terms.</p>

      <h4>2. Customer Commitments & Pickup Slots</h4>
      <ul>
        <li><strong>Fresh Produce Reservations:</strong> When placing an order, customers agree to choose an official market pickup slot and collect goods within the scheduled timeframe.</li>
        <li><strong>Cancellation Policy:</strong> Orders must be cancelled at least 2 hours before the farmer's cutoff window to avoid agricultural food waste.</li>
        <li><strong>Respectful Feedback:</strong> Ratings and reviews must reflect genuine purchases and adhere to anti-harassment guidelines.</li>
      </ul>

      <h4>3. Farmer & Stallholder Standard of Excellence</h4>
      <ul>
        <li><strong>Product Authenticity:</strong> All produce and artisanal farm items listed must be genuine, accurately weighed, and free from prohibited adulterants.</li>
        <li><strong>Approval & Verification:</strong> Farmer profiles undergo platform verification before stall listings are published publicly.</li>
        <li><strong>Inventory Accuracy:</strong> Farmers commit to updating weekly stock quantity and marking recurring produce availability realistically.</li>
      </ul>

      <h4>4. Privacy & Data Integrity</h4>
      <p>MarketLink stores user credentials securely utilizing high-entropy bcrypt password hashing. Contact details (phone numbers and pickup addresses) are strictly used for order fulfillment, market stall announcements, and logistics notifications. We never monetize or distribute personal data to unauthorized third parties.</p>

      <h4>5. Modifications & Account Governance</h4>
      <p>MarketLink administrators reserve the right to suspend or restrict accounts that violate fair trading standards, distribute false information, or repeatedly abandon confirmed pickup orders.</p>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-secondary" data-close-modal>Dismiss</button>
      <button type="button" class="btn-primary" id="acceptTermsModalBtn" style="width: auto; padding: 0 1.5rem;">
        ✓ I Accept the Terms & Agreement
      </button>
    </div>
  </div>
</div>
<!-- Gemini 3.8 Flash Real-World AI Assistant Widget -->
<?php require_once __DIR__ . '/ai_chatbot_widget.php'; ?>

<!-- JavaScript Assets -->
<script src="<?= BASE_URL ?>/assets/js/toast.js"></script>
<script src="<?= BASE_URL ?>/assets/js/home.js"></script>
<script src="<?= BASE_URL ?>/assets/js/scroll-animations.js?v=<?= filemtime(__DIR__ . '/../assets/js/scroll-animations.js') ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/auth-validation.js"></script>

<!-- Server-side Session Flash Toasts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($flashSuccess)): ?>
    Toast.success('Success', <?= json_encode($flashSuccess) ?>);
  <?php endif; ?>
  <?php if (!empty($flashError)): ?>
    Toast.error('Notice', <?= json_encode($flashError) ?>);
  <?php endif; ?>
  <?php if (!empty($flashWarning)): ?>
    Toast.warning('Warning', <?= json_encode($flashWarning) ?>);
  <?php endif; ?>
  <?php if (!empty($flashInfo)): ?>
    Toast.info('Information', <?= json_encode($flashInfo) ?>);
  <?php endif; ?>
});
</script>

</body>
</html>
