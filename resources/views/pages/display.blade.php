<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>شاشة العرض | {{ config('basmah.title') }}</title>
  <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
  @vite(['resources/css/app.css', 'resources/css/display.css', 'resources/js/app.js'])
</head>
<body data-page="display" class="display"
      data-feed="{{ route('display.feed') }}"
      data-interval="{{ $interval }}"
      data-intervals="{{ implode(',', $intervals) }}"
      data-poll="{{ config('basmah.display.poll_seconds') }}"
      data-words="{{ implode('،', config('basmah.words')) }}">

  <canvas class="display__field" data-field aria-hidden="true"></canvas>
  <div class="display__words" aria-hidden="true" data-bg-words></div>

  {{-- المقدمة: الشعار ثم العنوان --}}
  <section class="display__intro is-active" data-intro aria-label="{{ config('basmah.title') }}">
    <x-logo variant="display" eager class="display__intro-logo" />
    <h1 class="display__title">بصمتنا.. في وطننا</h1>
    <p class="display__tagline">{{ config('basmah.tagline') }}</p>
    <p class="display__waiting" data-waiting hidden>اللوحة بانتظار أولى البصمات. شارك الآن عبر الرابط أدناه.</p>
  </section>

  {{-- شريط علوي أثناء عرض البطاقات --}}
  <header class="display__top" data-topbar>
    <x-logo variant="header" class="display__top-logo" />
    <p class="display__count"><b data-count-live>0</b> بصمة</p>
  </header>

  <main class="display__stage" data-stage aria-live="polite" aria-atomic="true"></main>

  <footer class="display__bottom">
    <span>مقدمة من <b>{{ config('basmah.presenter') }}</b></span>
    <span class="display__join">شارك بكلمتك: <b dir="ltr">{{ preg_replace('#^https?://#', '', route('participate')) }}</b></span>
  </footer>

  <div class="display__controls" data-controls role="toolbar" aria-label="إعدادات العرض">
    <span class="text-sm">مدة البطاقة:</span>
    @foreach($intervals as $s)
      <button type="button" class="btn btn-sm {{ $s === $interval ? 'btn-primary' : 'btn-outline' }}" data-set-interval="{{ $s }}" aria-pressed="{{ $s === $interval ? 'true' : 'false' }}">{{ $s }} ث</button>
    @endforeach
    <span class="w-px h-6 bg-line mx-1"></span>
    <button type="button" class="btn btn-sm btn-outline" data-prev aria-label="السابق">‹</button>
    <button type="button" class="btn btn-sm btn-outline" data-toggle-play>إيقاف مؤقت</button>
    <button type="button" class="btn btn-sm btn-outline" data-next aria-label="التالي">›</button>
    <button type="button" class="btn btn-sm btn-outline" data-fullscreen>ملء الشاشة</button>
  </div>
</body>
</html>
