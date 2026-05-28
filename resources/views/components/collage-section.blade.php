@props(['galleryItems' => collect()])

@php
  $imgs = $galleryItems->whereNotNull('image_url')->where('image_url', '!=', '')->values();
@endphp

<section class="collage-wrap" data-collage-section>
  <div class="collage">
    @if ($imgs->get(0))
      <img class="c-side" src="{{ image_src($imgs->get(0)->image_url) }}" alt="{{ $imgs->get(0)->title ?? '' }}" loading="lazy" />
    @endif
    @if ($imgs->get(1))
      <img class="c-main" src="{{ image_src($imgs->get(1)->image_url) }}" alt="{{ $imgs->get(1)->title ?? '' }}" loading="lazy" />
    @endif
    @if ($imgs->get(2))
      <img class="c-side right" src="{{ image_src($imgs->get(2)->image_url) }}" alt="{{ $imgs->get(2)->title ?? '' }}" loading="lazy" />
    @endif
  </div>

  <p class="commitment" data-collage-commitment>
    {{ __('Komitmen kami pada') }}
    @if ($imgs->get(3))
      <img class="inline-img" src="{{ image_src($imgs->get(3)->image_url) }}" alt="" loading="lazy" />
    @endif
    {!! __('<em>bambu berkualitas</em>, anyaman tangan pengrajin, dan <em>mitra lokal</em>, demi wadah aman untuk makanan dan') !!}
    @if ($imgs->get(4))
      <img class="inline-img" src="{{ image_src($imgs->get(4)->image_url) }}" alt="" loading="lazy" />
    @endif
    {!! __('<em>tradisi yang tetap hidup.</em>') !!}
  </p>
</section>
