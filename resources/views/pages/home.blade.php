@extends('layouts.app')
@section('page', 'home')

@section('content')
<section class="hero" aria-labelledby="hero-title">
  <div class="container-x hero__grid">
    <div class="hero__copy">
      <p class="occasion">{{ config('basmah.occasion') }}</p>
      <h1 id="hero-title" class="display-title">بصمتنا<span class="dots">..</span> في وطننا</h1>
      <p class="lead">{{ config('basmah.tagline') }}</p>

      <div class="hero__actions">
        <a href="{{ route('participate') }}" class="btn btn-primary btn-lg">أضف بصمتك</a>
        <a href="{{ route('explore') }}" class="btn btn-outline btn-lg">استكشف بصمات المنسوبين والطلبة</a>
      </div>

      @if($participants > 0)
        <p class="joined">ترك <b>{{ number_format($participants) }}</b> من منسوبي وطلبة الكلية بصمتهم حتى الآن.</p>
      @endif

      <p class="credit">مقدمة من <strong>{{ config('basmah.presenter') }}</strong></p>
    </div>

    <div class="hero__field" aria-hidden="true">
      <canvas data-field></canvas>
    </div>
  </div>
</section>

<section class="section bg-surface border-y border-line" aria-labelledby="about-title">
  <div class="container-x grid gap-10 lg:grid-cols-2 lg:items-center">
    <div>
      <h2 id="about-title" class="h-section">لأن لكل شخص بصمة...</h2>
      <p class="prose-intro">
        في اليوم الوطني، نجتمع على معنى واحد، ونترك لكل منسوب مساحة ليعبّر بكلمته عن وطنه.
        اجمع بصمتك مع بصمات زملائك لتصنعوا معًا لوحة رقمية تعبّر عن مشاعر منسوبي الكلية.
      </p>
      <a href="{{ route('participate') }}" class="btn btn-national mt-8">اترك بصمتك الآن</a>
    </div>
    <div class="board">
      <x-board />
    </div>
  </div>
</section>
@endsection
