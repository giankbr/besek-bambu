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
        $isWishlist = request()->routeIs('account.wishlist');
        $isCartFlow = request()->routeIs('cart.*', 'checkout.*', 'payment.pay');
      @endphp
      <ul class="nav-links">
        <li>
          <a
            href="{{ route('shop.index') }}"
            class="@if ($isShop) is-active @endif"
            @if ($isShop) aria-current="page" @endif
          >Shop</a>
        </li>
        <li>
          <a
            href="{{ route('gallery') }}"
            class="@if ($isGallery) is-active @endif"
            @if ($isGallery) aria-current="page" @endif
          >Gallery</a>
        </li>
        <li>
          <a
            href="{{ route('about') }}"
            class="@if ($isAbout) is-active @endif"
            @if ($isAbout) aria-current="page" @endif
          >About</a>
        </li>
        <li>
          <a
            href="{{ route('contact') }}"
            class="@if ($isContact) is-active @endif"
            @if ($isContact) aria-current="page" @endif
          >Contact</a>
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
        <form
          method="get"
          action="{{ route('shop.index') }}"
          role="search"
          class="navbar-search"
          aria-label="Search products"
        >
          <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search products…"
            aria-label="Search products"
            autocomplete="off"
          />
          <button type="submit" aria-label="Submit search">⌕</button>
        </form>
        <div class="nav-actions__cluster">
          @auth
            @php
              // Cache 60s per user — wishlist count is fine to be slightly
              // stale and this avoids a DB roundtrip on every page render.
              $wishCount = \Illuminate\Support\Facades\Cache::remember(
                'nav.wish.'.auth()->id(),
                60,
                fn () => \Illuminate\Support\Facades\DB::table('wishlist_items')->where('user_id', auth()->id())->count(),
              );
            @endphp
            <a
              href="{{ route('account.wishlist') }}"
              aria-label="Wishlist"
              title="Wishlist"
              class="@if ($isWishlist) is-active @endif"
              @if ($isWishlist) aria-current="page" @endif
            >♥ {{ $wishCount }}</a>
            <a
              href="{{ route('account.index') }}"
              aria-label="Account"
              class="@if ($isAccountArea && ! $isWishlist) is-active @endif"
              @if ($isAccountArea && ! $isWishlist) aria-current="page" @endif
            >{{ auth()->user()->name }}</a>
          @else
            <a
              href="{{ route('login') }}"
              aria-label="Account"
              class="@if ($isAccountArea) is-active @endif"
              @if ($isAccountArea) aria-current="page" @endif
            >Account</a>
          @endauth
          <a
            href="{{ route('cart.show') }}"
            aria-label="Cart"
            class="@if ($isCartFlow) is-active @endif"
            @if ($isCartFlow) aria-current="page" @endif
          >Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
        </div>
      </div>
    </nav>
  </div>
</header>
