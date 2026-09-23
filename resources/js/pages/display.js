/**
 * وضع العرض /display — مصمم للعمل ساعات طويلة دون تدخل:
 * - يجلب المشاركات المعتمدة ويحدّثها دوريًا (تظهر الجديدة أولًا)
 * - يعرض فاصلًا بسحابة الكلمات كل عدة بطاقات
 * - يُعيد تحميل الصفحة كل 6 ساعات لتفادي تراكم الذاكرة
 * - يُبنى كل المحتوى بـ textContent (آمن من XSS)
 */
import { fingerprintField } from '../lib/field.js';

const INTRO_MS = 6000;
const INTERLUDE_EVERY = 10;
const RELOAD_MS = 6 * 60 * 60 * 1000;
const STORE_KEY = 'basmah.display.interval';

export default function () {
  const body = document.body;
  const stage = document.querySelector('[data-stage]');
  const intro = document.querySelector('[data-intro]');
  const topbar = document.querySelector('[data-topbar]');
  const waiting = document.querySelector('[data-waiting]');
  const liveCount = document.querySelector('[data-count-live]');
  const playBtn = document.querySelector('[data-toggle-play]');

  const intervals = body.dataset.intervals.split(',').map(Number);
  let interval = Number(body.dataset.interval);
  try {
    const saved = Number(localStorage.getItem(STORE_KEY));
    if (intervals.includes(saved) && !new URLSearchParams(location.search).has('interval')) interval = saved;
  } catch { /* التخزين غير متاح */ }

  let items = [];
  let queue = [];           // مشاركات جديدة وصلت أثناء العرض
  const seen = new Set();
  let index = -1;
  let shown = 0;
  let timer = 0;
  let playing = true;
  let current = null;
  let started = false;

  fingerprintField(document.querySelector('[data-field]'), { density: 1.3, gather: 8 });
  backgroundWords(document.querySelector('[data-bg-words]'), body.dataset.words.split('،'));

  // ── البيانات ─────────────────────────────
  async function load(initial = false) {
    try {
      const res = await fetch(body.dataset.feed, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      if (!res.ok) throw new Error(res.status);
      const data = await res.json();
      liveCount.textContent = new Intl.NumberFormat('en-US').format(data.count);

      const fresh = data.items.filter((i) => !seen.has(i.id));
      fresh.forEach((i) => seen.add(i.id));
      const ids = new Set(data.items.map((i) => i.id));

      // احذف ما أُخفي من الإدارة، وأضف الجديد
      items = data.items.slice().reverse();
      queue = queue.filter((q) => ids.has(q.id));
      if (!initial) fresh.forEach((i) => queue.push({ ...i, recent: true }));

      waiting.hidden = items.length > 0;
      if (!started && items.length) begin();
    } catch {
      /* الشبكة متقطعة: نكمل بما لدينا ونحاول لاحقًا */
    }
  }

  // ── التسلسل ──────────────────────────────
  function begin() {
    started = true;
    setTimeout(() => {
      intro.classList.remove('is-active');
      topbar.classList.add('is-visible');
      next();
    }, INTRO_MS);
  }

  function schedule() {
    clearTimeout(timer);
    if (playing) timer = setTimeout(next, interval * 1000);
  }

  function next(step = 1) {
    if (!items.length) return;
    shown++;
    if (step > 0 && shown % (INTERLUDE_EVERY + 1) === 0 && items.length >= 5) {
      swap(buildInterlude());
    } else {
      let item;
      if (step > 0 && queue.length) {
        item = queue.shift();
      } else {
        index = (index + step + items.length) % items.length;
        item = items[index];
      }
      swap(buildCard(item));
    }
    schedule();
  }

  function swap(el) {
    const old = current;
    stage.append(el);
    requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('is-in')));
    if (old) {
      old.classList.remove('is-in');
      old.classList.add('is-out');
      setTimeout(() => old.remove(), 1200);
    }
    current = el;
  }

  // ── البناء ───────────────────────────────
  const make = (tag, cls, text) => {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (text != null) el.textContent = text;
    return el;
  };

  function buildCard(p) {
    const card = make('article', 'd-card');
    card.append(make('p', 'd-card__kicker', p.recent ? 'بصمة جديدة' : 'بصمة منسوب'));

    const person = make('div', 'd-card__person');
    const avatar = make('span', 'd-card__avatar', p.initials);
    const who = make('div');
    who.append(make('h2', 'd-card__name', p.name), make('p', 'd-card__dept', p.department));
    person.append(avatar, who);
    card.append(person);

    card.append(make('p', 'd-card__word', p.word));
    card.append(make('blockquote', 'd-card__msg', `«${p.message}»`));
    card.append(make('p', 'd-card__sign', 'بصمتنا.. في وطننا'));
    return card;
  }

  function buildInterlude() {
    const counts = {};
    items.forEach((i) => { counts[i.word] = (counts[i.word] || 0) + 1; });
    const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]).slice(0, 9);
    const max = sorted[0][1];
    const min = sorted[sorted.length - 1][1];
    const arranged = [];
    sorted.forEach((w, i) => (i % 2 ? arranged.unshift(w) : arranged.push(w)));

    const card = make('section', 'd-card d-interlude');
    card.append(make('h2', null, 'ماذا قالت بصمتنا؟'));
    const cloud = make('div', 'cloud');
    arranged.forEach(([word, c]) => {
      const weight = max === min ? 3 : 1 + Math.round((4 * (c - min)) / (max - min));
      cloud.append(make('span', `w${weight}`, word));
    });
    card.append(cloud);
    card.append(make('p', 'd-card__sign', 'بصمتنا.. في وطننا'));
    return card;
  }

  // ── التحكم ───────────────────────────────
  document.querySelectorAll('[data-set-interval]').forEach((b) => {
    b.addEventListener('click', () => setInterval_(Number(b.dataset.setInterval)));
  });
  function setInterval_(s) {
    interval = s;
    try { localStorage.setItem(STORE_KEY, String(s)); } catch { /* */ }
    document.querySelectorAll('[data-set-interval]').forEach((b) => {
      const on = Number(b.dataset.setInterval) === s;
      b.classList.toggle('btn-primary', on);
      b.classList.toggle('btn-outline', !on);
      b.setAttribute('aria-pressed', String(on));
    });
    if (started) schedule();
  }
  setInterval_(interval);

  function togglePlay() {
    playing = !playing;
    playBtn.textContent = playing ? 'إيقاف مؤقت' : 'تشغيل';
    playing ? schedule() : clearTimeout(timer);
  }
  playBtn.addEventListener('click', togglePlay);
  document.querySelector('[data-next]').addEventListener('click', () => next(1));
  document.querySelector('[data-prev]').addEventListener('click', () => next(-1));

  const fullscreen = () => {
    if (document.fullscreenElement) document.exitFullscreen();
    else document.documentElement.requestFullscreen?.().catch(() => {});
  };
  document.querySelector('[data-fullscreen]').addEventListener('click', fullscreen);

  document.addEventListener('keydown', (e) => {
    if (e.key === ' ') { e.preventDefault(); togglePlay(); }
    else if (e.key === 'ArrowLeft') next(1);   // RTL: اليسار = التالي
    else if (e.key === 'ArrowRight') next(-1);
    else if (e.key.toLowerCase() === 'f') fullscreen();
  });

  // إظهار أدوات التحكم والمؤشر عند تحريك الفأرة فقط
  let idle = 0;
  const wake = () => {
    body.classList.add('show-cursor');
    clearTimeout(idle);
    idle = setTimeout(() => body.classList.remove('show-cursor'), 3000);
  };
  ['mousemove', 'touchstart', 'keydown'].forEach((ev) => document.addEventListener(ev, wake, { passive: true }));

  // منع الشاشة من السكون
  let lock = null;
  const requestLock = async () => { try { lock = await navigator.wakeLock?.request('screen'); } catch { /* */ } };
  requestLock();
  document.addEventListener('visibilitychange', () => { if (!document.hidden) requestLock(); }); // يُحرَّر القفل تلقائيًا عند إخفاء الصفحة

  load(true);
  setInterval(load, Number(body.dataset.poll || 60) * 1000);
  setTimeout(() => location.reload(), RELOAD_MS);
}

/** كلمات وطنية خافتة تنجرف ببطء شديد في الخلفية */
function backgroundWords(root, words) {
  if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const spans = words.slice(0, 8).map((w, i) => {
    const s = document.createElement('span');
    s.textContent = w;
    s.style.fontSize = `${3 + (i % 3) * 2}vw`;
    root.append(s);
    return s;
  });
  const place = () => spans.forEach((s, i) => {
    s.style.top = `${8 + ((i * 37 + Math.random() * 20) % 80)}%`;
    s.style.left = `${(i * 23 + Math.random() * 30) % 85}%`;
    s.style.transform = `translateY(${Math.random() * 40 - 20}px)`;
  });
  place();
  setInterval(place, 30000);
}
