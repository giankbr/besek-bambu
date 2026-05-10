<header>
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
      <a href="{{ route('shop.index') }}" aria-label="Search">Search</a>
      @auth
        <a href="{{ route('account.index') }}" aria-label="Account">{{ auth()->user()->name }}</a>
      @else
        <a href="{{ route('login') }}" aria-label="Account">Account</a>
      @endauth
      <a href="{{ route('cart.show') }}" aria-label="Cart">Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
    </div>
  </nav>
</header>
