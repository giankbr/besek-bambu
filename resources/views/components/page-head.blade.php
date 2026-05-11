@props([
  'crumbs' => [],
  'eyebrow' => null,
  'compact' => false,
])

<header @class(['page-head', 'page-head--compact' => $compact])>
  @if (count($crumbs))
    <nav class="page-head__crumbs" aria-label="{{ __('Breadcrumb') }}">
      <ol class="page-head__crumbs-list">
        @foreach ($crumbs as $crumb)
          <li class="page-head__crumbs-item">
            @if (! empty($crumb['url'] ?? null) && ! $loop->last)
              <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
            @else
              <span
                class="page-head__crumbs-target @if ($loop->last) page-head__crumbs-current @endif"
                @if ($loop->last) aria-current="page" @endif
              >{{ $crumb['label'] }}</span>
            @endif
          </li>
        @endforeach
      </ol>
    </nav>
  @endif

  @if ($compact && filled($eyebrow))
    <p class="eyebrow page-head__eyebrow">{{ $eyebrow }}</p>
  @endif

  @if (! $compact)
    <div class="page-head__main">
      @if (filled($eyebrow))
        <p class="eyebrow page-head__eyebrow">{{ $eyebrow }}</p>
      @endif
      <div class="page-head__heading">
        {{ $slot }}
      </div>
    </div>
  @endif
</header>
