@extends('layouts.app')
@section('title', 'اترك بصمتك')
@section('page', 'participate')

@section('content')
<section class="section pt-10" aria-labelledby="form-title">
  <div class="container-x max-w-[720px]">
    <div class="flex items-center gap-4 mb-8">
      <x-mark :size="46" :draw="true" />
      <div>
        <h1 id="form-title" class="text-[length:var(--fs-3xl)] font-bold">اترك بصمتك</h1>
        <p class="text-muted">دقيقة واحدة، وتصبح كلمتك جزءًا من لوحة الكلية.</p>
      </div>
    </div>

    @unless($open)
      <div class="panel p-8 text-center">
        <p class="text-xl font-semibold mb-2">اكتمل استقبال البصمات</p>
        <p class="text-muted mb-6">شكرًا لكل من شارك. يمكنك مشاهدة اللوحة الآن.</p>
        <a href="{{ route('explore') }}" class="btn btn-primary">شاهد البصمات</a>
      </div>
    @else
      @if($errors->any())
        <div class="alert alert-danger mb-6" role="alert" tabindex="-1" data-error-summary>
          <p class="font-semibold mb-1">راجع الحقول التالية:</p>
          <ul class="list-disc ps-5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('participate.store') }}" enctype="multipart/form-data"
            class="panel p-6 sm:p-9" novalidate data-participate-form>
        @csrf
        {{-- مصيدة للبرامج الآلية --}}
        <div class="sr-only" aria-hidden="true">
          <label for="website">اترك هذا الحقل فارغًا</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="field">
          <label class="label" for="full_name">الاسم الكامل <span class="req" aria-hidden="true">*</span></label>
          <input class="input @error('full_name') is-invalid @enderror" id="full_name" name="full_name" type="text"
                 value="{{ old('full_name') }}" required minlength="3" maxlength="{{ config('basmah.name_max') }}"
                 autocomplete="name" aria-describedby="full_name_hint full_name_err" @error('full_name') aria-invalid="true" @enderror>
          <p class="hint" id="full_name_hint">كما تحب أن يظهر في اللوحة، مثل: أ. سارة أحمد</p>
          <p class="error" id="full_name_err" data-error-for="full_name">@error('full_name'){{ $message }}@enderror</p>
        </div>

        <fieldset class="field">
          <legend class="label mb-2">منسوب أو طالب/ة <span class="req" aria-hidden="true">*</span></legend>
          <div class="chips" role="radiogroup" aria-describedby="audience_type_err">
            @foreach($audienceTypes as $value => $labelText)
              <label class="chip">
                <input type="radio" name="audience_type" value="{{ $value }}" @checked(old('audience_type') === $value) required>
                <span>{{ $labelText }}</span>
              </label>
            @endforeach
          </div>
          <p class="error" id="audience_type_err" data-error-for="audience_type">@error('audience_type'){{ $message }}@enderror</p>
        </fieldset>

        <div class="field">
          <label class="label" for="department">القسم / الوحدة <span class="req" aria-hidden="true">*</span></label>
          <select class="select @error('department') is-invalid @enderror" id="department" name="department" required
                  aria-describedby="department_err" data-other-toggle="#department_other_wrap" data-other-value="{{ config('basmah.other_department') }}">
            <option value="" disabled @selected(!old('department'))>اختر القسم</option>
            @foreach($departments as $d)
              <option value="{{ $d }}" @selected(old('department') === $d)>{{ $d }}</option>
            @endforeach
            <option value="{{ config('basmah.other_department') }}" @selected(old('department') === config('basmah.other_department'))>قسم أو وحدة أخرى</option>
          </select>
          <div id="department_other_wrap" class="mt-2" @if(old('department') !== config('basmah.other_department')) hidden @endif>
            <label class="sr-only" for="department_other">اسم القسم أو الوحدة</label>
            <input class="input" id="department_other" name="department_other" type="text" maxlength="80"
                   placeholder="اكتب اسم القسم أو الوحدة" value="{{ old('department_other') }}">
          </div>
          <p class="error" id="department_err" data-error-for="department">@error('department'){{ $message }}@enderror @error('department_other'){{ $message }}@enderror</p>
        </div>

        <fieldset class="field">
          <legend class="label mb-2">اختر كلمة تعبّر عن وطنك <span class="req" aria-hidden="true">*</span></legend>
          <div class="chips" role="radiogroup" aria-describedby="selected_word_err">
            @foreach([...$words, config('basmah.other_word')] as $w)
              <label class="chip">
                <input type="radio" name="selected_word" value="{{ $w }}" @checked(old('selected_word') === $w) required>
                <span>{{ $w }}</span>
              </label>
            @endforeach
          </div>
          <div id="other_word_wrap" class="mt-3" @if(old('selected_word') !== config('basmah.other_word')) hidden @endif>
            <label class="sr-only" for="other_word">كلمتك</label>
            <input class="input max-w-xs" id="other_word" name="other_word" type="text" maxlength="{{ config('basmah.custom_word_max') }}"
                   placeholder="اكتب كلمتك" value="{{ old('other_word') }}">
          </div>
          <p class="error" id="selected_word_err" data-error-for="selected_word">@error('selected_word'){{ $message }}@enderror @error('other_word'){{ $message }}@enderror</p>
        </fieldset>

        <div class="field">
          <div class="flex items-baseline justify-between gap-4">
            <label class="label" for="message">عبارتك <span class="req" aria-hidden="true">*</span></label>
            <span class="counter" data-counter aria-live="polite">0 / {{ config('basmah.message_max') }}</span>
          </div>
          <textarea class="textarea @error('message') is-invalid @enderror" id="message" name="message" rows="3" required
                    minlength="{{ config('basmah.message_min') }}" maxlength="{{ config('basmah.message_max') }}"
                    placeholder="السعودية بالنسبة لي..." aria-describedby="message_err">{{ old('message') }}</textarea>
          <p class="error" id="message_err" data-error-for="message">@error('message'){{ $message }}@enderror</p>
        </div>

        <div class="field">
          <span class="label" id="photo_label">صورة شخصية <span class="text-muted font-normal text-sm">(اختيارية)</span></span>
          <div class="upload">
            <div class="upload__preview" data-photo-preview>
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="9" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div class="grid gap-2">
              <div class="flex flex-wrap gap-2">
                <label for="photo" class="btn btn-outline btn-sm cursor-pointer">اختر صورة</label>
                <button type="button" class="btn btn-ghost btn-sm" data-photo-clear hidden>إزالة</button>
              </div>
              <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" aria-labelledby="photo_label" aria-describedby="photo_hint photo_err">
              <p class="hint" id="photo_hint">JPG أو PNG أو WEBP، حتى 3 ميجابايت.</p>
            </div>
          </div>
          <label class="check mt-3" data-photo-consent hidden>
            <input type="checkbox" name="show_image" value="1" @checked(old('show_image'))>
            <span class="text-sm">أوافق على عرض صورتي مع مشاركتي.</span>
          </label>
          <p class="error" id="photo_err" data-error-for="photo">@error('photo'){{ $message }}@enderror</p>
        </div>

        <hr class="my-8 border-line">

        <label class="check">
          <input type="checkbox" name="consent" value="1" required @checked(old('consent')) aria-describedby="consent_err">
          <span>أوافق على عرض اسمي ومشاركتي ضمن لوحة «بصمتنا.. في وطننا».</span>
        </label>
        <p class="hint mt-2 ps-9">تُراجع كل مشاركة قبل ظهورها في اللوحة وشاشات الكلية.</p>
        <p class="error ps-9" id="consent_err" data-error-for="consent">@error('consent'){{ $message }}@enderror @error('form'){{ $message }}@enderror</p>

        <button type="submit" class="btn btn-primary btn-lg w-full mt-8" data-submit>
          <span class="spinner" aria-hidden="true"></span>
          <span data-submit-label>أرسل بصمتك</span>
        </button>
      </form>
    @endunless
  </div>
</section>
@endsection
