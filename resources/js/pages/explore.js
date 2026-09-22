import { countUp } from '../lib/ui.js';

export default function () {
  document.querySelectorAll('[data-count]').forEach((el) => countUp(el, Number(el.dataset.count)));

  document.querySelectorAll('[data-autosubmit]').forEach((el) => {
    el.addEventListener('change', () => el.form.requestSubmit());
  });

  const cards = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)) return cards.forEach((c) => c.classList.add('is-in'));
  let i = 0;
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      const el = e.target;
      setTimeout(() => el.classList.add('is-in'), (i++ % 6) * 70);
      io.unobserve(el);
    });
  }, { rootMargin: '0px 0px -40px' });
  cards.forEach((c) => io.observe(c));
}
