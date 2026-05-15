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

<div @class(['shop-head', 'shop-head--compact' => $compact])>
  @if (count($crumbs))
    <nav class="shop-head__trail eyebrow" aria-label="{{ __('Breadcrumb') }}">
      @foreach ($crumbs as $crumb)
        @if (! $loop->first)
          <span class="shop-head__sep" aria-hidden="true">·</span>
        @endif
        @if (! empty($crumb['url'] ?? null) && ! $loop->last)
          <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
        @else
          <span @if ($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
        @endif
      @endforeach
    </nav>
  @elseif (filled($eyebrow))
    <p class="eyebrow">{{ $eyebrow }}</p>
  @endif

  @if ($compact && count($crumbs) && filled($eyebrow))
    <p class="eyebrow shop-head__tag">{{ $eyebrow }}</p>
  @endif

  @unless ($compact)
    <div class="shop-head__heading">
      {{ $slot }}
    </div>
  @endunless
</div>
