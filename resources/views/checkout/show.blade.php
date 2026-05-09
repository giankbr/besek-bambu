@extends('layouts.storefront')

@section('title', 'Checkout — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('cart.show') }}">Cart</a>
        <span>/</span>
        <span class="current">Checkout</span>
      </nav>

      <div class="eyebrow">Almost there</div>
      <h1 class="section-title cart-title"><em>Checkout</em></h1>

      <form method="post" action="{{ route('checkout.store') }}" class="checkout-grid">
        @csrf
        <div class="checkout-form">
          <h2 class="checkout-section-title">Contact</h2>
          <div class="checkout-row">
            <label>
              Name
              <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required />
              @error('customer_name')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <label>
              Email
              <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required />
              @error('customer_email')<span class="form-error">{{ $message }}</span>@enderror
            </label>
          </div>
          <label>
            Phone
            <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required />
            @error('customer_phone')<span class="form-error">{{ $message }}</span>@enderror
          </label>

          <h2 class="checkout-section-title">Shipping</h2>
          <label>
            Address
            <textarea name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
            @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
          </label>
          <label>
            Notes (optional)
            <textarea name="notes" rows="2" placeholder="Special instructions...">{{ old('notes') }}</textarea>
          </label>
        </div>

        <aside class="cart-summary checkout-summary">
          <h2 class="cart-summary__title">Order summary</h2>
          <ul class="checkout-items">
            @foreach ($items as $item)
              <li>
                <span class="checkout-item__name">{{ $item->product->icon }} {{ $item->product->name }} <small>× {{ $item->quantity }}</small></span>
                <span>${{ number_format($item->line_total, 2) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="cart-summary__row">
            <span>Subtotal</span>
            <strong>${{ number_format($subtotal, 2) }}</strong>
          </div>
          <div class="cart-summary__row cart-summary__row--muted">
            <span>Shipping</span>
            <span>Free trial</span>
          </div>
          <div class="cart-summary__total">
            <span>Total</span>
            <strong>${{ number_format($subtotal, 2) }}</strong>
          </div>

          <button type="submit" class="hero-cta cart-summary__cta">Place order</button>
          <a class="cart-link-btn" href="{{ route('cart.show') }}">Back to cart</a>
        </aside>
      </form>
    </section>

    <x-site-footer />
  </main>
@endsection
