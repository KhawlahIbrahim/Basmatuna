@extends('layouts.admin')
@section('title', $p->full_name)

@section('content')
<a href="{{ route('admin.participations.index') }}" class="text-sm text-muted underline underline-offset-4">المشاركات</a>
<div class="grid gap-6 lg:grid-cols-[1.5fr_1fr] mt-4">
  <article class="share-card">
    <div class="flex items-center gap-4">
      <span class="avatar avatar-lg">
        @if($p->image_path)<img src="{{ $p->adminImageUrl() }}" alt="صورة {{ $p->full_name }}">@else<span aria-hidden="true">{{ $p->initials() }}</span>@endif
      </span>
      <div>
        <h1 class="text-2xl font-bold">{{ $p->full_name }}</h1>
        <p class="text-muted">{{ $p->audienceLabel() }} · {{ $p->department }}</p>
      </div>
    </div>
    <p class="mt-6"><span class="word-pill">{{ $p->selected_word }}</span> @if($p->is_custom_word)<span class="text-xs text-muted ms-2">كلمة كتبها المشارك</span>@endif</p>
    <p class="text-lg leading-loose mt-4">«{{ $p->message }}»</p>
  </article>

  <aside class="panel p-6 text-sm grid gap-3 content-start">
    <p>الحالة: <span class="badge badge--{{ $p->status->tone() }}">{{ $p->status->label() }}</span> @if($p->is_demo)<span class="badge badge--demo">تجريبية</span>@endif</p>
    <p>تاريخ الإرسال: {{ $p->created_at->timezone(config('app.timezone'))->format('Y/m/d H:i') }}</p>
    <p>الموافقة على العرض: {{ $p->consent ? 'نعم' : 'لا' }}@if($p->consented_at) ({{ $p->consented_at->timezone(config('app.timezone'))->format('Y/m/d H:i') }})@endif</p>
    <p>الموافقة على عرض الصورة: {{ $p->image_path ? ($p->show_image ? 'نعم' : 'لا') : 'لا توجد صورة' }}</p>
    @if($p->reviewed_at)<p>آخر مراجعة: {{ $p->reviewer?->name ?? '—' }}، {{ $p->reviewed_at->timezone(config('app.timezone'))->format('Y/m/d H:i') }}</p>@endif

    <div class="flex flex-wrap gap-2 mt-3">
      <form method="POST" action="{{ route('admin.participations.status', $p) }}" class="flex flex-wrap gap-2">
        @csrf @method('PATCH')
        <button class="btn btn-national btn-sm" name="status" value="approved" @disabled($p->status->value === 'approved')>اعتماد</button>
        <button class="btn btn-outline btn-sm" name="status" value="hidden" @disabled($p->status->value === 'hidden')>إخفاء</button>
        <button class="btn btn-ghost btn-sm" name="status" value="pending" @disabled($p->status->value === 'pending')>إرجاع للمراجعة</button>
      </form>
      <form method="POST" action="{{ route('admin.participations.destroy', $p) }}" data-confirm="حذف هذه المشاركة نهائيًا؟">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">حذف</button>
      </form>
    </div>
  </aside>
</div>
@endsection
