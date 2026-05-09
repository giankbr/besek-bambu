@extends('layouts.storefront')

@section('title', 'Cart — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <span class="current">Cart</span>
      </nav>

      <div class="eyebrow">Your selection</div>
      <h1 class="section-title cart-title"><em>Shopping</em> cart</h1>

      @if (session('status'))
        <div class="cart-flash">{{ session('status') }}</div>
      @endif

      @if ($items->isEmpty())
        <div class="cart-empty">
          <p>Your cart is empty.</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">Browse products</a>
        </div>
      @else
        <div class="cart-grid">
          <ul class="cart-items">
            @foreach ($items as $item)
              <li class="cart-item">
                <a class="cart-item__media {{ $item->product->color_class }}" href="{{ route('shop.product', $item->product) }}">
                  @if ($item->product->image_url)
                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" />
                  @else
                    <span class="cart-item__icon">{{ $item->product->icon }}</span>
                  @endif
                </a>
                <div class="cart-item__body">
                  <a class="cart-item__name" href="{{ route('shop.product', $item->product) }}">{{ $item->product->name }}</a>
                  <div class="cart-item__price">${{ number_format($item->product->price, 2) }}</div>

                  <form method="post" action="{{ route('cart.update', $item->product->id) }}" class="cart-item__qty">
                    @csrf
                    @method('PATCH')
                    <label for="qty-{{ $item->product->id }}">Qty</label>
                    <input id="qty-{{ $item->product->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ max(1, $item->product->stock) }}" />
                    <button type="submit" class="cart-link-btn">Update</button>
                  </form>
                </div>

                <div class="cart-item__right">
                  <div class="cart-item__line">${{ number_format($item->line_total, 2) }}</div>
                  <form method="post" action="{{ route('cart.destroy', $item->product->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cart-link-btn cart-link-btn--danger">Remove</button>
                  </form>
                </div>
              </li>
            @endforeach
          </ul>

          <aside class="cart-summary">
            <h2 class="cart-summary__title">Summary</h2>
            <div class="cart-summary__row">
              <span>Subtotal</span>
              <strong>${{ number_format($subtotal, 2) }}</strong>
            </div>
            <div class="cart-summary__row cart-summary__row--muted">
              <span>Shipping</span>
              <span>Calculated at checkout</span>
            </div>
            <div class="cart-summary__total">
              <span>Total</span>
              <strong>${{ number_format($subtotal, 2) }}</strong>
            </div>
            <a class="hero-cta cart-summary__cta" href="#">Proceed to checkout</a>
            <a class="cart-link-btn" href="{{ route('shop.index') }}">Continue shopping</a>
          </aside>
        </div>
      @endif
    </section>

    <x-site-footer />
  </main>
@endsection
