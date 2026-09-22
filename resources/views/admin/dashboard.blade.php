@extends('layouts.admin')
@section('title', 'الرئيسية')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <h1 class="text-2xl font-bold">نظرة عامة</h1>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.participations.index', ['status' => 'pending']) }}" class="btn btn-primary btn-sm">مراجعة المعلّقة ({{ $stats['pending'] }})</a>
    <a href="{{ route('admin.participations.export') }}" class="btn btn-outline btn-sm">تصدير CSV</a>
  </div>
</div>

<div class="panel">
  <div class="stats-row !border-0 lg:!grid-cols-6 !grid-cols-2 md:!grid-cols-3">
    <div class="stat"><div class="stat__value">{{ $stats['total'] }}</div><div class="stat__label">إجمالي المشاركات</div></div>
    <div class="stat"><div class="stat__value text-[color:var(--warning-color)]">{{ $stats['pending'] }}</div><div class="stat__label">بانتظار المراجعة</div></div>
    <div class="stat"><div class="stat__value text-national">{{ $stats['approved'] }}</div><div class="stat__label">معتمدة</div></div>
    <div class="stat"><div class="stat__value text-muted">{{ $stats['hidden'] }}</div><div class="stat__label">مخفية</div></div>
    <div class="stat"><div class="stat__value">{{ $stats['departments'] }}</div><div class="stat__label">قسم ووحدة</div></div>
    <div class="stat stat--word"><div class="stat__value text-2xl">{{ $stats['top_words']->first()?->selected_word ?? '—' }}</div><div class="stat__label">أكثر كلمة</div></div>
  </div>
</div>

@if($stats['demo'] > 0)
  <div class="alert alert-info mt-6">
    توجد {{ $stats['demo'] }} مشاركة تجريبية. احذفها قبل الإطلاق بالأمر:
    <code dir="ltr" class="font-mono">php artisan basmah:purge-demo</code>
  </div>
@endif

<div class="grid gap-6 lg:grid-cols-[1.4fr_1fr] mt-6">
  <section class="panel p-6" aria-labelledby="pending-title">
    <h2 id="pending-title" class="text-lg font-bold mb-4">أحدث المشاركات المعلّقة</h2>
    @forelse($latestPending as $p)
      <div class="flex items-start justify-between gap-4 py-3 border-b border-line last:border-0">
        <div class="min-w-0">
          <p class="font-semibold">{{ $p->full_name }} <span class="text-muted font-normal text-sm">، {{ $p->department }}</span></p>
          <p class="text-sm"><b class="text-primary">{{ $p->selected_word }}</b>: {{ $p->shortMessage(80) }}</p>
        </div>
        <form method="POST" action="{{ route('admin.participations.status', $p) }}" class="flex-none">
          @csrf @method('PATCH')
          <button name="status" value="approved" class="btn btn-national btn-sm">اعتماد</button>
        </form>
      </div>
    @empty
      <p class="text-muted">لا توجد مشاركات بانتظار المراجعة.</p>
    @endforelse
  </section>

  <section class="panel p-6" aria-labelledby="words-title">
    <h2 id="words-title" class="text-lg font-bold mb-4">أكثر الكلمات اختيارًا</h2>
    @php($max = max(1, (int) ($stats['top_words']->first()?->total ?? 1)))
    <div class="bars">
      @forelse($stats['top_words'] as $w)
        <div class="bar"><span class="truncate">{{ $w->selected_word }}</span><span class="bar__track"><span class="bar__fill" data-bar="{{ round($w->total / $max * 100) }}"></span></span><span class="bar__num">{{ $w->total }}</span></div>
      @empty
        <p class="text-muted">لا توجد بيانات بعد.</p>
      @endforelse
    </div>

    <h2 class="text-lg font-bold mt-8 mb-2">رابط المشاركة</h2>
    <p class="text-sm text-muted mb-3">استخدمه في رمز QR والبوسترات والرسائل.</p>
    <div class="flex gap-2">
      <input class="input !min-h-10 text-sm" dir="ltr" readonly value="{{ route('participate') }}" data-copy-source aria-label="رابط المشاركة">
      <button type="button" class="btn btn-outline btn-sm" data-copy>نسخ</button>
    </div>
  </section>
</div>
@endsection
