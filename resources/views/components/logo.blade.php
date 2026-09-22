{{-- الشعار الرسمي كما هو: تغيير الحجم فقط مع الحفاظ على النسبة (width + height:auto) --}}
@props(['variant' => 'header', 'eager' => false])
<img src="{{ asset('assets/logos/taibah-university-1200.png') }}"
     srcset="{{ asset('assets/logos/taibah-university-1200.png') }} 1200w, {{ asset('assets/logos/taibah-university.png') }} 3840w"
     sizes="{{ $variant === 'display' ? '(min-width: 1600px) 520px, 34vw' : '300px' }}"
     width="1200" height="277"
     alt="شعار جامعة طيبة"
     loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
     {{ $attributes->class(['logo', 'logo-'.$variant]) }}>
