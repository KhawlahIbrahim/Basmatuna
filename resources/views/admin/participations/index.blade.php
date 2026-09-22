@extends('layouts.admin')
@section('title', 'المشاركات')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
  <h1 class="text-2xl font-bold">المشاركات</h1>
  <a href="{{ route('admin.participations.export') }}" class="btn btn-outline btn-sm">تصدير CSV</a>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <nav class="flex flex-wrap gap-2" aria-label="تصفية حسب الحالة">
    <a class="chip chip--link" href="{{ route('admin.participations.index', array_filter(['q' => $q])) }}" @if(!$status) aria-current="true" @endif><span>الكل ({{ $counts->sum() }})</span></a>
    @foreach(\App\Enums\ParticipationStatus::cases() as $s)
      <a class="chip chip--link" href="{{ route('admin.participations.index', array_filter(['status' => $s->value, 'q' => $q])) }}" @if($status === $s->value) aria-current="true" @endif><span>{{ $s->label() }} ({{ $counts[$s->value] ?? 0 }})</span></a>
    @endforeach
  </nav>
  <form method="GET" class="flex gap-2" role="search">
    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    <label class="sr-only" for="q">بحث</label>
    <input class="input !min-h-10 text-sm" type="search" id="q" name="q" value="{{ $q }}" placeholder="اسم، قسم، أو عبارة">
    <button class="btn btn-primary btn-sm">بحث</button>
  </form>
</div>

<form method="POST" action="{{ route('admin.participations.bulk') }}" id="bulk-form" data-bulk-form>
  @csrf
  <div class="flex flex-wrap items-center gap-2 mb-3 text-sm" data-bulk-bar>
    <span class="text-muted"><b data-bulk-count>0</b> محددة</span>
    <button class="btn btn-national btn-sm" name="action" value="approved" data-bulk-btn disabled>اعتماد</button>
    <button class="btn btn-outline btn-sm" name="action" value="hidden" data-bulk-btn disabled>إخفاء</button>
    <button class="btn btn-danger btn-sm" name="action" value="delete" data-bulk-btn disabled data-confirm="حذف المشاركات المحددة نهائيًا؟">حذف</button>
  </div>
</form>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th scope="col"><input type="checkbox" aria-label="تحديد الكل" data-select-all></th>
        <th scope="col">الاسم</th>
        <th scope="col">الفئة</th>
        <th scope="col">القسم</th>
        <th scope="col">الكلمة</th>
        <th scope="col">العبارة</th>
        <th scope="col">الصورة</th>
        <th scope="col">التاريخ</th>
        <th scope="col">الحالة</th>
        <th scope="col"><span class="sr-only">إجراءات</span></th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $p)
        <tr>
          <td><input type="checkbox" name="ids[]" value="{{ $p->id }}" form="bulk-form" aria-label="تحديد {{ $p->full_name }}" data-row-check></td>
          <td class="font-semibold">{{ $p->full_name }} @if($p->is_demo)<span class="badge badge--demo">تجريبية</span>@endif</td>
          <td>{{ $p->audienceLabel() }}</td>
          <td>{{ $p->department }}</td>
          <td><b class="text-primary">{{ $p->selected_word }}</b></td>
          <td class="max-w-[280px]">{{ $p->shortMessage(70) }}</td>
          <td>
            @if($p->image_path)
              <img src="{{ $p->adminImageUrl() }}" alt="صورة {{ $p->full_name }}" width="40" height="40" class="w-10 h-10 rounded-full object-cover" loading="lazy">
              @unless($p->show_image)<span class="block text-xs text-muted">غير موافق على عرضها</span>@endunless
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="whitespace-nowrap text-muted">{{ $p->created_at->timezone(config('app.timezone'))->format('Y/m/d H:i') }}</td>
          <td><span class="badge badge--{{ $p->status->tone() }}">{{ $p->status->label() }}</span></td>
          <td>
            <div class="flex gap-1 justify-end">
              <a class="btn btn-ghost btn-sm" href="{{ route('admin.participations.show', $p) }}">عرض</a>
              <form method="POST" action="{{ route('admin.participations.status', $p) }}">
                @csrf @method('PATCH')
                @if($p->status !== \App\Enums\ParticipationStatus::Approved)
                  <button class="btn btn-national btn-sm" name="status" value="approved">اعتماد</button>
                @else
                  <button class="btn btn-outline btn-sm" name="status" value="hidden">إخفاء</button>
                @endif
              </form>
              <form method="POST" action="{{ route('admin.participations.destroy', $p) }}" data-confirm="حذف مشاركة «{{ $p->full_name }}» نهائيًا؟">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">حذف</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="10" class="text-center text-muted py-10">لا توجد مشاركات.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
{{ $items->links() }}
@endsection
