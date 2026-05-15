@extends('layouts.storefront')

@section('title', __('Pesanan').' '.$order->number.' — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Akun'), 'url' => route('account.index')],
            ['label' => __('Pesanan'), 'url' => route('account.orders')],
            ['label' => $order->number],
        ]"
        eyebrow="{{ __('Detail pesanan') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Pesanan :num', ['num' => '<em>'.e($order->number).'</em>']) !!}</h1>
      </x-page-head>

      <div class="confirmation-card">
        <div class="confirmation-status">
          <span class="stock-pill stock-pill--in">{{ __('Pesanan:') }} {{ ucfirst($order->status) }}</span>
          <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">{{ __('Pembayaran:') }} {{ ucfirst($order->payment_status) }}</span>
          @if ($order->payment_method)
            <span class="stock-pill stock-pill--in">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
          @endif
        </div>

        <h2 class="confirmation-section-title">{{ __('Item') }}</h2>
        <ul class="checkout-items">
          @foreach ($order->items as $item)
            <li>
              <span class="checkout-item__name">{{ $item->product_icon }} {{ $item->product_name }} <small>× {{ $item->quantity }}</small></span>
              <span>{{ idr($item->line_total) }}</span>
            </li>
          @endforeach
        </ul>
        <div class="cart-summary__row">
          <span>{{ __('Subtotal') }}</span>
          <strong>{{ idr($order->subtotal) }}</strong>
        </div>
        @if ((float) $order->discount > 0)
          <div class="cart-summary__row" style="color:#1f7a3a">
            <span>{{ __('Diskon') }}{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</span>
            <strong>− {{ idr($order->discount) }}</strong>
          </div>
        @endif
        @if ((float) $order->shipping_cost > 0)
          <div class="cart-summary__row">
            <span>{{ __('Pengiriman') }}</span>
            <strong>{{ idr($order->shipping_cost) }}</strong>
          </div>
        @endif
        <div class="cart-summary__total">
          <span>{{ __('Total') }}</span>
          <strong>{{ idr($order->total) }}</strong>
        </div>

        <h2 class="confirmation-section-title">{{ __('Dikirim ke') }}</h2>
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
            <a class="hero-cta" href="{{ route('payment.pay', $order) }}">{{ __('Bayar sekarang') }}</a>
          @endif

          @if ($order->status === 'pending' && $order->payment_status !== 'paid')
            <form method="post" action="{{ route('account.orders.cancel', $order) }}" onsubmit="return confirm(@js(__('Batalkan pesanan ini? Stok akan dikembalikan.')));">
              @csrf
              <button type="submit" class="cart-link-btn cart-link-btn--danger">{{ __('Batalkan pesanan') }}</button>
            </form>
          @endif
        </div>
        @if ($order->status === 'cancelled')
          <p class="confirmation-meta" style="margin-top:1rem;color:#a33"><em>{{ __('Pesanan ini telah dibatalkan.') }}</em></p>
        @endif

        <h2 class="confirmation-section-title" style="margin-top:1.5rem">{{ __('Pelacakan') }}</h2>

        @if ($order->hasTracking())
          <div style="margin:0.5rem 0 1rem;padding:0.75rem 1rem;background:#eef7ee;border:1px solid #c8e6cb;border-radius:0.5rem">
            <p class="confirmation-meta" style="margin:0">
              <strong>{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</strong>
            </p>
            <p class="confirmation-meta" style="margin:0">
              AWB: <code style="font-family:monospace">{{ $order->tracking_number }}</code>
            </p>
            <a href="{{ route('account.orders.track', $order) }}" class="cart-link-btn" style="margin-top:0.5rem;display:inline-block">{{ __('Lacak paket') }} →</a>
          </div>
        @endif

        <ol class="order-timeline">
          <li class="order-timeline__step {{ in_array($order->status, ['pending','paid','shipped','delivered']) ? 'is-done' : '' }}">
            <strong>{{ __('Pesanan dibuat') }}</strong>
            <span>{{ $order->created_at->format('M d, Y · H:i') }}</span>
          </li>
          <li class="order-timeline__step {{ $order->isPaid() ? 'is-done' : '' }}">
            <strong>{{ __('Pembayaran diterima') }}</strong>
            <span>{{ $order->paid_at?->format('M d, Y · H:i') ?? '—' }}</span>
          </li>
          <li class="order-timeline__step {{ in_array($order->status, ['shipped','delivered']) ? 'is-done' : '' }}">
            <strong>{{ __('Dikirim') }}</strong>
            <span>{{ $order->shipped_at?->format('M d, Y · H:i') ?? ($order->status === 'shipped' ? __('Pesanan Anda sedang dalam perjalanan') : '—') }}</span>
          </li>
          <li class="order-timeline__step {{ $order->status === 'delivered' ? 'is-done' : '' }}">
            <strong>{{ __('Terkirim') }}</strong>
            <span>{{ $order->delivered_at?->format('M d, Y · H:i') ?? ($order->status === 'delivered' ? __('Selamat menikmati pembelian Anda!') : '—') }}</span>
          </li>
        </ol>
      </div>

      <div class="confirmation-actions">
        <a class="cart-link-btn" href="{{ route('account.orders') }}">← {{ __('Kembali ke pesanan') }}</a>
        <a class="cart-link-btn" href="{{ route('account.orders.invoice', $order) }}" target="_blank" rel="noopener">{{ __('Unduh invoice') }}</a>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
