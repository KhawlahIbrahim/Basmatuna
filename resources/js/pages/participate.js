const MAX_SIDE = 1200;
const JPEG_QUALITY = 0.85;

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

  // الصورة: تجهيز (تصغير + تحويل JPEG) في المتصفح، تُحفظ كـ Blob لإرسالها لاحقًا
  const photo = form.querySelector('#photo');
  const preview = form.querySelector('[data-photo-preview]');
  const placeholder = preview.innerHTML;
  const clear = form.querySelector('[data-photo-clear]');
  const photoConsent = form.querySelector('[data-photo-consent]');
  const processing = form.querySelector('[data-photo-processing]');
  let objectUrl = null;
  let compressedPhoto = null;

  const resetPhoto = () => {
    photo.value = '';
    compressedPhoto = null;
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    preview.innerHTML = placeholder;
    clear.hidden = true;
    photoConsent.hidden = true;
    photoConsent.querySelector('input').checked = false;
  };

  async function decodeImage(file) {
    if ('createImageBitmap' in window) {
      try {
        return await createImageBitmap(file, { imageOrientation: 'from-image' });
      } catch {
        // نكمل للخيار البديل
      }
    }
    return await new Promise((resolve, reject) => {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
      img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode-failed')); };
      img.src = url;
    });
  }

  function setPhotoProcessing(on) {
    processing.hidden = !on;
    submit.disabled = on;
  }

  photo.addEventListener('change', async () => {
    setError('photo', '');
    const file = photo.files[0];
    if (!file) return resetPhoto();

    setPhotoProcessing(true);
    try {
      const source = await decodeImage(file);
      const srcW = source.width || source.naturalWidth;
      const srcH = source.height || source.naturalHeight;
      const scale = Math.min(1, MAX_SIDE / Math.max(srcW, srcH));
      const w = Math.max(1, Math.round(srcW * scale));
      const h = Math.max(1, Math.round(srcH * scale));

      const canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      canvas.getContext('2d').drawImage(source, 0, 0, w, h);
      if (source.close) source.close();

      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY));
      if (!blob) throw new Error('encode-failed');

      compressedPhoto = blob;

      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = URL.createObjectURL(blob);
      const img = new Image();
      img.alt = 'معاينة الصورة';
      img.src = objectUrl;
      preview.replaceChildren(img);
      clear.hidden = false;
      photoConsent.hidden = false;
    } catch {
      resetPhoto();
      setError('photo', 'تعذّر فتح هذه الصورة. جرّب صورة أخرى أو التقط صورة جديدة.');
    } finally {
      setPhotoProcessing(false);
    }
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

  function buildErrorSummary(messages) {
    let box = document.querySelector('[data-error-summary]');
    if (!box) {
      box = document.createElement('div');
      box.className = 'alert alert-danger mb-6';
      box.setAttribute('role', 'alert');
      box.setAttribute('tabindex', '-1');
      box.setAttribute('data-error-summary', '');
      const title = document.createElement('p');
      title.className = 'font-semibold mb-1';
      title.textContent = 'راجع الحقول التالية:';
      const list = document.createElement('ul');
      list.className = 'list-disc ps-5';
      box.append(title, list);
      form.parentNode.insertBefore(box, form);
    }
    const list = box.querySelector('ul');
    list.replaceChildren(...messages.map((m) => {
      const li = document.createElement('li');
      li.textContent = m;
      return li;
    }));
    box.focus();
  }

  function removeErrorSummary() {
    document.querySelector('[data-error-summary]')?.remove();
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

  function startSubmitting() {
    submit.disabled = true;
    submit.classList.add('is-loading');
    submit.setAttribute('aria-busy', 'true');
    label.textContent = 'جارٍ إرسال بصمتك...';
  }

  function stopSubmitting() {
    submit.disabled = false;
    submit.classList.remove('is-loading');
    submit.removeAttribute('aria-busy');
    label.textContent = 'أرسل بصمتك';
  }

  const FIELD_NAMES = ['full_name', 'audience_type', 'department', 'selected_word', 'message', 'consent', 'photo'];

  async function submitViaFetch() {
    const formData = new FormData(form);
    formData.delete('photo');
    if (compressedPhoto) formData.append('photo', compressedPhoto, 'photo.jpg');

    startSubmitting();

    let response;
    try {
      response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      });
    } catch {
      stopSubmitting();
      buildErrorSummary(['تعذّر الإرسال. تحقق من الاتصال وحاول مرة أخرى.']);
      return;
    }

    if (response.ok) {
      let data = {};
      try { data = await response.json(); } catch { /* تجاهل */ }
      removeErrorSummary();
      window.location.href = data.redirect || form.action;
      return;
    }

    stopSubmitting();

    if (response.status === 422) {
      let data = {};
      try { data = await response.json(); } catch { /* تجاهل */ }
      const errors = data.errors || {};
      FIELD_NAMES.forEach((n) => setError(n, ''));
      Object.entries(errors).forEach(([field, messages]) => setError(field, messages[0]));
      const allMessages = Object.values(errors).map((messages) => messages[0]);
      if (allMessages.length) buildErrorSummary(allMessages);
      const firstField = Object.keys(errors)[0];
      const target = firstField
        ? (['selected_word', 'audience_type'].includes(firstField) ? form.querySelector(`input[name="${firstField}"]`) : form.querySelector(`[name="${firstField}"]`))
        : null;
      target?.focus();
      return;
    }

    if (response.status === 419) {
      buildErrorSummary(['انتهت صلاحية الصفحة. حدّث الصفحة وأعد المحاولة.']);
      return;
    }

    if (response.status === 429) {
      let data = {};
      try { data = await response.json(); } catch { /* تجاهل */ }
      buildErrorSummary([data.message || 'أرسلت عدة مشاركات خلال وقت قصير. حاول بعد دقائق.']);
      return;
    }

    buildErrorSummary(['تعذّر الإرسال. تحقق من الاتصال وحاول مرة أخرى.']);
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (submit.disabled) return;
    const errors = validate();
    ['full_name', 'audience_type', 'department', 'selected_word', 'message', 'consent'].forEach((n) => setError(n, errors[n] || ''));
    const first = Object.keys(errors)[0];
    if (first) {
      const target = ['selected_word', 'audience_type'].includes(first) ? form.querySelector(`input[name="${first}"]`) : form.querySelector(`[name="${first}"]`);
      target?.focus();
      return;
    }
    submitViaFetch();
  });

  // عند الرجوع بزر المتصفح بعد الإرسال
  window.addEventListener('pageshow', stopSubmitting);
}
