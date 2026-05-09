<header>
  <nav class="navbar">
    <ul class="nav-links">
      <li><a href="{{ route('shop.index') }}">Shop</a></li>
      <li><a href="{{ route('home') }}#categories">Collections</a></li>
      <li><a href="{{ route('home') }}#story">Sustainability</a></li>
      <li><a href="#">Journal</a></li>
    </ul>
    <a class="logo" href="{{ route('home') }}">besek</a>
    <div class="nav-actions">
      <a href="{{ route('shop.index') }}" aria-label="Search">Search</a>
      @auth
        <a href="{{ route('dashboard') }}" aria-label="Account">{{ auth()->user()->name }}</a>
      @else
        <a href="{{ route('login') }}" aria-label="Account">Account</a>
      @endauth
      <a href="#" aria-label="Cart">Cart (0)</a>
    </div>
  </nav>
</header>
