@extends('layouts.app')
@section('title', 'وصلت بصمتك')
@section('page', 'success')

@section('content')
<section class="section pt-10" aria-labelledby="done-title">
  <div class="container-x max-w-[640px] text-center">

    <div class="join-stage" data-join>
      <div class="join-grid" aria-hidden="true">
        @for($i = 0; $i < 60; $i++)
          <i class="{{ $i === 30 ? 'target' : (($i * 7) % 5 === 0 ? 'on' : (($i * 11) % 7 === 0 ? 'g' : '')) }}" @if($i === 30) data-target @endif></i>
        @endfor
      </div>
      <x-mark :size="120" class="join-mark" data-join-mark />
    </div>

    <div class="after-join" data-after-join>
      <h1 id="done-title" class="text-[length:var(--fs-3xl)] font-bold mt-6">وصلت بصمتك!</h1>
      <p class="prose-intro mx-auto mt-2">شكرًا لك، أصبحت بصمتك جزءًا من لوحة منسوبي وطلبة الكلية.</p>

      <article class="share-card text-start mt-10" aria-label="بطاقة مشاركتك">
        <div class="flex items-center gap-4">
          <span class="avatar avatar-lg" aria-hidden="true">{{ $p->initials() }}</span>
          <div class="min-w-0">
            <p class="text-xl font-bold">{{ $p->full_name }}</p>
            <p class="text-muted text-sm">{{ $p->department }}</p>
          </div>
        </div>
        <p class="mt-6"><span class="word-pill">{{ $p->selected_word }}</span></p>
        <p class="text-lg leading-loose mt-4">«{{ $p->message }}»</p>
        <p class="text-sm text-muted mt-6">{{ config('basmah.title') }}</p>
      </article>

      <p class="hint mt-4">ستظهر بطاقتك في اللوحة بعد مراجعتها.</p>

      <div class="flex flex-wrap justify-center gap-3 mt-8">
        <a href="{{ route('explore') }}" class="btn btn-primary">شاهد البصمات</a>
        <button type="button" class="btn btn-outline" data-share
                data-share-title="{{ config('basmah.title') }}"
                data-share-text="تركت بصمتي في «{{ config('basmah.title') }}»: {{ $p->selected_word }} شاركنا بكلمتك"
                data-share-url="{{ route('home') }}">شارك بطاقتي</button>
      </div>
      <p class="hint mt-3" data-share-status aria-live="polite"></p>
    </div>
  </div>
</section>
@endsection
