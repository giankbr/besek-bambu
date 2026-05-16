@extends('layouts.storefront')

@section('title', __('Pesanan').' '.$order->number.' — '.store_name())

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <div class="confirmation">
        <div class="confirmation__check">✓</div>
        <h1 class="confirmation__title">{!! __('Terima kasih, :name', ['name' => '<em>'.e($order->customer_name).'</em>']) !!}</h1>
        <p class="confirmation__lead">{!! __('Pesanan Anda :num telah kami terima.', ['num' => '<strong>'.e($order->number).'</strong>']) !!}</p>
        <p class="confirmation__lead">{!! __('Konfirmasi telah dikirim ke :email.', ['email' => '<strong>'.e($order->customer_email).'</strong>']) !!}</p>

        <div class="confirmation-card">
          <h2 class="confirmation-section-title">{{ __('Detail pesanan') }}</h2>
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
          @if ((float) $order->tax > 0)
            <div class="cart-summary__row">
              <span>{{ $order->tax_inclusive ? __('Termasuk pajak') : __('Pajak') }} ({{ rtrim(rtrim(number_format((float) $order->tax_rate, 2), '0'), '.') }}%)</span>
              <strong>{{ $order->tax_inclusive ? idr($order->tax) : '+ '.idr($order->tax) }}</strong>
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

          <div class="confirmation-status">
            <span class="stock-pill stock-pill--in">{{ __('Pesanan:') }} {{ ucfirst($order->status) }}</span>
            <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">
              {{ __('Pembayaran:') }} {{ ucfirst($order->payment_status) }}
            </span>
            @if ($order->payment_method && $order->payment_method !== 'midtrans')
              <span class="stock-pill stock-pill--in">{{ __('Metode:') }} {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
            @endif
          </div>

          @php
            $bankInfo = setting('payment_bank_info');
          @endphp

          @if ($order->payment_method === 'manual_transfer' && $order->canBePaid() && $bankInfo)
            <h2 class="confirmation-section-title">{{ __('Instruksi transfer bank') }}</h2>
            <p class="confirmation-meta" style="white-space:pre-line">{{ $bankInfo }}</p>
            <p class="confirmation-meta">{!! __('Silakan transfer :total dan balas dengan bukti transfer, sebutkan pesanan :num.', ['total' => '<strong>'.idr($order->total).'</strong>', 'num' => '<strong>'.e($order->number).'</strong>']) !!}</p>
          @endif

          @if ($order->payment_method === 'cod' && $order->canBePaid())
            <h2 class="confirmation-section-title">{{ __('Bayar di tempat (COD)') }}</h2>
            <p class="confirmation-meta">{!! __('Silakan siapkan :total tunai. Kurir kami akan menagih saat pengantaran.', ['total' => '<strong>'.idr($order->total).'</strong>']) !!}</p>
          @endif

          @if ($order->canBePaid() && $order->payment_method === 'midtrans' && setting('payment_midtrans', true) && config('services.midtrans.server_key'))
            <div class="confirmation-actions" style="margin-top:1.25rem">
              <a class="hero-cta" href="{{ route('payment.pay', $order) }}">{{ __('Bayar sekarang') }}</a>
            </div>
          @endif
        </div>

        <div class="confirmation-actions">
          <a class="cart-link-btn" href="{{ route('shop.index') }}">{{ __('Lanjut belanja') }}</a>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
