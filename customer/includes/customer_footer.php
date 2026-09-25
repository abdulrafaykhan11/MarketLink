    </main><!-- /.customer-content -->
  </div><!-- /.customer-main -->
</div><!-- /.customer-layout -->

<!-- Reusable Gemini 3.8 Flash Real-World AI Assistant Widget -->
<?php require_once __DIR__ . '/../../includes/ai_chatbot_widget.php'; ?>

<div id="toast-container"></div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= BASE_URL ?>/assets/js/toast.js"></script>
<script src="<?= BASE_URL ?>/assets/js/customer.js"></script>

</body>
</html>
<?php
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
