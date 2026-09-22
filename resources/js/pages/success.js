import { reducedMotion } from '../lib/ui.js';

export default function () {
  const stage = document.querySelector('[data-join]');
  const mark = document.querySelector('[data-join-mark]');
  const target = document.querySelector('[data-target]');
  const after = document.querySelector('[data-after-join]');

  const finish = () => {
    target?.classList.add('filled');
    stage?.classList.add('is-done');
    after?.classList.add('is-in');
  };

  if (!stage || !mark || !target || reducedMotion()) {
    if (mark) mark.hidden = true;
    finish();
  } else {
    mark.classList.add('mark-draw');
    setTimeout(() => {
      const s = stage.getBoundingClientRect();
      const t = target.getBoundingClientRect();
      mark.style.setProperty('--tx', `${t.left + t.width / 2 - (s.left + s.width / 2)}px`);
      mark.style.setProperty('--ty', `${t.top + t.height / 2 - (s.top + s.height / 2)}px`);
      mark.classList.add('is-flying');
    }, 1500);
    setTimeout(finish, 2500);
  }

  // مشاركة البطاقة
  const btn = document.querySelector('[data-share]');
  const status = document.querySelector('[data-share-status]');
  btn?.addEventListener('click', async () => {
    const data = { title: btn.dataset.shareTitle, text: btn.dataset.shareText, url: btn.dataset.shareUrl };
    try {
      if (navigator.share) {
        await navigator.share(data);
      } else {
        await navigator.clipboard.writeText(`${data.text}\n${data.url}`);
        status.textContent = 'نُسخ نص المشاركة والرابط.';
      }
    } catch { /* ألغى المستخدم المشاركة */ }
  });
}
