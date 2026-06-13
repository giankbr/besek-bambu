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
  $words = array_values(array_filter(explode(' ', $brandName)));
  $isLongBrand = count($words) > 3;
  $half = (int) ceil(count($words) / 2);
  $brandLines = [
    implode(' ', array_slice($words, 0, $half)),
    implode(' ', array_slice($words, $half)),
  ];
@endphp
<footer @class([
  'site-footer',
  'site-footer--compact' => ! request()->routeIs('home', 'about', 'contact', 'gallery', 'faq', 'blog.*', 'wholesale', 'privacy', 'terms', 'cart.show', 'checkout.show', 'checkout.confirmation', 'payment.pay'),
])>
  <div class="container">
    <div class="foot-upper">
      <p class="foot-tag">{{ $tagline }}</p>
      <nav class="foot-nav" aria-label="{{ __('Navigasi footer') }}">
        <a href="{{ route('shop.index') }}">{{ __('nav.shop') }}</a>
        <a href="{{ route('wholesale') }}">{{ __('Grosir & Custom') }}</a>
        <a href="{{ route('blog.index') }}">{{ __('Blog') }}</a>
        <a href="{{ route('gallery') }}">{{ __('nav.gallery') }}</a>
        <a href="{{ route('about') }}">{{ __('nav.about') }}</a>
        <a href="{{ route('faq') }}">{{ __('FAQ') }}</a>
        <a href="{{ route('contact') }}">{{ __('nav.contact') }}</a>
      </nav>
    </div>

    <div class="foot-mega" data-mega-brand>
      <div @class(['foot-mega__brand', 'mega-brand', 'mega-brand--long' => $isLongBrand]) aria-label="{{ $brandName }}">
        @foreach (array_filter($brandLines) as $index => $line)
          <span class="mega-brand__line">
            <span class="mega-brand__fill @if ($index > 0) mega-brand__fill--accent @endif">{{ $line }}</span>
          </span>
        @endforeach
      </div>
    </div>

    <div class="foot-lower">
      <a class="join-btn" href="{{ route('shop.index') }}">{{ __('Belanja sekarang') }} ↗</a>
      <div class="foot-lower__aside">
        <nav class="foot-legal" aria-label="{{ __('Legal') }}">
          <a href="{{ route('privacy') }}">{{ __('Kebijakan Privasi') }}</a>
          <a href="{{ route('terms') }}">{{ __('Syarat & Ketentuan') }}</a>
        </nav>
        @if (count($socials) > 0)
          <div class="foot-socials">
            @foreach ($socials as $key => $url)
              <a href="{{ $url }}" target="_blank" rel="noopener noreferrer{{ str_contains($url, 'wa.me') ? ' nofollow' : '' }}">{{ $socialLabels[$key] ?? ucfirst($key) }}</a>
            @endforeach
          </div>
        @endif
        <p class="foot-meta">© {{ date('Y') }} {{ $brandName }}</p>
      </div>
    </div>
  </div>
</footer>
