# بصمتنا.. في وطننا 🇸🇦

منصة تفاعلية لمنسوبي كلية إدارة الأعمال بجامعة طيبة بمناسبة اليوم الوطني السعودي.
**مقدمة من أ. أمل محسن الكربي**

Laravel 13 · Blade · MySQL · Tailwind CSS 4 · JavaScript (بدون مكتبات خارجية للرسوم أو السحابة)

---

## 1. المعمارية باختصار

```
الزائر (QR) ─► /participate ─► StoreParticipationRequest (تحقق + تنظيف)
                               └► ImageProcessor (إعادة ترميز الصورة) ─► participations (status = pending)
المسؤول ─► /admin ─► اعتماد / إخفاء / حذف / تصدير CSV
approved ─► /explore (بطاقات + سحابة + أرقام) و /display/feed ─► /display (الشاشات)
```

| الطبقة | الملفات |
|---|---|
| الإعدادات | `config/basmah.php` (الكلمات، الأقسام، النصوص، مدد العرض) |
| البيانات | `database/migrations/*participations*`، `app/Models/Participation.php`، `app/Enums/ParticipationStatus.php` |
| المنطق | `app/Services/StatsService.php` (إحصائيات حيّة مع ذاكرة مؤقتة)، `app/Services/ImageProcessor.php` |
| المتحكمات | `app/Http/Controllers/*` و `Admin/*` |
| الحماية | `EnsureAdmin`، `SecurityHeaders`، محددات المعدل في `AppServiceProvider` |
| الواجهات | `resources/views/{layouts,partials,components,pages,admin,errors}` |
| التصميم | `resources/css/app.css` (Design System)، `resources/css/display.css` |
| التفاعل | `resources/js/app.js` + `resources/js/pages/*` |

### المسارات

| المسار | الوصف |
|---|---|
| `/` | الصفحة الرئيسية |
| `/participate` | نموذج «اترك بصمتك» (رابط QR) |
| `/participate/done` | صفحة «وصلت بصمتك!» |
| `/explore` | البصمات + «ماذا قالت بصمتنا؟» + «بصمتنا بالأرقام» |
| `/display` | وضع الشاشات (`/display?interval=5` أو 8 أو 10) |
| `/admin` | لوحة التحكم (تسجيل دخول) |

---

## 2. تحليل الشعارات المرفقة

| الملف | النوع | ما هو | القرار |
|---|---|---|---|
| `taibah-university.png` | PNG شفاف 3840×887 | **الشعار الرسمي لجامعة طيبة** | الهوية الأساسية: الترويسة، الرئيسية، العرض، التذييل، الدخول |
| (الملف الثالث) | PNG شفاف 1152×2048 | **فارغ تمامًا**: كل البكسلات شفافة | لم يُستخدم، يلزم إعادة إرساله |
| `heritage-tower.png` | صورة JPG/PNG | صورة برج طيني، ليست شعارًا | محفوظ في `references/` فقط |
| `pattern-poster.png` | لقطة شاشة | ملصق بعناصر نخلة وخيل وراية، ليس شعارًا | محفوظ في `references/` فقط |
| `landmarks.png` | رسم | معالم الرياض بزخارف السدو، ليست شعارًا | محفوظ في `references/` فقط |

**ألوان الشعار المستخرجة من البكسلات:**

| اللون | القيمة | موقعه في الشعار | دوره في الموقع |
|---|---|---|---|
| أزرق طيبة | `#4056E3` | الكتابة العربية والإنجليزية وحواف الأضلاع | `--primary-color` |
| سماوي | `#00AEDA` | بداية تدرّج الأضلاع | `--secondary-color` (زخرفي فقط) |
| أخضر مزرق | `#0A8D6F` | نهاية تدرّج الأضلاع | `--accent-color` (جسر نحو الأخضر الوطني) |
| الأخضر الوطني | `#006C35` | — | `--national-green` للعناصر الوطنية فقط |

**الطابع:** هندسي، خطوط رفيعة متتالية تدور نحو المركز، حديث وهادئ. هذا التكرار الدائري هو ما بُنيت عليه «علامة البصمة» في الموقع: خطوط متداخلة بنفس التدرّج (سماوي ← أزرق ← أخضر) تشبه بصمة الإصبع. هي عنصر مستقل مستلهم، **وليست الشعار ولا إعادة رسم له**.

**قواعد الشعار المطبّقة:** الملف الأصلي كما هو في `public/assets/logos/`، مع نسخة مصغّرة (1200px) للأداء بنفس النسبة. لا ظل ولا تدرّج ولا قص ولا `object-fit: cover`، والعرض يُحدد بـ `width` مع `height:auto`. الخلفيات كلها فاتحة (حتى شاشة العرض) لأن الشعار أزرق على خلفية شفافة ولا يجوز إعادة تلوينه للخلفيات الداكنة.

---

## 3. نظام التصميم

المتغيرات في `resources/css/app.css` ومربوطة بـ Tailwind عبر `@theme inline` (مثل `text-primary` و `bg-surface`):

```css
--primary-color:#4056E3; --secondary-color:#00AEDA; --accent-color:#0A8D6F;
--national-green:#006C35; --background-color:#F4F6FD; --surface-color:#FFFFFF;
--text-color:#161C47; --muted-color:#5A628C; --border-color:#DFE3F4;
```

- **الخط:** IBM Plex Sans Arabic مستضاف ذاتيًا (`@fontsource`)، فلا اعتماد على Google Fonts داخل شبكة الجامعة.
- **المكونات:** `.btn-*`، `.input/.select/.textarea`، `.chip`، `.p-card`، `.badge--*`، `.stat`، `.bars`، `.cloud`، `.modal`، `.panel`.
- **الحركة:** لحظة واحدة مميزة لكل صفحة (تجمّع البصمات في الرئيسية، انضمام البصمة في صفحة النجاح، تبديل البطاقات في العرض). جميعها تحترم `prefers-reduced-motion`.

---

## 4. التشغيل محليًا

المتطلبات: PHP 8.3+ مع `gd`, `mbstring`, `pdo_mysql`, `fileinfo` (و`exif` اختياريًا)، Composer، MySQL 8، و Node 20+ (للتطوير فقط).

```bash
composer install
cp .env.example .env
php artisan key:generate
# عدّل DB_* في .env ثم:
php artisan migrate
php artisan basmah:admin your.email@taibahu.edu.sa    # يطلب كلمة مرور (12 حرفًا على الأقل)
php artisan db:seed --class=DemoParticipationSeeder     # اختياري: 30 مشاركة تجريبية
npm install && npm run dev                              # أو npm run build
php artisan serve
```

> ملفات الواجهة مبنية مسبقًا في `public/build`، فلا يلزم Node على الخادم.

### حذف البيانات التجريبية قبل الإطلاق

```bash
php artisan basmah:purge-demo
```

المشاركات التجريبية تحمل `is_demo = 1` وأسماء «مشارك تجريبي N»، وتظهر لها شارة «تجريبية» وتنبيه في لوحة التحكم حتى تُحذف.

---

## 5. النشر

1. ارفع المشروع، واجعل **جذر الموقع (Document Root) هو مجلد `public/`** فقط.
2. في `.env`:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain
   SESSION_SECURE_COOKIE=true
   ```
3. نفّذ:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan basmah:admin your.email@taibahu.edu.sa
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
4. صلاحيات الكتابة: `storage/` و `bootstrap/cache/`.
5. HTTPS إلزامي (الموقع يفرض `https` في الإنتاج).
6. لا يلزم `storage:link`: الصور محفوظة في تخزين خاص وتُقدَّم عبر `/media/{id}` فقط إذا كانت المشاركة معتمدة وصاحبها وافق على عرض صورته.

**بعد انتهاء الفعالية:** اجعل `BASMAH_SUBMISSIONS_OPEN=false` ثم `php artisan config:cache`، فيبقى الاستكشاف والعرض ويتوقف الاستقبال.

### رمز QR

الرابط الموصى به للبوسترات والرسائل: `https://your-domain/participate` (ينسخ من لوحة التحكم). ولشاشات الكلية: `https://your-domain/display` في متصفح بملء الشاشة (زر F). تحريك الفأرة يُظهر أدوات: مدة البطاقة، إيقاف، التالي/السابق.

---

## 6. الأمان والخصوصية

- **CSRF** على كل النماذج، و**تحقق مزدوج** (عميل + خادم) مع رسائل عربية.
- **SQL Injection:** Eloquent مع ربط المعاملات، وتهريب `%` و `_` في البحث.
- **XSS:** كل المخرجات عبر `{{ }}`، و`strip_tags` على المدخلات، وشاشة العرض تبني المحتوى بـ `textContent`، و CSP صارم في الإنتاج (`script-src 'self'`، بلا سكربتات مضمّنة).
- **الصور:** التحقق من الامتداد وMIME والتوقيع الحقيقي والأبعاد والحجم (3MB)، ثم **إعادة ترميز كاملة** عبر GD (تُزال بيانات EXIF والموقع الجغرافي وأي حمولة مخفية)، وقص مربع 600px، واسم عشوائي، وتخزين خارج `public`.
- **تحديد المعدل:** 3 مشاركات لكل جلسة كل 10 دقائق، و40 لكل IP في الدقيقة (حد واسع لأن شبكة الجامعة قد تجمع المنسوبين خلف IP واحد)، و5 محاولات دخول في الدقيقة.
- **مصيدة** للبرامج الآلية (حقل مخفي).
- **لوحة التحكم:** مصادقة + تحقق من `is_admin` (غير قابل للتعبئة الجماعية) + `noindex`.
- **CSV:** حماية من حقن الصيغ في Excel، مع BOM للعربية.
- **الخصوصية:** لا يُحفظ IP ولا بريد ولا رقم وظيفي. تُحفظ لحظة الموافقة (`consented_at`). الصورة تحتاج موافقة منفصلة. لا يظهر شيء للعامة قبل الاعتماد.

---

## 7. ما يحتاج مراجعتك

- **قائمة الأقسام** في `config/basmah.php` افتراضية، وتحتاج مطابقتها مع الهيكل الفعلي للكلية.
- **شعار الكلية/الجهة المنظمة:** الملف المرفق وصل فارغًا. عند وصوله يوضع في `public/assets/logos/` ويُضاف إلى `components/logo.blade.php`.
