/* MarketLink — scroll-driven first-entry experience. */
(() => {
  'use strict';
  const TOTAL_SCROLL = 5200;
  const BASE = document.documentElement.dataset.baseUrl || '/MarketLink';
  const $ = (selector) => document.querySelector(selector);
  const stage = $('#intro-stage'), harvest = $('#scene-harvest'), packed = $('#scene-packed');
  const picked = $('#scene-picked'), basket = $('#intro-basket'), backgroundText = $('#intro-bg-text');
  const glow = $('#intro-glow'), greenGlow = $('#intro-glow-green'), scrollHint = $('#intro-scroll-hint');
  const produce = ['#p-broccoli', '#p-corn', '#p-pineapple', '#p-tomato', '#p-carrot', '#p-grapes'].map($);
  let current = 0, target = 0, frame, isExiting = false, touchY = 0;

  const clamp = (value, min = 0, max = 1) => Math.min(max, Math.max(min, value));
  const range = (value, start, end) => clamp((value - start) / (end - start));
  const smooth = (value) => value * value * (3 - 2 * value);
  const lerp = (from, to, amount) => from + ((to - from) * amount);
  const fade = (p, a, b, c, d) => smooth(range(p, a, b)) * (1 - smooth(range(p, c, d)));
  const setTransform = (el, value) => { if (el) el.style.transform = value; };

  function render() {
    const p = clamp(current / TOTAL_SCROLL);
    const harvestOut = smooth(range(p, .24, .37));
    harvest.style.opacity = String(1 - harvestOut);
    setTransform(harvest, `translate3d(0, ${lerp(0, -60, harvestOut)}px, 0)`);
    scrollHint.style.opacity = String(1 - smooth(range(p, .025, .12)));

    const backdropOpacity = .10 - (.055 * smooth(range(p, .31, .55))) + (.055 * smooth(range(p, .74, .93)));
    backgroundText.style.opacity = String(Math.max(.035, backdropOpacity));
    setTransform(backgroundText, `translate3d(-42%, calc(-50% - ${current * .035}px), 0)`);
    glow.style.opacity = String(1 - (.65 * smooth(range(p, .30, .53))) + (.65 * smooth(range(p, .76, .94))));
    setTransform(glow, `translate3d(-50%, calc(-50% - ${current * .018}px), 0)`);
    greenGlow.style.opacity = String(fade(p, .66, .78, .87, .96));

    let x = 0, y = 0, scale = 1, rotation = 0, opacity = 1;
    if (p < .30) { const t = smooth(range(p, 0, .30)); x = lerp(0, -8, t); y = lerp(0, -14, t); scale = lerp(1, 1.05, t); rotation = lerp(0, -2, t); }
    else if (p < .52) { const t = smooth(range(p, .30, .52)); x = lerp(-8, -38, t); y = lerp(-14, -42, t); scale = lerp(1.05, 1.25, t); rotation = lerp(-2, 2, t); }
    else if (p < .68) { const t = smooth(range(p, .52, .68)); x = lerp(-38, -50, t); y = lerp(-42, -50, t); scale = lerp(1.25, 1.35, t); rotation = lerp(2, 5, t); opacity = lerp(1, .13, t); }
    else if (p < .84) { const t = smooth(range(p, .68, .84)); x = -50; y = -50; scale = lerp(1.35, 1.14, t); rotation = lerp(5, 0, t); opacity = lerp(.13, 1, t); }
    else { const t = smooth(range(p, .84, 1)); x = lerp(-50, 0, t); y = lerp(-50, 0, t); scale = lerp(1.14, 1, t); }
    basket.style.opacity = String(opacity);
    setTransform(basket, `translate3d(calc(-20% + ${x}%), calc(-50% + ${y}%), 0) scale(${scale}) rotate(${rotation}deg)`);

    const produceVisibility = fade(p, .65, .81, .87, .97), time = performance.now() / 1700;
    produce.forEach((item, index) => {
      const visible = clamp(produceVisibility - (index * .035));
      item.style.opacity = String(visible);
      setTransform(item, `scale(${visible}) translate3d(${Math.cos((time * .8) + index) * 3}px, ${Math.sin(time + (index * 1.15)) * 6}px, 0) rotate(${Math.sin(time * .5 + index) * 4}deg)`);
    });
    const packedVisible = fade(p, .35, .48, .67, .77);
    packed.style.opacity = String(packedVisible);
    setTransform(packed, `translate3d(0, ${lerp(36, -28, range(p, .35, .77))}px, 0)`);
    const pickedVisible = fade(p, .70, .80, .88, .97);
    picked.style.opacity = String(pickedVisible);
    setTransform(picked, `translate3d(${lerp(-36, 0, range(p, .70, .80))}px, 0, 0)`);
  }
  function tick() { if (isExiting) return; current += (target - current) * .1; if (Math.abs(target - current) < .05) current = target; render(); frame = requestAnimationFrame(tick); }
  function move(delta) { target = clamp(target + delta, 0, TOTAL_SCROLL); }
  function wheel(event) { if (!isExiting) { event.preventDefault(); move(event.deltaY); } }
  function touchStart(event) { touchY = event.touches[0].clientY; }
  function touchMove(event) { if (!isExiting) { event.preventDefault(); const nextY = event.touches[0].clientY; move((touchY - nextY) * 2.25); touchY = nextY; } }
  function keydown(event) { const keys = { ArrowDown: 220, PageDown: 600, ' ': 420, ArrowUp: -220, PageUp: -600, Home: -TOTAL_SCROLL, End: TOTAL_SCROLL }; if (Object.hasOwn(keys, event.key)) { event.preventDefault(); move(keys[event.key]); } }
  async function exitIntro(event) {
    event?.preventDefault(); if (isExiting) return; isExiting = true; cancelAnimationFrame(frame);
    stage.style.transition = 'opacity .5s cubic-bezier(.4,0,.2,1)'; stage.style.opacity = '0';
    try { await fetch(`${BASE}/api/intro_done.php`, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json' } }); } catch (_) { /* A failed request intentionally keeps the user at the entry gate next time. */ }
    window.setTimeout(() => window.location.assign(`${BASE}/index.php`), 520);
  }
  window.addEventListener('wheel', wheel, { passive: false }); window.addEventListener('touchstart', touchStart, { passive: true });
  window.addEventListener('touchmove', touchMove, { passive: false }); window.addEventListener('keydown', keydown);
  $('#intro-home-btn').addEventListener('click', exitIntro); $('#intro-logo').addEventListener('click', exitIntro);
  document.querySelectorAll('.intro-exit').forEach((button) => button.addEventListener('click', exitIntro));
  document.body.classList.add('intro-active'); render(); frame = requestAnimationFrame(tick);
})();
