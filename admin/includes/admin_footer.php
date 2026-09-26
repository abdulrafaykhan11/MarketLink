    </main><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Toast Notification Container (Prominent Side Notification) -->
<div id="adminToastContainer" style="position:fixed; top:2rem; right:2rem; z-index:999999; display:flex; flex-direction:column; gap:0.75rem; max-width:440px; pointer-events:none;"></div>

<script>
  // Initialize Lucide Icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // Mobile Drawer Toggle
  const sidebar = document.getElementById('adminSidebar');
  const toggleBtn = document.getElementById('adminSidebarToggle');
  const closeBtn = document.getElementById('adminSidebarClose');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  if (closeBtn && sidebar) {
    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('open');
    });
  }

  // Global Toast Function
  window.showAdminToast = function(message, type = 'success') {
    const container = document.getElementById('adminToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    const isError = type === 'error';
    const bg = isSuccess ? 'rgba(16, 185, 129, 0.95)' : isError ? 'rgba(239, 68, 68, 0.95)' : 'rgba(99, 102, 241, 0.95)';

    toast.style.cssText = `
      background: ${bg};
      color: #ffffff;
      padding: 0.95rem 1.35rem;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 600;
      line-height: 1.45;
      box-shadow: 0 14px 35px -5px rgba(0,0,0,0.45);
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      pointer-events: auto;
      border: 1px solid rgba(255,255,255,0.2);
      animation: toastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      backdrop-filter: blur(12px);
    `;

    toast.innerHTML = `<span style="font-size:1.15rem; flex-shrink:0;">${isSuccess ? '✔' : isError ? '⚠️' : 'ℹ'}</span> <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };
</script>

<style>
@keyframes toastSlideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Gemini 3.8 Flash Real-World AI Assistant Widget -->
<?php require_once __DIR__ . '/../../includes/ai_chatbot_widget.php'; ?>

</body>
</html>
<?php
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
