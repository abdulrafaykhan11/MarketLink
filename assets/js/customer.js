/**
 * MARKETLINK - CUSTOMER PORTAL CLIENT JAVASCRIPT
 * Handles real-time cart actions, favorite toggles, order cancellations,
 * AI Chatbot interactions, star ratings, and Leaflet OpenStreetMap integration.
 */

document.addEventListener('DOMContentLoaded', () => {
  initCustomerSidebar();
  initCartBadge();
  initFavoriteHandlers();
  initChatbot();
  initStarRatings();
  initMarketMap();
});

/* ==========================================================================
   1. Responsive Sidebar Navigation
   ========================================================================== */
function initCustomerSidebar() {
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.querySelector('.customer-sidebar');
  if (!toggleBtn || !sidebar) return;

  // Create overlay backdrop for mobile
  let backdrop = document.querySelector('.sidebar-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    backdrop.style.cssText = `
      display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px); z-index: 95;
    `;
    document.body.appendChild(backdrop);
  }

  function toggleSidebar(open) {
    if (open) {
      sidebar.classList.add('open');
      backdrop.style.display = 'block';
    } else {
      sidebar.classList.remove('open');
      backdrop.style.display = 'none';
    }
  }

  toggleBtn.addEventListener('click', () => {
    const isOpen = sidebar.classList.contains('open');
    toggleSidebar(!isOpen);
  });

  backdrop.addEventListener('click', () => toggleSidebar(false));
}

/* ==========================================================================
   2. Cart & Pre-Order AJAX State
   ========================================================================== */
function initCartBadge() {
  fetch(`${window.BASE_URL || ''}/customer/api/cart.php?action=count`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        updateAllCartBadges(data.count);
      }
    })
    .catch(() => {});
}

function updateAllCartBadges(count) {
  const badges = document.querySelectorAll('.cart-count-badge');
  badges.forEach(b => {
    b.textContent = count;
    b.style.display = count > 0 ? 'inline-flex' : 'none';
  });
}

window.addToPreOrder = function(productId, stallId, quantity = 1) {
  const btn = event?.currentTarget;
  if (btn) btn.disabled = true;

  const formData = new FormData();
  formData.append('action', 'add');
  formData.append('product_id', productId);
  formData.append('stall_id', stallId);
  formData.append('quantity', quantity);

  fetch(`${window.BASE_URL || ''}/customer/api/cart.php`, {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showPortalToast(data.message || 'Added to Pre-Order Basket! 🥬', 'success');
        updateAllCartBadges(data.total_items);
      } else {
        showPortalToast(data.message || 'Could not add to cart.', 'error');
      }
    })
    .catch(err => {
      showPortalToast('Network error while adding to basket.', 'error');
    })
    .finally(() => {
      if (btn) btn.disabled = false;
    });
};

const cartQuantityRequests = new Set();

window.updateCartItemQty = function(cartItemId, delta) {
  if (cartQuantityRequests.has(cartItemId)) return;

  const inputElem = document.getElementById(`cart-qty-${cartItemId}`);
  if (!inputElem) return;
  let currentQty = parseInt(inputElem.value, 10) || 1;
  let newQty = currentQty + delta;
  if (newQty < 1) newQty = 1;
  if (newQty === currentQty) return;

  cartQuantityRequests.add(cartItemId);
  const quantityButtons = inputElem.parentElement?.querySelectorAll('.cart-qty-btn') || [];
  quantityButtons.forEach(button => { button.disabled = true; });

  const formData = new FormData();
  formData.append('action', 'update_qty');
  formData.append('cart_item_id', cartItemId);
  formData.append('quantity', newQty);

  fetch(`${window.BASE_URL || ''}/customer/api/cart.php`, {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        inputElem.value = data.quantity ?? newQty;
        // Update subtotal cell
        const subtotalCell = document.getElementById(`cart-subtotal-${cartItemId}`);
        if (subtotalCell && data.item_subtotal) {
          subtotalCell.textContent = `Rs. ${data.item_subtotal}`;
        }
        // Update grand total
        const grandTotalElem = document.getElementById('cart-grand-total');
        if (grandTotalElem && data.grand_total) {
          grandTotalElem.textContent = `Rs. ${data.grand_total}`;
        }
        const subtotalDisplay = document.getElementById('cart-subtotal-display');
        if (subtotalDisplay && data.grand_total) {
          subtotalDisplay.textContent = `Rs. ${data.grand_total}`;
        }
        updateAllCartBadges(data.total_items);
      } else {
        showPortalToast(data.message, 'error');
      }
    })
    .catch(() => showPortalToast('Error updating item quantity.', 'error'))
    .finally(() => {
      cartQuantityRequests.delete(cartItemId);
      quantityButtons.forEach(button => { button.disabled = false; });
    });
};

window.removeCartItem = function(cartItemId) {
  if (!confirm('Remove this harvest item from your pre-order basket?')) return;

  const formData = new FormData();
  formData.append('action', 'remove');
  formData.append('cart_item_id', cartItemId);

  fetch(`${window.BASE_URL || ''}/customer/api/cart.php`, {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        const row = document.getElementById(`cart-row-${cartItemId}`);
        if (row) {
          row.style.opacity = '0';
          setTimeout(() => {
            row.remove();
            if (data.total_items === 0) {
              location.reload();
            }
          }, 300);
        }
        const grandTotalElem = document.getElementById('cart-grand-total');
        if (grandTotalElem && data.grand_total) {
          grandTotalElem.textContent = `Rs. ${data.grand_total}`;
        }
        const subtotalDisplay = document.getElementById('cart-subtotal-display');
        if (subtotalDisplay && data.grand_total) {
          subtotalDisplay.textContent = `Rs. ${data.grand_total}`;
        }
        updateAllCartBadges(data.total_items);
        showPortalToast('Item removed from basket.', 'info');
      }
    });
};

/* ==========================================================================
   3. Favorite Farmers & Products
   ========================================================================== */
function initFavoriteHandlers() {
  document.addEventListener('click', (e) => {
    const favBtn = e.target.closest('.js-fav-toggle');
    if (!favBtn) return;
    e.preventDefault();

    const type = favBtn.dataset.type; // 'product' or 'farmer'
    const id = favBtn.dataset.id;
    if (!type || !id) return;

    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('type', type);
    formData.append('id', id);

    fetch(`${window.BASE_URL || ''}/customer/api/favorite.php`, {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          if (data.is_favorite) {
            favBtn.classList.add('is-favorite');
            favBtn.innerHTML = '❤️';
            showPortalToast(data.message || 'Saved to favorites! ❤️', 'success');
          } else {
            favBtn.classList.remove('is-favorite');
            favBtn.innerHTML = '🤍';
            showPortalToast(data.message || 'Removed from favorites.', 'info');
          }
        }
      })
      .catch(() => showPortalToast('Error updating favorite state.', 'error'));
  });
}

/* ==========================================================================
   4. AI Assistant (Now managed by assets/js/ai-assistant.js)
   ========================================================================== */
function initChatbot() {
  // Handled universally by assets/js/ai-assistant.js with Gemini 3.8 Flash
}

/* ==========================================================================
   5. Star Ratings for Reviews
   ========================================================================== */
function initStarRatings() {
  const ratingPickers = document.querySelectorAll('.star-rating-picker');
  ratingPickers.forEach(picker => {
    const stars = picker.querySelectorAll('.star');
    const targetInput = document.getElementById(picker.dataset.targetInput);

    stars.forEach((star, index) => {
      star.addEventListener('mouseover', () => {
        stars.forEach((s, i) => s.classList.toggle('active', i <= index));
      });
      star.addEventListener('click', () => {
        const ratingVal = index + 1;
        if (targetInput) targetInput.value = ratingVal;
        stars.forEach((s, i) => s.classList.toggle('active', i < ratingVal));
      });
    });

    picker.addEventListener('mouseleave', () => {
      const currentVal = parseInt(targetInput?.value || 0, 10);
      stars.forEach((s, i) => s.classList.toggle('active', i < currentVal));
    });
  });
}

/* ==========================================================================
   6. Leaflet Map (OpenStreetMap) Integration
   ========================================================================== */
function initMarketMap() {
  const mapElem = document.getElementById('marketMap');
  if (!mapElem) return;

  // Dynamically load Leaflet if not present
  if (typeof L === 'undefined') {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => renderLeafletMap(mapElem);
    document.head.appendChild(script);
  } else {
    renderLeafletMap(mapElem);
  }
}

function renderLeafletMap(mapElem) {
  // Default center: Pakistan
  const map = L.map(mapElem).setView([30.3753, 69.3451], 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const dataScript = document.getElementById('marketLocationsData');
  if (!dataScript) return;

  try {
    const locations = JSON.parse(dataScript.textContent);
    const bounds = [];

    locations.forEach(loc => {
      const lat = parseFloat(loc.latitude);
      const lng = parseFloat(loc.longitude);
      if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;

      bounds.push([lat, lng]);

      // Truncate long market names for the label
      const labelName = loc.market_name.length > 22
        ? loc.market_name.substring(0, 20) + '..'
        : loc.market_name;

      // Custom named pin: green gradient pin + market name label below
      const icon = L.divIcon({
        className: '',
        html: '<div style="display:flex;flex-direction:column;align-items:center;">'
            + '<div style="width:20px;height:20px;'
            +   'background:linear-gradient(135deg,#10b981,#059669);'
            +   'border:3px solid #fff;'
            +   'border-radius:50% 50% 50% 0;'
            +   'transform:rotate(-45deg);'
            +   'box-shadow:0 3px 10px rgba(16,185,129,0.55);">'
            + '</div>'
            + '<div style="margin-top:4px;'
            +   'background:rgba(6,95,70,0.92);'
            +   'border:1.5px solid #10b981;'
            +   'color:#fff;font-size:10px;font-weight:700;'
            +   'padding:2px 7px;border-radius:5px;'
            +   'white-space:nowrap;'
            +   'box-shadow:0 2px 8px rgba(0,0,0,0.35);'
            +   'text-align:center;">'
            +   labelName
            + '</div>'
            + '</div>',
        iconSize:    [140, 50],
        iconAnchor:  [70, 23],
        popupAnchor: [0, -28]
      });

      const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
      marker.bindPopup(
        '<div style="font-family:sans-serif;min-width:210px;">'
        + '<h4 style="margin:0 0 5px 0;color:#065f46;font-size:14px;font-weight:700;">' + loc.market_name + '</h4>'
        + '<p style="margin:0 0 4px 0;font-size:12px;color:#475569;">' + loc.address + (loc.city ? ', ' + loc.city : '') + '</p>'
        + '<p style="margin:0 0 8px 0;font-size:11px;color:#059669;font-weight:600;">' + loc.operating_days + ' (' + loc.operating_hours + ')</p>'
        + '<a href="' + (window.BASE_URL || '') + '/customer/products.php?market_id=' + loc.market_id + '" '
        +   'style="display:inline-block;background:#10b981;color:#fff;text-decoration:none;padding:5px 12px;border-radius:5px;font-size:11px;font-weight:bold;">'
        +   'Browse Stalls &amp; Produce &#8594;'
        + '</a>'
        + '</div>'
      );
    });

    if (bounds.length === 1) {
      map.setView(bounds[0], 13);
    } else if (bounds.length > 1) {
      map.fitBounds(bounds, { padding: [55, 55] });
    }
  } catch (err) {
    console.error('Error rendering Leaflet map markers', err);
  }
}

/* ==========================================================================
   7. Toast Notification Helper
   ========================================================================== */
function showPortalToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.style.cssText = `
    display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem;
    border-radius: 12px; margin-bottom: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    background: ${type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#0369a1'};
    color: #ffffff; font-weight: 600; font-size: 0.875rem; animation: toastSlide 0.3s ease;
    z-index: 9999;
  `;
  toast.innerHTML = `<span>${type === 'success' ? '✔' : type === 'error' ? '✖' : 'ℹ'}</span> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}
