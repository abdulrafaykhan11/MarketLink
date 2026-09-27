/**
 * MarketLink - Homepage Interactivity & Micro-Animations
 */

document.addEventListener('DOMContentLoaded', function () {
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', function () {
    if (window.scrollY > 30) {
      if (header) header.classList.add('scrolled');
    } else {
      if (header) header.classList.remove('scrolled');
    }
  });

  const mobileBtn = document.getElementById('mobileMenuBtn');
  const navMenu = document.getElementById('navMenu');
  if (mobileBtn && navMenu) {
    mobileBtn.addEventListener('click', function () {
      const isOpen = navMenu.classList.toggle('is-open');
      mobileBtn.setAttribute('aria-expanded', String(isOpen));
      if (header) header.classList.toggle('menu-open', isOpen);
    });
    navMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
      navMenu.classList.remove('is-open');
      mobileBtn.setAttribute('aria-expanded', 'false');
      if (header) header.classList.remove('menu-open');
    }));
  }

  const heroSearchForm = document.getElementById('heroSearchForm');
  const marketLocationSelect = document.getElementById('marketLocation');
  const produceCategorySelect = document.getElementById('produceCategory');
  if (heroSearchForm) {
    heroSearchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const loc = marketLocationSelect ? marketLocationSelect.value : 'all';
      const cat = produceCategorySelect ? produceCategorySelect.value : 'all';
      const stallsSec = document.getElementById('local-stalls');
      if (stallsSec) stallsSec.scrollIntoView({ behavior: 'smooth' });
      if (window.Toast) {
        Toast.success('Stalls Filtered', 'Showing active stalls for ' + (cat !== 'all' ? cat : 'all produce') + ' near ' + (loc !== 'all' ? loc : 'all locations') + '.');
      }
    });
  }

  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const btn = item.querySelector('.faq-question-btn');
    if (btn) {
      btn.addEventListener('click', () => {
        const isActive = item.classList.contains('active');
        faqItems.forEach(other => other.classList.remove('active'));
        if (!isActive) item.classList.add('active');
      });
    }
  });

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href').substring(1);
      const targetEl = document.getElementById(targetId);
      if (targetEl) {
        e.preventDefault();
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  (function initHeroDeckShuffle() {
    const deck = document.getElementById('heroDeckContainer');
    if (!deck) return;

    const cards = Array.from(deck.querySelectorAll('.hero-deck-card'));
    const stateOrder = ['front', 'peek-1', 'peek-2', 'peek-3'];
    let isAnimating = false;

    function getOrderedCards() {
      return stateOrder.map(state => cards.find(card => card.dataset.state === state)).filter(Boolean);
    }

    function shuffleDeck() {
      if (isAnimating) return;
      isAnimating = true;
      const [frontCard, peek1, peek2, peek3] = getOrderedCards();
      if (!frontCard || !peek1) {
        isAnimating = false;
        return;
      }

      frontCard.dataset.state = 'shuffling-out';
      setTimeout(() => {
        frontCard.dataset.state = 'shuffling-in';
        void frontCard.offsetWidth;
        if (peek1) peek1.dataset.state = 'front';
        if (peek2) peek2.dataset.state = 'peek-1';
        if (peek3) peek3.dataset.state = 'peek-2';

        setTimeout(() => {
          frontCard.dataset.state = 'peek-3';
          const newFront = deck.querySelector('[data-state="front"]');
          if (newFront) {
            newFront.style.animation = 'none';
            void newFront.offsetWidth;
            newFront.style.animation = '';
          }
          setTimeout(() => { isAnimating = false; }, 950);
        }, 60);
      }, 680);
    }

    let deckTimer = setInterval(shuffleDeck, 2500);
    cards.forEach(card => {
      card.addEventListener('click', function () {
        const state = this.dataset.state;
        if (state !== 'front' && state !== 'shuffling-out' && state !== 'shuffling-in') {
          clearInterval(deckTimer);
          shuffleDeck();
          deckTimer = setInterval(shuffleDeck, 2500);
        }
      });
    });

    deck.addEventListener('mouseenter', () => clearInterval(deckTimer));
    deck.addEventListener('mouseleave', () => {
      clearInterval(deckTimer);
      deckTimer = setInterval(shuffleDeck, 2500);
    });
  })();
});
