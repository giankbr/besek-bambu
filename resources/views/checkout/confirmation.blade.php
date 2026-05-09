@extends('layouts.storefront')

@section('title', 'Order ' . $order->number . ' — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <div class="confirmation">
        <div class="confirmation__check">✓</div>
        <h1 class="confirmation__title">Thank you, <em>{{ $order->customer_name }}</em></h1>
        <p class="confirmation__lead">Your order <strong>{{ $order->number }}</strong> has been received.</p>
        <p class="confirmation__lead">A confirmation has been sent to <strong>{{ $order->customer_email }}</strong>.</p>

        <div class="confirmation-card">
          <h2 class="confirmation-section-title">Order details</h2>
          <ul class="checkout-items">
            @foreach ($order->items as $item)
              <li>
                <span class="checkout-item__name">{{ $item->product_icon }} {{ $item->product_name }} <small>× {{ $item->quantity }}</small></span>
                <span>${{ number_format($item->line_total, 2) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="cart-summary__total">
            <span>Total</span>
            <strong>${{ number_format($order->total, 2) }}</strong>
          </div>

          <h2 class="confirmation-section-title">Shipping to</h2>
          <p class="confirmation-meta">{{ $order->customer_name }}</p>
          <p class="confirmation-meta">{{ $order->customer_phone }}</p>
          <p class="confirmation-meta">{{ $order->shipping_address }}</p>

          <div class="confirmation-status">
            <span class="stock-pill stock-pill--in">Status: {{ ucfirst($order->status) }}</span>
          </div>
        </div>

        <div class="confirmation-actions">
          <a class="hero-cta" href="{{ route('shop.index') }}">Continue shopping</a>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
