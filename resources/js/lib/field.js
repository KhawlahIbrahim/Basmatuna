/**
 * حقل «البصمات الرقمية»: نقاط تتجمع ببطء لتشكّل بصمة من خطوط متداخلة،
 * بنفس هندسة علامة البصمة المستلهمة من أضلاع شعار الجامعة.
 */
import { reducedMotion } from './ui.js';

const RINGS = 9;

export function fingerprintField(canvas, { density = 1, gather = 6, breathe = true } = {}) {
  const ctx = canvas.getContext('2d');
  const css = getComputedStyle(document.documentElement);
  const palette = ['--secondary-color', '--primary-color', '--accent-color'].map((v) => css.getPropertyValue(v).trim());
  const still = reducedMotion();

  let W = 0, H = 0, particles = [], raf = 0, visible = true, t0 = performance.now();

  function build() {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const r = canvas.getBoundingClientRect();
    W = r.width; H = r.height;
    if (!W || !H) return;
    canvas.width = Math.round(W * dpr);
    canvas.height = Math.round(H * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const s = Math.min(W / 124, H / 150);
    const cx = W / 2, cy = H / 2 + s * 2;
    particles = [];
    for (let i = 0; i < RINGS; i++) {
      const rx = (7 + i * 6.2) * s, ry = (9 + i * 7.4) * s;
      const a0 = 112 + (i % 3) * 14, a1 = 428 - (i % 2) * 22;
      const n = Math.round((8 + i * 6.5) * density);
      for (let k = 0; k < n; k++) {
        const a = ((a0 + ((a1 - a0) * k) / (n - 1)) * Math.PI) / 180;
        const tx = cx + rx * Math.cos(a), ty = cy + ry * Math.sin(a);
        const g = (tx / W + ty / H) / 2; // تدرّج قطري كتدرّج الشعار
        particles.push({
          tx, ty,
          sx: Math.random() * W, sy: Math.random() * H,
          r: 1.1 + Math.random() * 1.6 + i * 0.05,
          c: palette[g < 0.42 ? 0 : g < 0.68 ? 1 : 2],
          ph: Math.random() * Math.PI * 2,
          d: Math.random() * 1.8,
        });
      }
    }
  }

  const ease = (x) => 1 - Math.pow(1 - x, 3);

  function draw(now) {
    const t = (now - t0) / 1000;
    ctx.clearRect(0, 0, W, H);
    for (const p of particles) {
      const prog = still ? 1 : ease(Math.min(1, Math.max(0, (t - p.d) / gather)));
      const wob = still || !breathe ? 0 : Math.sin(t * 0.5 + p.ph) * 1.4 * prog;
      const x = p.sx + (p.tx - p.sx) * prog + wob;
      const y = p.sy + (p.ty - p.sy) * prog + Math.cos(t * 0.4 + p.ph) * wob;
      ctx.globalAlpha = 0.25 + 0.65 * prog;
      ctx.fillStyle = p.c;
      ctx.beginPath();
      ctx.arc(x, y, p.r, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.globalAlpha = 1;
  }

  function loop(now) {
    draw(now);
    if (!still && visible && !document.hidden) raf = requestAnimationFrame(loop);
  }

  function start() { cancelAnimationFrame(raf); raf = requestAnimationFrame(loop); }

  build();
  start();

  new ResizeObserver(() => { build(); if (still) draw(performance.now()); }).observe(canvas);
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(([e]) => { visible = e.isIntersecting; if (visible) start(); }).observe(canvas);
  }
  document.addEventListener('visibilitychange', () => { if (!document.hidden) start(); });

  return { regather() { t0 = performance.now(); particles.forEach((p) => { p.sx = Math.random() * W; p.sy = Math.random() * H; }); start(); } };
}
