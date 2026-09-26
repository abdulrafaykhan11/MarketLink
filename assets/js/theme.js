/**
 * MarketLink - Theme Management Engine (Dark & Light Mode)
 * Ensures instant theme application without Flash of Unstyled Content (FOUC)
 */

(function () {
  const STORAGE_KEY = 'marketlink_theme';
  const THEME_ATTR = 'data-theme';

  // Determine initial theme: saved preference -> system preference -> fallback dark
  function getPreferredTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'dark' || saved === 'light') {
      return saved;
    }
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
      return 'light';
    }
    return 'dark'; // Default to ultra-luxurious dark mode
  }

  // Apply immediately to <html> root
  const initialTheme = getPreferredTheme();
  document.documentElement.setAttribute(THEME_ATTR, initialTheme);

  // Expose helper globally
  window.MarketLinkTheme = {
    get: () => document.documentElement.getAttribute(THEME_ATTR) || 'dark',
    set: (theme) => {
      if (theme !== 'dark' && theme !== 'light') return;
      document.documentElement.setAttribute(THEME_ATTR, theme);
      localStorage.setItem(STORAGE_KEY, theme);
      updateToggleButtons(theme);
      
      // Dispatch custom event for dynamic components
      window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    },
    toggle: () => {
      const current = window.MarketLinkTheme.get();
      const next = current === 'dark' ? 'light' : 'dark';
      window.MarketLinkTheme.set(next);
      if (window.Toast) {
        Toast.info('Theme Updated', `Switched to ${next.toUpperCase()} mode`);
      }
      return next;
    }
  };

  function updateToggleButtons(theme) {
    const btns = document.querySelectorAll('.theme-toggle-btn');
    btns.forEach(btn => {
      const icon = btn.querySelector('.theme-toggle-icon');
      if (theme === 'dark') {
        if (icon) icon.innerHTML = '<i class="fa-solid fa-sun" aria-hidden="true"></i>';
        btn.setAttribute('aria-label', 'Switch to Light Mode');
        btn.title = 'Switch to Light Mode';
      } else {
        if (icon) icon.innerHTML = '<i class="fa-solid fa-moon" aria-hidden="true"></i>';
        btn.setAttribute('aria-label', 'Switch to Dark Mode');
        btn.title = 'Switch to Dark Mode';
      }
    });
  }

  // Setup button listeners once DOM loads
  document.addEventListener('DOMContentLoaded', () => {
    updateToggleButtons(window.MarketLinkTheme.get());
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        window.MarketLinkTheme.toggle();
      });
    });
  });
})();
