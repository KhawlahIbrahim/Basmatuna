{{-- «البصمة»: خطوط متداخلة مستلهمة من الأضلاع المتتالية في شعار الجامعة (ليست الشعار نفسه) --}}
@props(['size' => 120, 'draw' => false, 'label' => null])
@php($gid = 'mk-'.\Illuminate\Support\Str::random(6))
<svg {{ $attributes->class(['mark', 'mark-draw' => $draw]) }} viewBox="0 0 120 146" width="{{ $size }}" height="{{ round($size * 146 / 120) }}"
     @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif focusable="false" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="1" y2="1">
      <stop class="s1" offset="0"/><stop class="s2" offset=".55"/><stop class="s3" offset="1"/>
    </linearGradient>
  </defs>
  <g fill="none" stroke="url(#{{ $gid }})" stroke-width="3.2" stroke-linecap="round">
    <path d="M57.4 82.3A7.0 9.0 0 0 1 60.0 65.0A7.0 9.0 0 0 1 62.6 82.3"/>
    <path d="M52.2 87.3A13.2 16.4 0 0 1 59.1 57.6A13.2 16.4 0 0 1 69.2 85.8"/>
    <path d="M45.1 89.3A19.4 23.8 0 0 1 64.7 50.9A19.4 23.8 0 0 1 67.3 96.1"/>
    <path d="M50.4 102.9A25.6 31.2 0 0 1 55.1 43.4A25.6 31.2 0 0 1 77.8 96.4"/>
    <path d="M41.3 105.2A31.8 38.6 0 0 1 63.9 35.7A31.8 38.6 0 0 1 71.9 109.8"/>
    <path d="M30.9 103.6A38.0 46.0 0 0 1 62.0 28.1A38.0 46.0 0 0 1 86.4 107.1"/>
    <path d="M43.4 123.5A44.2 53.4 0 0 1 60.0 20.6A44.2 53.4 0 0 1 76.6 123.5"/>
    <path d="M30.4 123.2A50.4 60.8 0 0 1 56.5 13.3A50.4 60.8 0 0 1 95.0 117.7"/>
    <path d="M16.6 117.8A56.6 68.2 0 0 1 69.8 6.8A56.6 68.2 0 0 1 88.3 133.1"/>
  </g>
</svg>
