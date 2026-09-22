{{-- لوحة من بصمات صغيرة تتجمع نحو المركز --}}
@php
  $cols = 14; $rows = 8; $cells = [];
  for ($r = 0; $r < $rows; $r++) {
    for ($c = 0; $c < $cols; $c++) {
      $d = hypot(($c - ($cols - 1) / 2) / $cols, ($r - ($rows - 1) / 2) / $rows);
      $n = abs(sin($c * 12.9898 + $r * 78.233) * 43758.5453);
      $n = $n - floor($n);
      $cells[] = ['x' => 20 + $c * 40, 'y' => 20 + $r * 40, 'on' => $n > $d * 1.25, 'tone' => ($c + $r) % 3, 'o' => round(max(.25, 1 - $d * 1.3), 2)];
    }
  }
  $tones = ['stroke-secondary', 'stroke-primary', 'stroke-accent'];
@endphp
<svg viewBox="0 0 560 320" role="img" aria-label="بصمات متعددة تتجمع لتكوّن لوحة واحدة" xmlns="http://www.w3.org/2000/svg">
  @foreach($cells as $cell)
    @if($cell['on'])
      <g fill="none" stroke-width="2" class="{{ $tones[$cell['tone']] }}" opacity="{{ $cell['o'] }}">
        <circle cx="{{ $cell['x'] }}" cy="{{ $cell['y'] }}" r="10"/>
        <circle cx="{{ $cell['x'] }}" cy="{{ $cell['y'] }}" r="4.5"/>
      </g>
    @else
      <circle cx="{{ $cell['x'] }}" cy="{{ $cell['y'] }}" r="2" class="fill-line"/>
    @endif
  @endforeach
</svg>
