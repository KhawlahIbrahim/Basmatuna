<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }} | {{ config('basmah.title') }}</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen grid place-items-center p-6 text-center">
  <main>
    <div class="flex justify-center mb-8"><x-logo variant="header" eager /></div>
    <x-mark :size="56" class="mx-auto mb-4" />
    <h1 class="text-3xl font-bold mb-2">{{ $title }}</h1>
    <p class="text-muted mb-8">{{ $text }}</p>
    <a href="{{ url('/') }}" class="btn btn-primary">العودة إلى الرئيسية</a>
  </main>
</body>
</html>
