<header class="site-header" data-header>
  <div class="container-x site-header__row relative">
    <a href="{{ route('home') }}" class="logo-safe" aria-label="الرئيسية">
      <x-logo variant="header" eager />
      <span class="logo-caption">كلية إدارة الأعمال بينبع</span>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
      <span class="sr-only">القائمة</span>
      <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    <nav id="site-nav" class="site-nav" aria-label="التنقل الرئيسي" data-nav>
      <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>الرئيسية</a>
      <a href="{{ route('explore') }}" @if(request()->routeIs('explore')) aria-current="page" @endif>بصمات المنسوبين والطلبة</a>
      @unless(request()->routeIs('participate*'))
        <a href="{{ route('participate') }}" class="btn btn-primary btn-sm">أضف بصمتك</a>
      @endunless
    </nav>
  </div>
</header>
