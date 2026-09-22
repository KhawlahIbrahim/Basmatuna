export default function () {
  const all = document.querySelector('[data-select-all]');
  const rows = [...document.querySelectorAll('[data-row-check]')];
  const count = document.querySelector('[data-bulk-count]');
  const btns = document.querySelectorAll('[data-bulk-btn]');
  if (!all) return;

  const sync = () => {
    const n = rows.filter((r) => r.checked).length;
    count.textContent = n;
    btns.forEach((b) => { b.disabled = n === 0; });
    all.checked = n > 0 && n === rows.length;
    all.indeterminate = n > 0 && n < rows.length;
  };
  all.addEventListener('change', () => { rows.forEach((r) => { r.checked = all.checked; }); sync(); });
  rows.forEach((r) => r.addEventListener('change', sync));
  sync();
}
