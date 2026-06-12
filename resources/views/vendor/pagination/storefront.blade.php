@if ($paginator->hasPages())
  <nav role="navigation" aria-label="{{ __('Navigasi halaman') }}">
    @if ($paginator->onFirstPage())
      <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">&lsaquo;</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">&lsaquo;</a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span aria-disabled="true">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span aria-current="page">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">&rsaquo;</a>
    @else
      <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">&rsaquo;</span>
    @endif
  </nav>
@endif
