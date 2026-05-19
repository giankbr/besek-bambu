@php
  $brandLogo = store_logo_url();
  $brandName = store_name();
  $navTagline = \Illuminate\Support\Str::limit(
    (string) (setting('store_tagline') ?: __('Besek anyaman bambu untuk hantaran & kemasan ramah lingkungan')),
    90,
  );
  $isHome = request()->routeIs('home');
  $isShop = request()->routeIs('shop.*');
  $isGallery = request()->routeIs('gallery');
  $isAbout = request()->routeIs('about');
  $isContact = request()->routeIs('contact');
  $isAccountArea = request()->routeIs(
    'account.*',
    'profile.*',
    'appearance.*',
    'security.*',
    'login',
    'register',
    'password.request',
    'password.reset',
    'password.confirm',
    'verification.notice',
    'two-factor.*',
  );
  $isCartFlow = request()->routeIs('cart.*', 'checkout.*', 'payment.pay');
  $cartCount = app(\App\Services\CartService::class)->count();
  $locales = ['id' => 'Indonesia', 'en' => 'English'];
  $activeLocale = app()->getLocale();

  $mobileNavLinks = [
    ['route' => 'shop.index', 'label' => __('nav.shop'), 'active' => $isShop],
    ['route' => 'gallery', 'label' => __('nav.gallery'), 'active' => $isGallery],
    ['route' => 'about', 'label' => __('nav.about'), 'active' => $isAbout],
    ['route' => 'contact', 'label' => __('nav.contact'), 'active' => $isContact],
  ];
@endphp

<header class="site-header">
  <div class="container">
    <nav class="navbar">
      <button
        type="button"
        class="nav-mobile__btn"
        data-nav-mobile-toggle
        aria-expanded="false"
        aria-controls="nav-mobile-panel"
        aria-label="{{ __('Menu') }}"
      >
        <span class="nav-mobile__icon nav-mobile__icon--menu" aria-hidden="true"><x-icons.menu /></span>
        <span class="nav-mobile__icon nav-mobile__icon--close" aria-hidden="true"><x-icons.close /></span>
      </button>

      <ul class="nav-links">
        @foreach ($mobileNavLinks as $link)
          <li>
            <a
              href="{{ route($link['route']) }}"
              class="@if ($link['active']) is-active @endif"
              @if ($link['active']) aria-current="page" @endif
            >{{ $link['label'] }}</a>
          </li>
        @endforeach
      </ul>

      <a
        class="logo @if ($isHome) is-active @endif"
        href="{{ route('home') }}"
        aria-label="{{ $brandName }}"
        @if ($isHome) aria-current="page" @endif
      >
        @if ($brandLogo)
          <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="sf-brand-logo" width="120" height="32" />
        @else
          {{ \Illuminate\Support\Str::lower($brandName) }}
        @endif
      </a>

      <div class="nav-actions">
        <div class="nav-actions__cluster">
          @auth
            <a
              href="{{ route('account.index') }}"
              class="nav-actions__link @if ($isAccountArea) is-active @endif"
              aria-label="{{ __('nav.account') }}"
              @if ($isAccountArea) aria-current="page" @endif
            >
              <x-icons.user class="nav-actions__icon" />
              <span class="nav-actions__label">{{ auth()->user()->name }}</span>
            </a>
          @else
            <a
              href="{{ route('login') }}"
              class="nav-actions__link @if ($isAccountArea) is-active @endif"
              aria-label="{{ __('nav.login_register') }}"
              @if ($isAccountArea) aria-current="page" @endif
            >
              <x-icons.user class="nav-actions__icon" />
              <span class="nav-actions__label">{{ __('nav.login_register') }}</span>
            </a>
          @endauth
          <a
            href="{{ route('cart.show') }}"
            class="nav-actions__link nav-actions__link--cart @if ($isCartFlow) is-active @endif"
            aria-label="{{ __('nav.cart') }} ({{ $cartCount }})"
            @if ($isCartFlow) aria-current="page" @endif
          >
            <x-icons.cart class="nav-actions__icon" />
            <span class="nav-actions__cart-badge" aria-hidden="true">{{ $cartCount }}</span>
            <span class="nav-actions__label nav-actions__label--cart">{{ __('nav.cart') }} ({{ $cartCount }})</span>
          </a>
          <x-theme-toggle />
          <details class="nav-lang" data-nav-lang>
            <summary class="nav-lang__toggle" aria-label="{{ __('nav.language') }}">
              <x-icons.globe class="nav-lang__icon" />
              <span class="nav-lang__current">{{ strtoupper($activeLocale) }}</span>
              <svg class="nav-lang__caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
            </summary>
            <div class="nav-lang__menu" role="menu">
              @foreach ($locales as $code => $label)
                <a
                  href="{{ route('locale.switch', $code) }}"
                  class="nav-lang__item @if ($activeLocale === $code) is-active @endif"
                  role="menuitem"
                  @if ($activeLocale === $code) aria-current="true" @endif
                >
                  <span class="nav-lang__dot" aria-hidden="true"></span>
                  {{ $label }}
                </a>
              @endforeach
            </div>
          </details>
        </div>
      </div>
    </nav>
  </div>

  <div
    id="nav-mobile-panel"
    class="nav-mobile__panel"
    data-nav-mobile-panel
    hidden
  >
    <div class="nav-mobile__head">
      <a
        href="{{ route('home') }}"
        class="nav-mobile__brand"
        @if ($isHome) aria-current="page" @endif
      >
        @if ($brandLogo)
          <img src="{{ $brandLogo }}" alt="" class="sf-brand-logo" width="120" height="32" />
        @else
          <span class="nav-mobile__brand-text">{{ \Illuminate\Support\Str::lower($brandName) }}</span>
        @endif
      </a>
      <button
        type="button"
        class="nav-mobile__btn nav-mobile__btn--panel"
        data-nav-mobile-toggle
        aria-expanded="false"
        aria-controls="nav-mobile-panel"
        aria-label="{{ __('nav.close_menu') }}"
      >
        <span class="nav-mobile__icon" aria-hidden="true"><x-icons.close /></span>
      </button>
    </div>

    <div class="nav-mobile__inner">
      <p class="nav-mobile__tagline">{{ $navTagline }}</p>

      <nav class="nav-mobile__body" aria-label="{{ __('Menu') }}">
        <ul class="nav-mobile__links">
          @foreach ($mobileNavLinks as $link)
            <li>
              <a
                href="{{ route($link['route']) }}"
                class="nav-mobile__link @if ($link['active']) is-active @endif"
                @if ($link['active']) aria-current="page" @endif
              >
                {{ $link['label'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </nav>

      <div class="nav-mobile__bottom">
        <a href="{{ route('shop.index') }}" class="nav-mobile__cta">
          {{ __('Belanja sekarang') }}
          <span class="nav-mobile__cta-icon" aria-hidden="true">↗</span>
        </a>
        <x-theme-toggle class="nav-mobile__theme-btn" />
      </div>
    </div>
  </div>
</header>