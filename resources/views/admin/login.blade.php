<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>تسجيل الدخول | {{ config('basmah.title') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="login" class="min-h-screen grid place-items-center p-4">
  <main class="w-full max-w-[420px]">
    <div class="flex justify-center mb-8 logo-safe"><x-logo variant="hero" eager /></div>
    <form method="POST" action="{{ route('admin.login.store') }}" class="panel p-7 sm:p-8" novalidate>
      @csrf
      <h1 class="text-2xl font-bold mb-1">لوحة التحكم</h1>
      <p class="text-muted text-sm mb-6">{{ config('basmah.title') }}</p>

      @error('email')<div class="alert alert-danger mb-5" role="alert">{{ $message }}</div>@enderror

      <div class="field">
        <label class="label" for="email">البريد الإلكتروني</label>
        <input class="input" id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required autocomplete="username" autofocus>
      </div>
      <div class="field">
        <label class="label" for="password">كلمة المرور</label>
        <input class="input" id="password" name="password" type="password" dir="ltr" required autocomplete="current-password">
        @error('password')<p class="error">{{ $message }}</p>@enderror
      </div>
      <label class="check mt-5"><input type="checkbox" name="remember" value="1"><span class="text-sm">تذكّرني على هذا الجهاز</span></label>
      <button class="btn btn-primary w-full mt-6" type="submit">تسجيل الدخول</button>
    </form>
    <p class="text-center mt-6"><a class="text-sm text-muted underline underline-offset-4" href="{{ route('home') }}">العودة إلى الموقع</a></p>
  </main>
</body>
</html>
