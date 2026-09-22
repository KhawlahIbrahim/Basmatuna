export const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** أشرطة التوزيع: العرض يُضبط من data-bar (بدون style مضمّن من أجل CSP) */
export function initBars() {
  const bars = document.querySelectorAll('[data-bar]');
  if (!bars.length) return;
  const fill = (el) => { el.style.width = `${Math.max(2, Number(el.dataset.bar))}%`; };
  if (!('IntersectionObserver' in window)) return bars.forEach(fill);
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { fill(e.target); io.unobserve(e.target); } });
  }, { threshold: 0.3 });
  bars.forEach((b) => io.observe(b));
}

export function initCopy() {
  document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const src = btn.parentElement.querySelector('[data-copy-source]');
      try {
        await navigator.clipboard.writeText(src.value);
        btn.textContent = 'تم النسخ';
      } catch {
        src.select();
      }
      setTimeout(() => { btn.textContent = 'نسخ'; }, 2000);
    });
  });
}

/** تأكيد قبل الحذف: على النماذج أو الأزرار التي تحمل data-confirm */
export function initConfirm() {
  document.addEventListener('submit', (e) => {
    const form = e.target;
    const msg = e.submitter?.dataset.confirm || form.dataset.confirm;
    if (msg && !window.confirm(msg)) e.preventDefault();
  });
}

/** عدّاد تصاعدي للأرقام */
export function countUp(el, to, duration = 1400) {
  const fmt = new Intl.NumberFormat('en-US');
  if (reducedMotion() || to < 2) { el.textContent = fmt.format(to); return; }
  const start = performance.now();
  const step = (now) => {
    const p = Math.min(1, (now - start) / duration);
    el.textContent = fmt.format(Math.round(to * (1 - Math.pow(1 - p, 3))));
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}
