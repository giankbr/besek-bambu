@props([
  'crumbs' => [],
  'eyebrow' => null,
  'compact' => false,
  'schema' => true,
])

@if ($schema && count($crumbs))
  @push('head')
    @php
      $breadcrumbItems = [];
      foreach ($crumbs as $index => $crumb) {
        $position = $index + 1;
        $label = (string) ($crumb['label'] ?? '');
        if ($label === '') {
          continue;
        }

        $itemUrl = $crumb['url'] ?? null;
        if (! $itemUrl && $position === count($crumbs)) {
          $itemUrl = url()->current();
        }

        $item = [
          '@type' => 'ListItem',
          'position' => $position,
          'name' => $label,
        ];

        if ($itemUrl) {
          $item['item'] = (string) $itemUrl;
        }

        $breadcrumbItems[] = $item;
      }

      $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
      ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
  @endpush
@endif

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
