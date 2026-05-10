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
                <span>{{ idr($item->line_total) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="cart-summary__row">
            <span>Subtotal</span>
            <strong>{{ idr($order->subtotal) }}</strong>
          </div>
          @if ((float) $order->discount > 0)
            <div class="cart-summary__row" style="color:#1f7a3a">
              <span>Discount{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</span>
              <strong>− {{ idr($order->discount) }}</strong>
            </div>
          @endif
          @if ((float) $order->shipping_cost > 0)
            <div class="cart-summary__row">
              <span>Shipping</span>
              <strong>{{ idr($order->shipping_cost) }}</strong>
            </div>
          @endif
          <div class="cart-summary__total">
            <span>Total</span>
            <strong>{{ idr($order->total) }}</strong>
          </div>

          <h2 class="confirmation-section-title">Shipping to</h2>
          <p class="confirmation-meta">{{ $order->customer_name }}</p>
          <p class="confirmation-meta">{{ $order->customer_phone }}</p>
          <p class="confirmation-meta">{{ $order->shipping_address }}</p>

          <div class="confirmation-status">
            <span class="stock-pill stock-pill--in">Order: {{ ucfirst($order->status) }}</span>
            <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">
              Payment: {{ ucfirst($order->payment_status) }}
            </span>
            @if ($order->payment_method)
              <span class="stock-pill stock-pill--in">Method: {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
            @endif
          </div>

          @if ($order->canBePaid())
            <div class="confirmation-actions" style="margin-top:1.25rem">
              <a class="hero-cta" href="{{ route('payment.pay', $order) }}">Pay now</a>
            </div>
          @endif
        </div>

        <div class="confirmation-actions">
          <a class="cart-link-btn" href="{{ route('shop.index') }}">Continue shopping</a>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
