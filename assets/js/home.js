/**
 * MarketLink - Homepage Interactivity & Micro-Animations
 */

document.addEventListener('DOMContentLoaded', function () {

  // ==========================================================================
  // 1. Sticky Navbar Scroll Effect
  // ==========================================================================
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', function () {
    if (window.scrollY > 30) {
      if (header) header.classList.add('scrolled');
    } else {
      if (header) header.classList.remove('scrolled');
    }
  });

  // ==========================================================================
  // 2. Mobile Menu Toggle
  // ==========================================================================
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

  // ==========================================================================
  // 3. Interactive Hero Market Search Bar
  // ==========================================================================
  const heroSearchForm = document.getElementById('heroSearchForm');
  const marketLocationSelect = document.getElementById('marketLocation');
  const produceCategorySelect = document.getElementById('produceCategory');

  if (heroSearchForm) {
    heroSearchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const loc = marketLocationSelect ? marketLocationSelect.value : 'all';
      const cat = produceCategorySelect ? produceCategorySelect.value : 'all';

      // Smooth scroll to stalls section
      const stallsSec = document.getElementById('local-stalls');
      if (stallsSec) {
        stallsSec.scrollIntoView({ behavior: 'smooth' });
      }

      if (window.Toast) {
        Toast.success('Stalls Filtered', `Showing active stalls for ${cat !== 'all' ? cat : 'all produce'} near ${loc !== 'all' ? loc : 'all locations'}.`);
      }
    });
  }

  // ==========================================================================
  // 4. FAQ Accordion Logic
  // ==========================================================================
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const btn = item.querySelector('.faq-question-btn');
    if (btn) {
      btn.addEventListener('click', () => {
        const isActive = item.classList.contains('active');
        // Close others
        faqItems.forEach(other => other.classList.remove('active'));
        if (!isActive) {
          item.classList.add('active');
        }
      });
    }
  });

  // ==========================================================================
  // 5. Footer Newsletter Subscribe
  // ==========================================================================
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const emailInput = document.getElementById('newsletterEmail');
      const status = document.getElementById('newsletterStatus');
      const submitBtn = newsletterForm.querySelector('button[type="submit"]');
      const val = emailInput ? emailInput.value.trim() : '';

      if (!val || !val.includes('@')) {
        if (window.Toast) {
          Toast.error('Invalid Email', 'Please enter a valid email address.');
        }
        return;
      }

      if (status) {
        status.className = 'newsletter-status';
        status.textContent = 'Saving your subscription…';
      }
      if (submitBtn) submitBtn.disabled = true;

      try {
        const response = await fetch(newsletterForm.action, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ email: val })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.error || 'Subscription could not be completed.');

        if (status) {
          status.className = 'newsletter-status is-success';
          status.textContent = result.message;
        }
        if (window.Toast) Toast.success('Subscribed!', result.message);
        newsletterForm.reset();
      } catch (error) {
        const message = error.message || 'Please try again in a moment.';
        if (status) {
          status.className = 'newsletter-status is-error';
          status.textContent = message;
        }
        if (window.Toast) Toast.error('Subscription failed', message);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // ==========================================================================
  // 6. Smooth Scrolling for Internal Hash Links
  // ==========================================================================
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

  // ==========================================================================
  // 7. Hero Card Deck Shuffle Animation
  // ==========================================================================
  (function initHeroDeckShuffle() {
    const deck = document.getElementById('heroDeckContainer');
    if (!deck) return;

    const cards = Array.from(deck.querySelectorAll('.hero-deck-card'));
    const stateOrder = ['front', 'peek-1', 'peek-2', 'peek-3'];
    let isAnimating = false;

    /**
     * Reads current state of all cards and returns the sorted order
     * front card first, then peek-1, peek-2, peek-3
     */
    function getOrderedCards() {
      return stateOrder.map(state =>
        cards.find(c => c.dataset.state === state)
      ).filter(Boolean);
    }

    /**
     * Perform one shuffle step:
     *  - Front card → shuffling-out (flies left)
     *  - peek-1 → front, peek-2 → peek-1, peek-3 → peek-2
     *  - Old front → instantly placed at shuffling-in, then promoted to peek-3
     */
    function shuffleDeck() {
      if (isAnimating) return;
      isAnimating = true;

      const ordered = getOrderedCards();
      if (ordered.length < 2) { isAnimating = false; return; }

      const [frontCard, peek1, peek2, peek3] = ordered;

      // Step 1: Fly the front card out
      frontCard.dataset.state = 'shuffling-out';

      // Step 2: After fly-out transition, reshuffle positions
      setTimeout(() => {
        // Place old front behind invisibly (no transition)
        frontCard.dataset.state = 'shuffling-in';

        // Force reflow so the instant position takes effect
        void frontCard.offsetWidth;

        // Step 3: Promote remaining cards forward
        if (peek1) peek1.dataset.state = 'front';
        if (peek2) peek2.dataset.state = 'peek-1';
        if (peek3) peek3.dataset.state = 'peek-2';

        // Step 4: Slide old front in as peek-3
        setTimeout(() => {
          frontCard.dataset.state = 'peek-3';
          // Re-enable morph animation on new front
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

    // Auto-shuffle every 2.5 seconds
    let deckTimer = setInterval(shuffleDeck, 2500);

    // Click on any peeking card to bring it to front immediately
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

    // Pause shuffle on hover
    deck.addEventListener('mouseenter', () => clearInterval(deckTimer));
    deck.addEventListener('mouseleave', () => {
      clearInterval(deckTimer);
      deckTimer = setInterval(shuffleDeck, 2500);
    });
  })();

});
