const MAX_BYTES = 3 * 1024 * 1024;
const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

export default function () {
  const form = document.querySelector('[data-participate-form]');
  if (!form) return;

  const summary = document.querySelector('[data-error-summary]');
  if (summary) summary.focus();

  // «أخرى» في القسم
  const dept = form.querySelector('#department');
  const deptWrap = form.querySelector('#department_other_wrap');
  const deptOther = form.querySelector('#department_other');
  const syncDept = () => {
    const on = dept.value === dept.dataset.otherValue;
    deptWrap.hidden = !on;
    deptOther.required = on;
    if (on) deptOther.focus();
  };
  dept.addEventListener('change', syncDept);

  // «أخرى» في الكلمات
  const otherWrap = form.querySelector('#other_word_wrap');
  const otherWord = form.querySelector('#other_word');
  form.querySelectorAll('input[name="selected_word"]').forEach((r) => {
    r.addEventListener('change', () => {
      const on = r.value === 'أخرى' && r.checked;
      otherWrap.hidden = !on;
      otherWord.required = on;
      if (on) otherWord.focus();
      setError('selected_word', '');
    });
  });

  // عداد الأحرف
  const msg = form.querySelector('#message');
  const counter = form.querySelector('[data-counter]');
  const max = Number(msg.getAttribute('maxlength'));
  const count = () => {
    const n = [...msg.value].length;
    counter.textContent = `${n} / ${max}`;
    counter.classList.toggle('is-near', n >= max * 0.85 && n <= max);
    counter.classList.toggle('is-over', n > max);
  };
  msg.addEventListener('input', count);
  count();

  // الصورة: تحقق + معاينة
  const photo = form.querySelector('#photo');
  const preview = form.querySelector('[data-photo-preview]');
  const placeholder = preview.innerHTML;
  const clear = form.querySelector('[data-photo-clear]');
  const photoConsent = form.querySelector('[data-photo-consent]');
  let objectUrl = null;

  const resetPhoto = () => {
    photo.value = '';
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    preview.innerHTML = placeholder;
    clear.hidden = true;
    photoConsent.hidden = true;
    photoConsent.querySelector('input').checked = false;
  };

  photo.addEventListener('change', () => {
    setError('photo', '');
    const file = photo.files[0];
    if (!file) return resetPhoto();
    if (!TYPES.includes(file.type)) { resetPhoto(); return setError('photo', 'الصيغ المقبولة: JPG أو PNG أو WEBP.'); }
    if (file.size > MAX_BYTES) { resetPhoto(); return setError('photo', 'حجم الصورة أكبر من 3 ميجابايت.'); }

    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(file);
    const img = new Image();
    img.alt = 'معاينة الصورة';
    img.onerror = () => { resetPhoto(); setError('photo', 'تعذّرت قراءة الصورة. جرّب صورة أخرى.'); };
    img.src = objectUrl;
    preview.replaceChildren(img);
    clear.hidden = false;
    photoConsent.hidden = false;
  });
  clear.addEventListener('click', resetPhoto);

  // التحقق قبل الإرسال
  function setError(name, text) {
    const el = form.querySelector(`[data-error-for="${name}"]`);
    if (el) el.textContent = text;
    const input = form.querySelector(`[name="${name}"]`);
    if (input && input.type !== 'radio' && input.type !== 'checkbox') {
      input.toggleAttribute('aria-invalid', Boolean(text));
      input.classList.toggle('is-invalid', Boolean(text));
    }
  }

  function validate() {
    const errors = {};
    const name = form.full_name.value.trim();
    if (name.length < 3) errors.full_name = name ? 'الاسم قصير جدًا.' : 'اكتب اسمك الكامل.';
    if (!form.querySelector('input[name="audience_type"]:checked')) errors.audience_type = 'اختر منسوب أو طالب/ة.';
    if (!dept.value) errors.department = 'اختر القسم أو الوحدة.';
    else if (!deptWrap.hidden && deptOther.value.trim().length < 2) errors.department = 'اكتب اسم القسم أو الوحدة.';
    const word = form.querySelector('input[name="selected_word"]:checked');
    if (!word) errors.selected_word = 'اختر كلمة تعبّر عن وطنك.';
    else if (word.value === 'أخرى' && otherWord.value.trim().length < 2) errors.selected_word = 'اكتب كلمتك.';
    const m = msg.value.trim();
    if (!m) errors.message = 'اكتب عبارتك.';
    else if ([...m].length < 5) errors.message = 'العبارة قصيرة جدًا.';
    else if ([...m].length > max) errors.message = `العبارة أطول من ${max} حرفًا.`;
    if (!form.consent.checked) errors.consent = 'يلزم الموافقة على عرض مشاركتك قبل الإرسال.';
    return errors;
  }

  ['full_name', 'message'].forEach((n) => form[n].addEventListener('input', () => setError(n, '')));
  form.querySelectorAll('input[name="audience_type"]').forEach((r) => r.addEventListener('change', () => setError('audience_type', '')));
  dept.addEventListener('change', () => setError('department', ''));
  form.consent.addEventListener('change', () => setError('consent', ''));

  const submit = form.querySelector('[data-submit]');
  const label = form.querySelector('[data-submit-label]');

  form.addEventListener('submit', (e) => {
    if (submit.disabled) { e.preventDefault(); return; }
    const errors = validate();
    ['full_name', 'audience_type', 'department', 'selected_word', 'message', 'consent'].forEach((n) => setError(n, errors[n] || ''));
    const first = Object.keys(errors)[0];
    if (first) {
      e.preventDefault();
      const target = ['selected_word', 'audience_type'].includes(first) ? form.querySelector(`input[name="${first}"]`) : form.querySelector(`[name="${first}"]`);
      target?.focus();
      return;
    }
    submit.disabled = true;
    submit.classList.add('is-loading');
    submit.setAttribute('aria-busy', 'true');
    label.textContent = 'جارٍ إرسال بصمتك...';
  });

  // عند الرجوع بزر المتصفح بعد الإرسال
  window.addEventListener('pageshow', () => {
    submit.disabled = false;
    submit.classList.remove('is-loading');
    submit.removeAttribute('aria-busy');
    label.textContent = 'أرسل بصمتك 🇸🇦';
  });
}
