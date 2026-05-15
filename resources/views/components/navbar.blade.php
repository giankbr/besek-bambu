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
          <a
            href="{{ route('cart.show') }}"
            class="nav-actions__link @if ($isCartFlow) is-active @endif"
            @if ($isCartFlow) aria-current="page" @endif
          >
            <x-icons.cart class="nav-actions__icon" />
            <span class="nav-actions__label">{{ __('nav.cart') }} ({{ app(\App\Services\CartService::class)->count() }})</span>
          </a>
          <div class="nav-lang" role="group" aria-label="{{ __('nav.language') }}">
            <a
              href="{{ route('locale.switch', 'id') }}"
              class="@if (app()->getLocale() === 'id') is-active @endif"
              @if (app()->getLocale() === 'id') aria-current="true" @endif
            >ID</a>
            <span aria-hidden="true">·</span>
            <a
              href="{{ route('locale.switch', 'en') }}"
              class="@if (app()->getLocale() === 'en') is-active @endif"
              @if (app()->getLocale() === 'en') aria-current="true" @endif
            >EN</a>
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>
