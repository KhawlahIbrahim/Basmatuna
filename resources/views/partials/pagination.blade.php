@if ($paginator->hasPages())
  <nav class="pager" aria-label="التنقل بين الصفحات">
    @if ($paginator->onFirstPage())
      <span class="btn btn-outline btn-sm" aria-disabled="true">السابق</span>
    @else
      <a class="btn btn-outline btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
    @endif
    <span>صفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())
      <a class="btn btn-outline btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
    @else
      <span class="btn btn-outline btn-sm" aria-disabled="true">التالي</span>
    @endif
  </nav>
@endif
