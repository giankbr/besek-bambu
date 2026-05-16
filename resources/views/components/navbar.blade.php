<header class="site-header">
  <div class="container">
    <nav class="navbar">
      @php
        $brandLogo = store_logo_url();
        $brandName = store_name();
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
      @endphp

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
        <li>
          <a
            href="{{ route('shop.index') }}"
            class="@if ($isShop) is-active @endif"
            @if ($isShop) aria-current="page" @endif
          >{{ __('nav.shop') }}</a>
        </li>
        <li>
          <a
            href="{{ route('gallery') }}"
            class="@if ($isGallery) is-active @endif"
            @if ($isGallery) aria-current="page" @endif
          >{{ __('nav.gallery') }}</a>
        </li>
        <li>
          <a
            href="{{ route('about') }}"
            class="@if ($isAbout) is-active @endif"
            @if ($isAbout) aria-current="page" @endif
          >{{ __('nav.about') }}</a>
        </li>
        <li>
          <a
            href="{{ route('contact') }}"
            class="@if ($isContact) is-active @endif"
            @if ($isContact) aria-current="page" @endif
          >{{ __('nav.contact') }}</a>
        </li>
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
          @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
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
          @php
            $locales = ['id' => 'Indonesia', 'en' => 'English'];
            $activeLocale = app()->getLocale();
          @endphp
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

  <div id="nav-mobile-panel" class="nav-mobile__panel" data-nav-mobile-panel hidden>
    <div class="nav-mobile__head">
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
    <nav class="nav-mobile__inner" aria-label="{{ __('Menu') }}">
      <ul class="nav-mobile__links">
        <li>
          <a href="{{ route('shop.index') }}" class="@if ($isShop) is-active @endif" @if ($isShop) aria-current="page" @endif>{{ __('nav.shop') }}</a>
        </li>
        <li>
          <a href="{{ route('gallery') }}" class="@if ($isGallery) is-active @endif" @if ($isGallery) aria-current="page" @endif>{{ __('nav.gallery') }}</a>
        </li>
        <li>
          <a href="{{ route('about') }}" class="@if ($isAbout) is-active @endif" @if ($isAbout) aria-current="page" @endif>{{ __('nav.about') }}</a>
        </li>
        <li>
          <a href="{{ route('contact') }}" class="@if ($isContact) is-active @endif" @if ($isContact) aria-current="page" @endif>{{ __('nav.contact') }}</a>
        </li>
      </ul>
    </nav>
  </div>
</header>
