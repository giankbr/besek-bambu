@php
  $brandName = store_name();
  $tagline = setting('store_tagline') ?: __('Besek bambu handmade untuk Hantaran, hampers & kemasan.');
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
        <p class="foot-tag">{{ $tagline }}</p>
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
        @php
          $words = array_values(array_filter(explode(' ', $brandName)));
          $half = (int) ceil(count($words) / 2);
          $lines = [
            implode(' ', array_slice($words, 0, $half)),
            implode(' ', array_slice($words, $half)),
          ];
        @endphp
        @foreach (array_filter($lines) as $index => $line)
          <span class="mega-brand__line">
            <span class="mega-brand__fill @if ($index > 0) mega-brand__fill--accent @endif">{{ $line }}</span>
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
