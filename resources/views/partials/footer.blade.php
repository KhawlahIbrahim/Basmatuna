<footer class="site-footer">
  <div class="container-x py-12 grid gap-8 md:grid-cols-[auto_1fr_auto] md:items-center">
    <div class="logo-safe"><x-logo variant="footer" /></div>
    <div class="md:ps-8 md:border-s md:border-line">
      <p class="text-xl font-bold">{{ config('basmah.title') }} 🇸🇦</p>
      <p class="text-muted">{{ config('basmah.occasion') }}، {{ config('basmah.college') }}</p>
    </div>
    <p class="text-sm text-muted leading-relaxed">مقدمة من<br><strong class="text-ink text-base font-semibold">{{ config('basmah.presenter') }}</strong></p>
  </div>
</footer>
