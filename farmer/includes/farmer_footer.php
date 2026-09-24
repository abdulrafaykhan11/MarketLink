<?php
/**
 * MarketLink - Farmer / Seller Dashboard Portal Footer
 * Lucide icon initialization, mobile drawer JS, and global interaction helpers
 */
?>
  </div><!-- /.farmer-main -->
</div><!-- /.farmer-layout -->

<!-- Global Toast Notification Container -->
<div id="farmerToastContainer" style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; pointer-events: none;"></div>

<!-- Global Farmer App Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Initialize Lucide Vector Icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // 2. Mobile Drawer Navigation Toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.getElementById('farmerSidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (mobileMenuBtn && sidebar && overlay) {
    mobileMenuBtn.addEventListener('click', function() {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', function() {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }
});

// Farmer App Global JavaScript Utility Namespace
window.FarmerApp = {
  // Toast Alert Notification System
  showToast: function(type, title, message) {
    const container = document.getElementById('farmerToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.style.pointerEvents = 'auto';
    toast.style.background = type === 'success' ? '#064e3b' : (type === 'error' ? '#7f1d1d' : '#1e293b');
    toast.style.color = '#ffffff';
    toast.style.border = '1px solid ' + (type === 'success' ? '#059669' : (type === 'error' ? '#dc2626' : '#334155'));
    toast.style.borderRadius = '12px';
    toast.style.padding = '0.9rem 1.25rem';
    toast.style.minWidth = '280px';
    toast.style.maxWidth = '400px';
    toast.style.boxShadow = '0 10px 25px -5px rgba(0,0,0,0.4)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'flex-start';
    toast.style.gap = '0.75rem';
    toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
    toast.style.transform = 'translateY(20px)';
    toast.style.opacity = '0';

    const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
    toast.innerHTML = `
      <div style="font-weight:bold; font-size:1.1rem; line-height:1; color:${type === 'success' ? '#34d399' : (type === 'error' ? '#f87171' : '#38bdf8')}">${icon}</div>
      <div style="flex:1;">
        <div style="font-weight:700; font-size:0.875rem; margin-bottom:0.15rem;">${title}</div>
        <div style="font-size:0.8125rem; opacity:0.9; line-height:1.4;">${message}</div>
      </div>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    });

    setTimeout(() => {
      toast.style.transform = 'translateY(20px)';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  },

  // Asynchronous Pre-Order Status Updater
  updateOrderStatus: function(orderId, newStatus, reason = '') {
    const btn = event ? event.target.closest('button') : null;
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    if (reason) formData.append('reason', reason);

    fetch('<?= BASE_URL ?>/farmer/api/update_order.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (btn) btn.disabled = false;
      if (data.status === 'success') {
        FarmerApp.showToast('success', 'Order Updated', data.message || `Order moved to ${newStatus}`);
        setTimeout(() => location.reload(), 800);
      } else {
        FarmerApp.showToast('error', 'Update Failed', data.message || 'Could not update order status.');
      }
    })
    .catch(err => {
      if (btn) btn.disabled = false;
      FarmerApp.showToast('error', 'Network Error', 'Please check server connection.');
    });
  },

  // Instant In-Stock / Sold-Out Switcher
  toggleStock: function(productId, isAvailable) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('is_available', isAvailable ? 1 : 0);

    fetch('<?= BASE_URL ?>/farmer/api/toggle_stock.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        FarmerApp.showToast('success', 'Inventory Updated', isAvailable ? 'Item marked IN STOCK for market days' : 'Item marked SOLD OUT');
      } else {
        FarmerApp.showToast('error', 'Error', data.message || 'Could not update stock status.');
      }
    })
    .catch(err => {
      FarmerApp.showToast('error', 'Network Error', 'Could not sync availability.');
    });
  }
};
</script>

</body>
</html>
