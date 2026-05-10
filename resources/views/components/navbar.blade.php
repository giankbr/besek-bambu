<header>
  <nav class="navbar">
    <ul class="nav-links">
      <li><a href="{{ route('shop.index') }}">Shop</a></li>
      <li><a href="{{ route('gallery') }}">Gallery</a></li>
      <li><a href="{{ route('about') }}">About</a></li>
      <li><a href="{{ route('contact') }}">Contact</a></li>
    </ul>
    <a class="logo" href="{{ route('home') }}">besek</a>
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
