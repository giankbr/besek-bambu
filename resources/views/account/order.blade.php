@extends('layouts.storefront')

@section('title', 'Order ' . $order->number . ' — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('account.index') }}">Account</a>
        <span>/</span>
        <a href="{{ route('account.orders') }}">Orders</a>
        <span>/</span>
        <span class="current">{{ $order->number }}</span>
      </nav>

      <div class="eyebrow">Order detail</div>
      <h1 class="section-title cart-title">Order <em>{{ $order->number }}</em></h1>

      <div class="confirmation-card">
        <div class="confirmation-status">
          <span class="stock-pill stock-pill--in">Order: {{ ucfirst($order->status) }}</span>
          <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">Payment: {{ ucfirst($order->payment_status) }}</span>
          @if ($order->payment_method)
            <span class="stock-pill stock-pill--in">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
          @endif
        </div>

        <h2 class="confirmation-section-title">Items</h2>
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
        @if ($order->notes)
          <p class="confirmation-meta"><em>{{ $order->notes }}</em></p>
        @endif

        @if (session('status'))
          <div style="margin-top:1rem;padding:10px 14px;background:#eef7ee;border-radius:10px;font-size:14px">
            {{ session('status') }}
          </div>
        @endif

        <div class="confirmation-actions" style="margin-top:1.25rem;flex-wrap:wrap">
          @if ($order->canBePaid())
            <a class="hero-cta" href="{{ route('payment.pay', $order) }}">Pay now</a>
          @endif

          @if ($order->status === 'pending' && $order->payment_status !== 'paid')
            <form method="post" action="{{ route('account.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order? Stock will be restored.');">
              @csrf
              <button type="submit" class="cart-link-btn cart-link-btn--danger">Cancel order</button>
            </form>
          @endif
        </div>
        @if ($order->status === 'cancelled')
          <p class="confirmation-meta" style="margin-top:1rem;color:#a33"><em>This order has been cancelled.</em></p>
        @endif

        <h2 class="confirmation-section-title" style="margin-top:1.5rem">Tracking</h2>

        @if ($order->hasTracking())
          <div style="margin:0.5rem 0 1rem;padding:0.75rem 1rem;background:#eef7ee;border:1px solid #c8e6cb;border-radius:0.5rem">
            <p class="confirmation-meta" style="margin:0">
              <strong>{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</strong>
            </p>
            <p class="confirmation-meta" style="margin:0">
              AWB: <code style="font-family:monospace">{{ $order->tracking_number }}</code>
            </p>
            <a href="{{ route('account.orders.track', $order) }}" class="cart-link-btn" style="margin-top:0.5rem;display:inline-block">Track package →</a>
          </div>
        @endif

        <ol class="order-timeline">
          <li class="order-timeline__step {{ in_array($order->status, ['pending','paid','shipped','delivered']) ? 'is-done' : '' }}">
            <strong>Order placed</strong>
            <span>{{ $order->created_at->format('M d, Y · H:i') }}</span>
          </li>
          <li class="order-timeline__step {{ $order->isPaid() ? 'is-done' : '' }}">
            <strong>Payment received</strong>
            <span>{{ $order->paid_at?->format('M d, Y · H:i') ?? '—' }}</span>
          </li>
          <li class="order-timeline__step {{ in_array($order->status, ['shipped','delivered']) ? 'is-done' : '' }}">
            <strong>Shipped</strong>
            <span>{{ $order->shipped_at?->format('M d, Y · H:i') ?? ($order->status === 'shipped' ? 'Your order is on the way' : '—') }}</span>
          </li>
          <li class="order-timeline__step {{ $order->status === 'delivered' ? 'is-done' : '' }}">
            <strong>Delivered</strong>
            <span>{{ $order->delivered_at?->format('M d, Y · H:i') ?? ($order->status === 'delivered' ? 'Enjoy your purchase!' : '—') }}</span>
          </li>
        </ol>
      </div>

      <div class="confirmation-actions">
        <a class="cart-link-btn" href="{{ route('account.orders') }}">← Back to orders</a>
        <a class="cart-link-btn" href="{{ route('account.orders.invoice', $order) }}" target="_blank" rel="noopener">Download invoice</a>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
