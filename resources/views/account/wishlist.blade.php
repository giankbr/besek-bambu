@extends('layouts.storefront')

@section('title', 'My wishlist — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('account.index') }}">Account</a>
        <span>/</span>
        <span class="current">Wishlist</span>
      </nav>

      <div class="eyebrow">Saved for later</div>
      <h1 class="section-title cart-title">My <em>wishlist</em></h1>

      <div class="account-grid">
        <aside class="account-side">
          <ul class="account-nav">
            <li><a class="account-nav__item" href="{{ route('account.index') }}">Overview</a></li>
            <li><a class="account-nav__item" href="{{ route('account.orders') }}">My orders</a></li>
            <li><a class="account-nav__item account-nav__item--active" href="{{ route('account.wishlist') }}">Wishlist</a></li>
            <li><a class="account-nav__item" href="{{ route('profile.edit') }}">Profile settings</a></li>
            <li>
              <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="account-nav__item account-nav__item--button">Sign out</button>
              </form>
            </li>
          </ul>
        </aside>

        <div class="account-main">
          @if (session('status'))
            <div class="confirmation-card" style="margin-bottom:1rem;background:#eef7ee">
              <p class="confirmation-meta" style="margin:0">{{ session('status') }}</p>
            </div>
          @endif

          @if ($products->isEmpty())
            <div class="confirmation-card">
              <p class="confirmation-meta">Your wishlist is empty.</p>
              <a class="hero-cta" href="{{ route('shop.index') }}">Browse products</a>
            </div>
          @else
            <div class="grid-4">
              @foreach ($products as $product)
                <div class="wishlist-cell">
                  <x-product-card :product="$product" />
                  <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
                    @if ($product->stock > 0)
                      <form method="post" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}" />
                        <input type="hidden" name="quantity" value="1" />
                        <button type="submit" class="cart-link-btn">Add to cart</button>
                      </form>
                    @else
                      <span class="cart-link-btn" style="opacity:.6;cursor:not-allowed">Sold out</span>
                    @endif
                    <form method="post" action="{{ route('wishlist.toggle', $product) }}">
                      @csrf
                      <button type="submit" class="cart-link-btn cart-link-btn--danger">Remove</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
