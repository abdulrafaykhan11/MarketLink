/**
 * MarketLink - Toast Notification System
 * Non-blocking, beautiful floating feedback messages
 */

const Toast = (function () {
  let container = null;

  function initContainer() {
    if (!container) {
      container = document.getElementById('toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
      }
    }
    return container;
  }

  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
  };

  function show(type, title, message, duration = 4500) {
    const parent = initContainer();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    toast.innerHTML = `
      <div class="toast-icon">${icons[type] || '•'}</div>
      <div class="toast-content">
        <div class="toast-title">${title}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close" type="button" aria-label="Close">&times;</button>
    `;

    const closeBtn = toast.querySelector('.toast-close');
    const dismiss = () => {
      if (toast.classList.contains('toast-hiding')) return;
      toast.classList.add('toast-hiding');
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    };

    closeBtn.addEventListener('click', dismiss);

    parent.appendChild(toast);

    if (duration > 0) {
      setTimeout(dismiss, duration);
    }

    return toast;
  }

  return {
    success: (title, msg, dur) => show('success', title, msg, dur),
    error: (title, msg, dur) => show('error', title, msg, dur),
    warning: (title, msg, dur) => show('warning', title, msg, dur),
    info: (title, msg, dur) => show('info', title, msg, dur)
  };
})();

// Attach globally
window.Toast = Toast;
