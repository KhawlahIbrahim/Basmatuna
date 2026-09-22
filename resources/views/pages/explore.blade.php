@extends('layouts.app')
@section('title', 'بصمات منسوبي الكلية والطلبة')
@section('page', 'explore')

@section('content')
<section class="section pt-10 pb-12" aria-labelledby="explore-title">
  <div class="container-x">
    <h1 id="explore-title" class="h-section">بصمات منسوبي الكلية والطلبة 🇸🇦</h1>
    <div class="count-hero">
      <span class="count-hero__num" data-count="{{ $stats['participants'] }}">{{ number_format($stats['participants']) }}</span>
      <span class="count-hero__label">{{ $stats['participants'] === 1 ? 'شخص ترك بصمته' : 'شخصًا تركوا بصمتهم' }}</span>
    </div>
  </div>
</section>

@if($stats['participants'] === 0)
  <section class="container-x pb-24">
    <div class="panel p-10 text-center">
      <x-mark :size="64" class="mx-auto mb-4" />
      <p class="text-xl font-semibold mb-2">اللوحة بانتظار أولى البصمات</p>
      <p class="text-muted mb-6">كن أول من يترك كلمته فيها.</p>
      <a href="{{ route('participate') }}" class="btn btn-primary">أضف بصمتك 🇸🇦</a>
    </div>
  </section>
@else

<section class="bg-surface border-y border-line" aria-labelledby="cloud-title">
  <div class="container-x section">
    <h2 id="cloud-title" class="h-sub text-center">ماذا قالت بصمتنا؟</h2>
    <p class="text-center text-muted">اضغط على كلمة لعرض بصماتها.</p>
    <nav class="cloud" aria-label="الكلمات المختارة">
      @foreach($cloud as $c)
        <a href="{{ route('explore', array_filter(['word' => $active['word'] === $c['word'] ? null : $c['word'], 'department' => $active['department'], 'q' => $active['q']])) }}#results"
           class="w{{ $c['weight'] }}" @if($active['word'] === $c['word']) aria-current="true" @endif
           aria-label="{{ $c['word'] }}: {{ $c['count'] }} {{ $c['count'] === 1 ? 'مشاركة' : 'مشاركات' }}">{{ $c['word'] }}</a>
      @endforeach
    </nav>
  </div>
</section>

<section class="section" aria-labelledby="numbers-title">
  <div class="container-x">
    <h2 id="numbers-title" class="h-sub">بصمتنا بالأرقام</h2>
    <div class="stats-row mt-6">
      <div class="stat"><div class="stat__value" data-count="{{ $stats['participants'] }}">{{ $stats['participants'] }}</div><div class="stat__label">مشارك</div></div>
      <div class="stat"><div class="stat__value" data-count="{{ $stats['department_count'] }}">{{ $stats['department_count'] }}</div><div class="stat__label">قسم ووحدة</div></div>
      <div class="stat"><div class="stat__value" data-count="{{ $stats['word_count'] }}">{{ $stats['word_count'] }}</div><div class="stat__label">كلمة مختلفة</div></div>
      <div class="stat stat--word"><div class="stat__value">{{ $stats['top_word'] }}</div><div class="stat__label">الكلمة الأكثر اختيارًا</div></div>
    </div>

    @php($maxWord = max($stats['words']))
    <div class="bars mt-10 max-w-2xl" role="list" aria-label="توزيع الكلمات">
      @foreach(array_slice($stats['words'], 0, 10, true) as $word => $count)
        <div class="bar" role="listitem">
          <span class="truncate">{{ $word }}</span>
          <span class="bar__track"><span class="bar__fill" data-bar="{{ round($count / $maxWord * 100) }}"></span></span>
          <span class="bar__num">{{ $count }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section id="results" class="pb-24 scroll-mt-24" aria-labelledby="results-title">
  <div class="container-x">
    <h2 id="results-title" class="sr-only">المشاركات</h2>
    <form method="GET" action="{{ route('explore') }}#results" class="filters panel p-4 sm:p-5" role="search" data-filters>
      <div class="field">
        <label class="label text-sm" for="q">ابحث بالاسم</label>
        <input class="input" type="search" id="q" name="q" value="{{ $active['q'] }}" maxlength="60" placeholder="اسم المشارك">
      </div>
      <div class="field !mt-0">
        <label class="label text-sm" for="f-dept">القسم</label>
        <select class="select" id="f-dept" name="department" data-autosubmit>
          <option value="">كل الأقسام</option>
          @foreach($stats['departments'] as $d)
            <option value="{{ $d }}" @selected($active['department'] === $d)>{{ $d }}</option>
          @endforeach
        </select>
      </div>
      <input type="hidden" name="word" value="{{ $active['word'] }}">
      <button class="btn btn-primary" type="submit">بحث</button>
    </form>

    <nav class="flex flex-wrap gap-2 mt-5" aria-label="تصفية حسب الكلمة">
      <a class="chip chip--link" href="{{ route('explore', array_filter(['department' => $active['department'], 'q' => $active['q']])) }}#results" @if(!$active['word']) aria-current="true" @endif><span>الكل</span></a>
      @foreach(array_keys($stats['words']) as $w)
        <a class="chip chip--link" href="{{ route('explore', array_filter(['word' => $w, 'department' => $active['department'], 'q' => $active['q']])) }}#results" @if($active['word'] === $w) aria-current="true" @endif><span>{{ $w }}</span></a>
      @endforeach
    </nav>

    <p class="text-muted text-sm mt-6" aria-live="polite">
      @if($filtered)
        {{ $items->total() }} نتيجة.
        <a class="text-primary-strong underline underline-offset-4" href="{{ route('explore') }}#results">إزالة التصفية</a>
      @endif
    </p>

    @if($items->isEmpty())
      <div class="panel p-10 text-center mt-4">
        <p class="font-semibold mb-1">لا توجد بصمات تطابق البحث.</p>
        <p class="text-muted">جرّب اسمًا آخر أو أزل التصفية.</p>
      </div>
    @else
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mt-4">
        @foreach($items as $p)
          <x-participation-card :p="$p" />
        @endforeach
      </div>
      {{ $items->fragment('results')->links() }}
    @endif
  </div>
</section>
@endif
@endsection
