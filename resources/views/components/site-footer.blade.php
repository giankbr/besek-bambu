@php
  $brandName = store_name();
  $tagline = setting('store_tagline') ?: __('Besek anyaman bambu untuk hantaran, kurban, dan kebutuhan ramah lingkungan Anda');
  $socials = store_socials();
  $socialLabels = [
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'tiktok' => 'TikTok',
    'whatsapp' => 'WhatsApp',
  ];
@endphp
<footer class="site-footer">
  <div class="container">
    <div class="foot-band">
      <div>
        <p class="foot-tag">{{ $tagline }}, <em>{{ __('besek bambu') }}</em></p>
        <a class="join-btn" href="{{ route('shop.index') }}">{{ __('Belanja sekarang') }} ↗</a>
      </div>
      <div class="foot-cols">
        <a href="{{ route('shop.index') }}">{{ __('nav.shop') }}</a>
        <a href="{{ route('gallery') }}">{{ __('nav.gallery') }}</a>
        <a href="{{ route('about') }}">{{ __('nav.about') }}</a>
        <a href="{{ route('faq') }}">{{ __('FAQ') }}</a>
        <a href="{{ route('contact') }}">{{ __('nav.contact') }}</a>
      </div>
    </div>

    <div class="mega-logo" data-mega-brand>
      <div class="word mega-brand" aria-label="{{ $brandName }}">
        @foreach (array_values(array_filter(explode(' ', $brandName))) as $index => $part)
          <span class="mega-brand__line">
            <span
              class="mega-brand__fill @if ($index > 0) mega-brand__fill--accent @endif"
            >{{ $part }}</span>
          </span>
        @endforeach
      </div>
      <div class="socials">
        @if (count($socials) > 0)
          @foreach ($socials as $key => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $socialLabels[$key] ?? ucfirst($key) }}</a>
          @endforeach
        @else
          <a href="#">Instagram</a>
          <a href="#">Facebook</a>
          <a href="#">TikTok</a>
        @endif
      </div>
    </div>
  </div>
</footer>
