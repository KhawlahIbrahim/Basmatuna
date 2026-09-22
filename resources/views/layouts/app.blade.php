<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@hasSection('title')@yield('title') | @endif{{ config('basmah.title') }}</title>
  <meta name="description" content="{{ config('basmah.tagline') }} مبادرة {{ config('basmah.college') }} في {{ config('basmah.university') }} بمناسبة {{ config('basmah.occasion') }}.">
  <meta property="og:title" content="{{ config('basmah.title') }}">
  <meta property="og:description" content="{{ config('basmah.tagline') }}">
  <meta property="og:image" content="{{ asset('assets/images/og-image.png') }}">
  <meta property="og:locale" content="ar_SA">
  <meta name="theme-color" content="#4056E3">
  @stack('meta')
  <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="@yield('page', 'default')">
  <a href="#main" class="skip-link">تخطَّ إلى المحتوى</a>
  @include('partials.header')
  <main id="main" tabindex="-1">
    @yield('content')
  </main>
  @include('partials.footer')
</body>
</html>
