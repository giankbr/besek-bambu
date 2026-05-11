<header class="site-header">
  <div class="container">
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="{{ route('shop.index') }}">Shop</a></li>
        <li><a href="{{ route('gallery') }}">Gallery</a></li>
        <li><a href="{{ route('about') }}">About</a></li>
        <li><a href="{{ route('contact') }}">Contact</a></li>
      </ul>
      @php
        $brandLogo = store_logo_url();
        $brandName = store_name();
      @endphp
      <a class="logo" href="{{ route('home') }}" aria-label="{{ $brandName }}">
        @if ($brandLogo)
          <img src="{{ $brandLogo }}" alt="{{ $brandName }}" style="max-height:32px;width:auto;display:block" />
        @else
          besek
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
            <a href="{{ route('account.wishlist') }}" aria-label="Wishlist" title="Wishlist">♥ {{ $wishCount }}</a>
            <a href="{{ route('account.index') }}" aria-label="Account">{{ auth()->user()->name }}</a>
          @else
            <a href="{{ route('login') }}" aria-label="Account">Account</a>
          @endauth
          <a href="{{ route('cart.show') }}" aria-label="Cart">Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
        </div>
      </div>
    </nav>
  </div>
</header>
