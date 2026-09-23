@props(['p', 'reveal' => true])
<article {{ $attributes->class(['p-card', 'reveal' => $reveal]) }}>
  <div class="p-card__word">{{ $p->selected_word }}</div>
  <p class="p-card__msg">{{ $p->message }}</p>
  <footer class="p-card__who">
    <span class="avatar">
      <span aria-hidden="true">{{ $p->initials() }}</span>
    </span>
    <span class="min-w-0">
      <span class="block p-card__name">{{ $p->full_name }}</span>
      <span class="block p-card__dept">{{ $p->department }}</span>
    </span>
  </footer>
</article>
