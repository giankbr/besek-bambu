@props(['galleryItems' => collect()])

@php
  $pool = $galleryItems->whereNotNull('image_url')->where('image_url', '!=', '')->values();
  // 3 collage + 2 inline slots; cycle gallery when fewer than 5 (since 7be2466 dropped hardcoded URLs)
  $slot = fn (int $i) => $pool->isEmpty() ? null : $pool->get($i % $pool->count());
@endphp

<section class="collage-wrap" data-collage-section>
  <div class="collage">
    @if ($item = $slot(0))
      <img class="c-side" src="{{ image_src($item->image_url) }}" alt="{{ $item->title ?? '' }}" loading="lazy" decoding="async" />
    @endif
    @if ($item = $slot(1))
      <img class="c-main" src="{{ image_src($item->image_url) }}" alt="{{ $item->title ?? '' }}" loading="lazy" decoding="async" />
    @endif
    @if ($item = $slot(2))
      <img class="c-side right" src="{{ image_src($item->image_url) }}" alt="{{ $item->title ?? '' }}" loading="lazy" decoding="async" />
    @endif
  </div>

  <p class="commitment" data-collage-commitment>
    {{ __('Komitmen kami pada') }}
    @if ($item = $slot(3))
      <img class="inline-img" src="{{ image_src($item->image_url) }}" alt="" loading="lazy" decoding="async" />
    @endif
    {!! __('<em>bambu berkualitas</em>, anyaman tangan pengrajin, dan <em>mitra lokal</em>, demi wadah aman untuk makanan dan') !!}
    @if ($item = $slot(4))
      <img class="inline-img" src="{{ image_src($item->image_url) }}" alt="" loading="lazy" decoding="async" />
    @endif
    {!! __('<em>tradisi yang tetap hidup.</em>') !!}
  </p>
</section>
