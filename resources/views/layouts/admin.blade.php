<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'لوحة التحكم') | {{ config('basmah.title') }}</title>
  <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="admin">
  <a href="#main" class="skip-link">تخطَّ إلى المحتوى</a>
  <header class="bg-surface border-b border-line">
    <div class="container-x flex flex-wrap items-center justify-between gap-3 py-3">
      <div class="flex items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="logo-safe"><x-logo variant="header" eager /></a>
        <span class="hidden sm:inline text-muted text-sm border-s border-line ps-4">لوحة التحكم</span>
      </div>
      <nav class="flex flex-wrap items-center gap-1 site-nav !static !flex-row !shadow-none !border-0 !p-0 !bg-transparent" aria-label="لوحة التحكم">
        <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>الرئيسية</a>
        <a href="{{ route('admin.participations.index') }}" @if(request()->routeIs('admin.participations.*')) aria-current="page" @endif>المشاركات</a>
        <a href="{{ route('display') }}" target="_blank" rel="noopener">شاشة العرض</a>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-ghost btn-sm">خروج</button></form>
      </nav>
    </div>
  </header>
  <main id="main" class="container-x py-8" tabindex="-1">
    @if(session('status'))
      <div class="alert alert-success mb-6" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger mb-6" role="alert">{{ $errors->first() }}</div>
    @endif
    @yield('content')
  </main>
</body>
</html>
