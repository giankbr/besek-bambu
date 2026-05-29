@props(['galleryItems' => collect()])

@php
  $pool = $galleryItems->whereNotNull('image_url')->where('image_url', '!=', '')->values();
  $photos = $pool->isEmpty()
    ? collect()
    : collect(range(0, 3))->map(fn (int $i) => $pool->get($i % $pool->count()));
@endphp

<section class="newsletter-section" aria-labelledby="newsletter-title">
  <div class="newsletter">
    @if ($photos->isNotEmpty())
      <div class="newsletter-photos" aria-hidden="true">
        @foreach ($photos as $photo)
          <figure class="news-photo">
            <img src="{{ image_src($photo->image_url) }}" alt="" width="200" height="200" loading="lazy" decoding="async" />
          </figure>
        @endforeach
      </div>
    @endif

    <div class="news-center">
      <p class="label">{{ __('Info & promo') }}</p>
      <h2 id="newsletter-title" class="big">
        <span class="big-line">{{ __('Daftar email') }}</span>
        <span class="big-accent">{{ __('diskon 10%') }}</span>
      </h2>
      <form class="newsletter-form" action="#" method="post" onsubmit="event.preventDefault();">
        @csrf
        <label class="newsletter-field">
          <span class="visually-hidden">{{ __('Email') }}</span>
          <input type="email" name="email" placeholder="{{ __('Email Anda') }}" required autocomplete="email" inputmode="email" />
        </label>
        <button type="submit">{{ __('Daftar') }}</button>
      </form>
      <p class="sub">{{ __('Tips packing hantaran, ide isian besek, dan kode diskon untuk pembelian besek anyaman bambu berikutnya.') }}</p>
    </div>
  </div>
</section>
