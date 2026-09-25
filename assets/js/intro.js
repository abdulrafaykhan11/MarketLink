/* MarketLink - Scroll-Driven Intro Engine */
(() => {
  'use strict';

  const TOTAL_SCROLL = 5200;
  const BASE = document.documentElement.dataset.baseUrl || '/MarketLink';
  const sel = (id) => document.getElementById(id);

  const stage        = sel('intro-stage');
  const harvest      = sel('scene-harvest');
  const packed       = sel('scene-packed');
  const picked       = sel('scene-picked');
  const basket       = sel('intro-basket');
  const bgText       = sel('intro-bg-text');
  const glow         = sel('intro-glow');
  const greenGlow    = sel('intro-glow-green');
  const scrollHint   = sel('intro-scroll-hint');

  // Produce items - positioned RELATIVE to basket visually via JS
  const produceEls = [
    sel('p-broccoli'),
    sel('p-corn'),
    sel('p-pineapple'),
    sel('p-tomato'),
    sel('p-carrot'),
    sel('p-grapes'),
  ];

  // Each produce item: offset from basket center in px (at scale=1)
  // Basket is roughly 350px wide at desktop, centered at (50% + some offset)
  // We position produce ABSOLUTE on the stage, anchored near the basket
  const produceOffsets = [
    { dx: -55, dy: -135 },   // broccoli - upper left of basket
    { dx:  80, dy: -155 },   // corn     - upper right
    { dx: 130, dy: -65  },   // pineapple - right
    { dx:  40, dy: -100 },   // tomato   - center-upper
    { dx: -95, dy: -100 },   // carrot   - left
    { dx: -110, dy: -40 },   // grapes   - lower left
  ];

  let current = 0, target = 0, rafId, isExiting = false, touchY = 0;

  // -- Math helpers --
  const clamp  = (v, lo = 0, hi = 1) => Math.min(hi, Math.max(lo, v));
  const range  = (v, a, b) => clamp((v - a) / (b - a));
  const smooth = (t) => t * t * (3 - 2 * t);
  const lerp   = (a, b, t) => a + (b - a) * t;
  const fade   = (p, a, b, c, d) => smooth(range(p, a, b)) * (1 - smooth(range(p, c, d)));

  // -- Get basket visual center on stage --
  function getBasketCenter() {
    const rect = basket.getBoundingClientRect();
    return {
      x: rect.left + rect.width  / 2,
      y: rect.top  + rect.height / 2,
      w: rect.width,
      h: rect.height
    };
  }

  // -- Render --
  function render() {
    const p = clamp(current / TOTAL_SCROLL);

    // SCENE 1: Daily Harvest fade out
    const harvestOut = smooth(range(p, 0.24, 0.37));
    harvest.style.opacity = String(1 - harvestOut);
    harvest.style.transform = 'translate3d(0,' + lerp(0, -60, harvestOut) + 'px,0)';

    // Scroll hint
    scrollHint.style.opacity = String(1 - smooth(range(p, 0.025, 0.12)));

    // BG text parallax
    const bgOpacity = Math.max(0.035, 0.10 - 0.055 * smooth(range(p, 0.31, 0.55)) + 0.055 * smooth(range(p, 0.74, 0.93)));
    bgText.style.opacity = String(bgOpacity);
    bgText.style.transform = 'translate3d(-42%, calc(-50% - ' + (current * 0.035) + 'px), 0)';

    // Glow
    glow.style.opacity = String(Math.max(0, 1 - 0.65 * smooth(range(p, 0.30, 0.53)) + 0.65 * smooth(range(p, 0.76, 0.94))));
    glow.style.transform = 'translate3d(-50%, calc(-50% - ' + (current * 0.018) + 'px), 0)';
    greenGlow.style.opacity = String(fade(p, 0.66, 0.78, 0.87, 0.96));

    // BASKET
    let bx = 0, by = 0, bscale = 1, brot = 0, bopacity = 1;
    if (p < 0.30) {
      const t = smooth(range(p, 0, 0.30));
      bx = lerp(0, -8, t); by = lerp(0, -14, t);
      bscale = lerp(1, 1.05, t); brot = lerp(0, -2, t);
    } else if (p < 0.52) {
      const t = smooth(range(p, 0.30, 0.52));
      bx = lerp(-8, -38, t); by = lerp(-14, -42, t);
      bscale = lerp(1.05, 1.25, t); brot = lerp(-2, 2, t);
    } else if (p < 0.68) {
      const t = smooth(range(p, 0.52, 0.68));
      bx = lerp(-38, -50, t); by = lerp(-42, -50, t);
      bscale = lerp(1.25, 1.35, t); brot = lerp(2, 5, t);
      bopacity = lerp(1, 0.13, t);
    } else if (p < 0.84) {
      const t = smooth(range(p, 0.68, 0.84));
      bx = -50; by = -50;
      bscale = lerp(1.35, 1.14, t); brot = lerp(5, 0, t);
      bopacity = lerp(0.13, 1, t);
    } else {
      // p >= 0.84: hold at centered position — NO loop-back
      bx = -50; by = -50;
      bscale = 1.14;
      bopacity = 1;
    }
    basket.style.opacity = String(bopacity);
    basket.style.transform = 'translate3d(calc(-20% + ' + bx + '%), calc(-50% + ' + by + '%), 0) scale(' + bscale + ') rotate(' + brot + 'deg)';

    // PRODUCE - positioned relative to basket center
    const produceVis = fade(p, 0.65, 0.81, 0.87, 0.97);
    const timeNow = performance.now() / 1700;
    const bc = getBasketCenter();

    produceEls.forEach((el, i) => {
      if (!el) return;
      const offset = produceOffsets[i];
      // Scale offset by basket scale ratio (compared to base 350px width)
      const sizeRatio = bc.w / 350;
      const cx = bc.x + offset.dx * sizeRatio;
      const cy = bc.y + offset.dy * sizeRatio;

      const vis = clamp(produceVis - i * 0.035);
      const floatY = Math.sin(timeNow + i * 1.15) * 6;
      const floatX = Math.cos(timeNow * 0.8 + i) * 3;
      const rot    = Math.sin(timeNow * 0.5 + i) * 4;

      el.style.opacity = String(vis);
      // Position: left/top are the element top-left corner
      // We want center of element at (cx, cy), element is 100% wide img
      const elW = el.offsetWidth  || 90;
      const elH = el.offsetHeight || 90;
      el.style.left = '0';
      el.style.top  = '0';
      el.style.transform = 'translate3d(' + (cx - elW / 2 + floatX) + 'px, ' + (cy - elH / 2 + floatY) + 'px, 0) scale(' + vis + ') rotate(' + rot + 'deg)';
    });

    // SCENE 2/3: Your Basket Is Packed
    const packedVis = fade(p, 0.35, 0.48, 0.67, 0.77);
    packed.style.opacity = String(packedVis);
    packed.style.transform = 'translate3d(0,' + lerp(36, -28, range(p, 0.35, 0.77)) + 'px,0)';
    packed.style.pointerEvents = packedVis > 0.05 ? 'all' : 'none';

    // SCENE 4: Freshly Picked
    const pickedVis = fade(p, 0.70, 0.80, 0.88, 0.97);
    picked.style.opacity = String(pickedVis);
    picked.style.transform = 'translate3d(' + lerp(-36, 0, range(p, 0.70, 0.80)) + 'px,0,0)';
  }

  // -- Loop --
  let autoExitTimer = null;
  function tick() {
    if (isExiting) return;
    current += (target - current) * 0.1;
    if (Math.abs(target - current) < 0.05) current = target;
    render();
    // Auto-exit when user has scrolled to the very end and holds there
    if (target >= TOTAL_SCROLL && current >= TOTAL_SCROLL - 5) {
      if (!autoExitTimer) {
        autoExitTimer = setTimeout(() => exitIntro(), 600);
      }
    } else {
      if (autoExitTimer) { clearTimeout(autoExitTimer); autoExitTimer = null; }
    }
    rafId = requestAnimationFrame(tick);
  }

  // -- Input --
  function addScroll(delta) {
    if (isExiting) return;
    target = clamp(target + delta, 0, TOTAL_SCROLL);
  }

  function onWheel(e) { e.preventDefault(); addScroll(e.deltaY); }
  function onTouchStart(e) { touchY = e.touches[0].clientY; }
  function onTouchMove(e) {
    e.preventDefault();
    const ny = e.touches[0].clientY;
    addScroll((touchY - ny) * 2.25);
    touchY = ny;
  }
  function onKey(e) {
    const map = { ArrowDown: 220, PageDown: 600, ' ': 420, ArrowUp: -220, PageUp: -600, Home: -TOTAL_SCROLL, End: TOTAL_SCROLL };
    if (Object.prototype.hasOwnProperty.call(map, e.key)) { e.preventDefault(); addScroll(map[e.key]); }
  }

  // -- Exit --
  async function exitIntro(e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    if (isExiting) return;
    isExiting = true;
    cancelAnimationFrame(rafId);
    window.removeEventListener('wheel',      onWheel);
    window.removeEventListener('touchstart', onTouchStart);
    window.removeEventListener('touchmove',  onTouchMove);
    window.removeEventListener('keydown',    onKey);

    stage.style.transition = 'opacity .5s cubic-bezier(.4,0,.2,1)';
    stage.style.opacity    = '0';

    try {
      await fetch(BASE + '/api/intro_done.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
    } catch (_) {}

    setTimeout(() => window.location.assign(BASE + '/index.php'), 520);
  }

  // -- Wire buttons --
  // HOME PAGE button (top right nav)
  sel('intro-home-btn')?.addEventListener('click', exitIntro);

  // GO TO HOMEPAGE button (scene 2 CTA) - only this, no side button
  document.querySelectorAll('.intro-exit').forEach(btn => btn.addEventListener('click', exitIntro));

  // Event listeners
  window.addEventListener('wheel',      onWheel,      { passive: false });
  window.addEventListener('touchstart', onTouchStart, { passive: true  });
  window.addEventListener('touchmove',  onTouchMove,  { passive: false });
  window.addEventListener('keydown',    onKey);

  // Start
  render();
  rafId = requestAnimationFrame(tick);
})();
