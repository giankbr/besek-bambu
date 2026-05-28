@props(['galleryItems' => collect()])

@php
  $corners = $galleryItems->whereNotNull('image_url')->where('image_url', '!=', '')->take(4)->values();
@endphp

<section class="newsletter-section" aria-labelledby="newsletter-title">
  <div class="newsletter">
    @if ($corners->get(0))
    <figure class="news-corner news-corner--tl" aria-hidden="true">
      <img src="{{ image_src($corners->get(0)->image_url) }}" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    @endif
    @if ($corners->get(1))
    <figure class="news-corner news-corner--bl" aria-hidden="true">
      <img src="{{ image_src($corners->get(1)->image_url) }}" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    @endif
    @if ($corners->get(2))
    <figure class="news-corner news-corner--tr" aria-hidden="true">
      <img src="{{ image_src($corners->get(2)->image_url) }}" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    @endif
    @if ($corners->get(3))
    <figure class="news-corner news-corner--br" aria-hidden="true">
      <img src="{{ image_src($corners->get(3)->image_url) }}" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
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
