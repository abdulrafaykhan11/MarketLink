/**
 * MarketLink - Scroll-Triggered Animations (Section-Level & Element-Level)
 * Pure Vanilla JS (Intersection Observer API) + CSS transitions — Zero external libraries
 * Respects prefers-reduced-motion
 */

(function () {
  'use strict';

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ─── 0. Section-Level Observer (.section-reveal → .in-view) ───────────────
  // Watches ALL sections at once with threshold: 0.15 (fires when ~15% is visible)
  // Each section only animates the FIRST time it enters viewport (unobserve)
  function initSectionReveal() {
    const sections = document.querySelectorAll('.section-reveal');
    if (!sections.length) return;

    if (prefersReduced) {
      sections.forEach(s => s.classList.add('in-view'));
      return;
    }

    const obs = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          // Only animate the FIRST time — never re-trigger on reverse scroll
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.15
    });

    sections.forEach(sec => obs.observe(sec));
  }

  // ─── 0b. Background Parallax Between Sections ────────────────────────────
  // Each section's background shifts at a slightly different scroll speed
  // (multiplier between 0.05 - 0.09) for cinematic depth between sections
  function initSectionParallax() {
    if (prefersReduced) return;

    const parallaxTargets = document.querySelectorAll(
      '.hero-fullscreen-bg, .bento-story-section, .stalls-section, .testimonials-section, .audience-section, .faq-section, .cta-section, .site-footer'
    );
    if (!parallaxTargets.length) return;

    let ticking = false;

    function updateParallax() {
      const vh = window.innerHeight;
      parallaxTargets.forEach((sec, idx) => {
        const rect = sec.getBoundingClientRect();
        // Only compute for sections currently near or within the viewport
        if (rect.bottom > -80 && rect.top < vh + 80) {
          const multiplier = 0.05 + ((idx % 4) * 0.012);
          const offset = (vh * 0.5 - (rect.top + rect.height * 0.5)) * multiplier;

          sec.style.setProperty('--parallax-y', `${offset.toFixed(1)}px`);

          const bgLayer = sec.querySelector('.hero-fullscreen-bg, .section-parallax-bg');
          if (bgLayer) {
            bgLayer.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
          } else {
            sec.style.backgroundPositionY = `calc(50% + ${offset.toFixed(1)}px)`;
          }
        }
      });
      ticking = false;
    }

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
      }
    }, { passive: true });

    // Initial pass on load
    requestAnimationFrame(updateParallax);
  }

  // ─── 1. Reusable .reveal → .active Observer ──────────────────────────────
  function initRevealObserver() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    if (prefersReduced) {
      els.forEach(el => el.classList.add('active'));
      return;
    }
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('active');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(el => obs.observe(el));
  }

  // ─── 2. Navbar ───────────────────────────────────────────────────────────
  function initNavbar() {
    const header = document.getElementById('siteHeader');
    if (!header) return;
    if (!prefersReduced) {
      header.style.opacity = '0';
      header.style.transform = 'translateY(-100%)';
      header.style.transition = 'opacity 0.55s ease-out, transform 0.55s cubic-bezier(0.16,1,0.3,1)';
      requestAnimationFrame(() => setTimeout(() => {
        header.style.opacity = '1';
        header.style.transform = 'translateY(0)';
      }, 60));
    }
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          header.classList.toggle('scrolled', window.scrollY > 50);
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
  }

  // ─── 3. Hero Section Elements ────────────────────────────────────────────
  // Heading groups fade/rise with stagger 220ms after hero section entrance
  function initHero() {
    if (prefersReduced) return;
    document.querySelectorAll('.hero-heading-group, .hero-mobile-kicker, .hero-mobile-panel h1, .hero-mobile-panel p').forEach((el, i) => {
      el.style.cssText = 'opacity:0;transform:translateY(28px);transition:opacity 0.6s ease-out,transform 0.6s cubic-bezier(0.16,1,0.3,1)';
      setTimeout(() => {
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      }, 220 + i * 140);
    });

    const heroImg = document.querySelector('.hero-farm-image, .hero-visual-img, .hero-image-wrap img');
    if (heroImg) {
      window.addEventListener('scroll', () => {
        heroImg.style.transform = 'translateY(' + (window.scrollY * 0.18) + 'px)';
      }, { passive: true });
    }

    const heroSection = document.querySelector('.hero-section');
    if (heroSection && !document.querySelector('.scroll-down-arrow')) {
      const arrow = document.createElement('div');
      arrow.className = 'scroll-down-arrow';
      arrow.setAttribute('aria-label', 'Scroll down');
      arrow.innerHTML = '<span class="scroll-arrow-icon">&#8595;</span>';
      arrow.addEventListener('click', () => {
        const next = heroSection.nextElementSibling;
        if (next) next.scrollIntoView({ behavior: 'smooth' });
      });
      heroSection.appendChild(arrow);
    }
  }

  // ─── 4. Journey Steps (0.2s Stagger After Section Entrance) ───────────────
  function initJourneySteps() {
    const steps = document.querySelectorAll('.bento-step-item, .journey-step, .step-card');
    if (!steps.length) return;
    if (prefersReduced) return;

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        // Base delay 200ms ensures section entrance completes first
        const delay = 200 + ((parseInt(el.dataset.stepIndex || 0)) * 140);
        setTimeout(() => {
          el.style.opacity = '1';
          el.style.transform = 'translateX(0)';
        }, delay);
        const icon = el.querySelector('.bento-step-icon-wrap, .step-icon, .journey-icon');
        if (icon) {
          setTimeout(() => {
            icon.style.animation = 'stepIconBounce 0.5s cubic-bezier(0.36,0.07,0.19,0.97)';
          }, delay + 180);
        }
        obs.unobserve(el);
      });
    }, { threshold: 0.15 });

    steps.forEach((el, i) => {
      el.dataset.stepIndex = i;
      el.style.cssText += 'opacity:0;transform:translateX(-36px);transition:opacity 0.55s ease-out,transform 0.55s cubic-bezier(0.16,1,0.3,1)';
      obs.observe(el);
    });

    const line = document.querySelector('.bento-step-connector, .journey-connector-line, .steps-connector');
    if (line) {
      line.style.height = '0';
      line.style.transition = 'height 1.2s cubic-bezier(0.16,1,0.3,1)';
      const lo = new IntersectionObserver((e) => {
        if (e[0].isIntersecting) {
          setTimeout(() => { line.style.height = '100%'; }, 250);
          lo.disconnect();
        }
      }, { threshold: 0.1 });
      lo.observe(line.parentElement || line);
    }
  }

  // ─── 5. Comparison Cards ─────────────────────────────────────────────────
  function initComparisonCards() {
    const cards = document.querySelectorAll('.broken-reason-item, .comparison-card, .compare-card');
    if (!cards.length || prefersReduced) return;

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const idx = parseInt(entry.target.dataset.compIdx || 0);
        setTimeout(() => {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }, 200 + (idx * 100));
        obs.unobserve(entry.target);
      });
    }, { threshold: 0.12 });

    cards.forEach((el, i) => {
      el.dataset.compIdx = i;
      el.style.cssText += 'opacity:0;transform:translateY(24px);transition:opacity 0.5s ease-out,transform 0.5s cubic-bezier(0.16,1,0.3,1),box-shadow 0.3s ease,border-color 0.3s ease';
      obs.observe(el);
    });
  }

  // ─── 6. Stats — Count-up & Progress Bars (Delayed After Entrance) ─────────
  function initStats() {
    document.querySelectorAll('[data-count-to]').forEach(el => {
      const obs = new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting) return;
        const target = parseFloat(el.dataset.countTo);
        const duration = prefersReduced ? 0 : 1400;
        const suffix = el.dataset.suffix || '';
        const start = performance.now();
        function tick(now) {
          const p = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = (Number.isInteger(target) ? Math.round(target * eased) : (target * eased).toFixed(1)) + suffix;
          if (p < 1) requestAnimationFrame(tick);
        }
        setTimeout(() => requestAnimationFrame(tick), 200);
        obs.unobserve(el);
      }, { threshold: 0.2 });
      obs.observe(el);
    });

    const bars = document.querySelectorAll('.eff-bar-fill, .slots-progress-fill, .bio-gauge-bar, .comparison-bar-fill, .progress-bar-fill, .stat-bar-fill');
    bars.forEach(bar => {
      if (!bar.dataset.targetWidth) {
        bar.dataset.targetWidth = bar.style.width || getComputedStyle(bar).width;
      }
      const obs = new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting) return;
        if (!prefersReduced) {
          bar.style.width = '0%';
          bar.style.transition = 'width 1s cubic-bezier(0.16,1,0.3,1)';
          // Starts 220ms after section enters
          setTimeout(() => {
            bar.style.width = bar.dataset.targetWidth;
          }, 220);
        }
        obs.unobserve(bar);
      }, { threshold: 0.15 });
      obs.observe(bar);
    });
  }

  // ─── 7. Stall Cards (0.2s Stagger After Section Entrance) ─────────────────
  function initStallCards() {
    const grid = document.querySelector('.stalls-bento-grid, .stalls-grid');
    if (!grid || prefersReduced) return;
    const cards = grid.querySelectorAll('.stall-bento-card, [class*="stall-card"]');
    if (!cards.length) return;

    cards.forEach(c => {
      c.style.opacity = '0';
      c.style.transform = 'translateY(24px)';
      c.style.transition = 'opacity 0.55s ease-out,transform 0.55s cubic-bezier(0.16,1,0.3,1),box-shadow 0.3s ease,border-color 0.3s ease';
    });

    const obs = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting) return;
      cards.forEach((card, i) => {
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
          const bar = card.querySelector('.slots-progress-fill, [class*="avail-bar"], .availability-bar');
          if (bar) {
            const tw = bar.dataset.targetWidth || bar.style.width || '80%';
            bar.style.width = '0%';
            bar.style.transition = 'width 0.9s cubic-bezier(0.16,1,0.3,1)';
            setTimeout(() => { bar.style.width = tw; }, 120);
          }
        }, 200 + i * 90);
      });
      obs.disconnect();
    }, { threshold: 0.1 });
    obs.observe(grid);
  }

  // ─── 8. CTA Section ──────────────────────────────────────────────────────
  function initCTA() {
    if (prefersReduced) return;
    const target = document.querySelector('#cta > .section-container > div') ||
      document.querySelector('.cta-box') ||
      document.querySelector('section:last-of-type > .section-container > div');
    if (!target) return;

    target.style.opacity = '0';
    target.style.transform = 'scale(0.95)';
    target.style.transition = 'opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1)';

    const obs = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        setTimeout(() => {
          target.style.opacity = '1';
          target.style.transform = 'scale(1)';
        }, 180);
        obs.disconnect();
      }
    }, { threshold: 0.15 });
    obs.observe(target);
  }

  // ─── 9. Footer ───────────────────────────────────────────────────────────
  function initFooter() {
    const footer = document.querySelector('footer, .site-footer');
    if (!footer || prefersReduced) return;
    footer.style.opacity = '0';
    footer.style.transition = 'opacity 0.8s cubic-bezier(0.16,1,0.3,1)';
    const obs = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) {
        footer.style.opacity = '1';
        obs.disconnect();
      }
    }, { threshold: 0.05 });
    obs.observe(footer);
  }

  // ─── 10. Testimonials — Diverse Per-Card Entrance Animations ─────────────
  function initTestimonials() {
    const grid = document.querySelector('.testimonials-grid');
    if (!grid) return;
    const cards = grid.querySelectorAll('.testimonial-card');
    if (!cards.length) return;

    const animVariants = [
      'anim-fade-left',
      'anim-fade-up',
      'anim-fade-right',
      'anim-tilt-left',
      'anim-scale-pop',
      'anim-tilt-right'
    ];

    cards.forEach((c, i) => {
      // Ensure distinct animation class is present
      const animClass = animVariants[i % animVariants.length];
      if (!c.classList.contains(animClass)) {
        c.classList.add(animClass);
      }
      // Clear any conflicting inline transform or opacity
      c.style.removeProperty('opacity');
      c.style.removeProperty('transform');

      if (prefersReduced) {
        c.classList.add('in-view');
      }
    });

    if (prefersReduced) return;

    const obs = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting) return;
      cards.forEach((c, i) => {
        setTimeout(() => {
          c.classList.add('in-view');
        }, 180 + i * 130); // Smooth cascading entry (card 1, then card 2, etc.)
      });
      obs.disconnect();
    }, { threshold: 0.1 });
    obs.observe(grid);
  }

  // ─── INIT ─────────────────────────────────────────────────────────────────
  function init() {
    initSectionReveal();
    initSectionParallax();
    initRevealObserver();
    initNavbar();
    initHero();
    initJourneySteps();
    initComparisonCards();
    initStats();
    initStallCards();
    initCTA();
    initFooter();
    initTestimonials();
  }

  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
})();
