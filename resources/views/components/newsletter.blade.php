@props(['galleryItems' => collect()])

@php
  $pool = $galleryItems->whereNotNull('image_url')->where('image_url', '!=', '')->values();
  $photos = $pool->isEmpty()
    ? collect()
    : collect(range(0, 3))->map(fn (int $i) => $pool->get($i % $pool->count()));
@endphp

<section id="newsletter" class="newsletter-section" data-motion="newsletter" aria-labelledby="newsletter-title">
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
      <p class="label">{{ __('Newsletter') }}</p>
      <h2 id="newsletter-title" class="big">
        <span class="big-line">{{ __('Daftar email') }}</span>
        <span class="big-accent">{{ __('info terbaru') }}</span>
      </h2>

      @if (session('newsletter_status'))
        <p class="newsletter-flash" role="status">{{ session('newsletter_status') }}</p>
      @endif

      @if ($errors->has('name') || $errors->has('email'))
        <p class="newsletter-flash newsletter-flash--error" role="alert">
          {{ $errors->first('name') ?: $errors->first('email') }}
        </p>
      @endif

      <form class="newsletter-form" action="{{ route('newsletter.subscribe') }}" method="post">
        @csrf
        <input type="text" name="company" value="" tabindex="-1" autocomplete="off" class="newsletter-honeypot" aria-hidden="true" />
        <label class="newsletter-field">
          <span class="visually-hidden">{{ __('Nama Anda') }}</span>
          <input
            type="text"
            name="name"
            value="{{ old('name') }}"
            placeholder="{{ __('Nama Anda') }}"
            required
            autocomplete="name"
          />
        </label>
        <label class="newsletter-field">
          <span class="visually-hidden">{{ __('Email') }}</span>
          <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="{{ __('Email Anda') }}"
            required
            autocomplete="email"
            inputmode="email"
          />
        </label>
        <button type="submit">{{ __('Daftar') }}</button>
      </form>
      <p class="sub">{{ __('Kabar produk, tips packing hantaran, dan cerita dari pengrajin kami.') }}</p>
    </div>
  </div>
</section>
