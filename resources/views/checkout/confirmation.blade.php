@extends('layouts.storefront')

@section('title', meta_title(__('Pesanan').' '.$order->number, store_name()))

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
                <span class="checkout-item__name">{{ $item->product_name }} <small>× {{ $item->quantity }}</small></span>
                <span>{{ idr($item->line_total) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="cart-summary__row">
            <span>{{ __('Subtotal') }}</span>
            <strong>{{ idr($order->subtotal) }}</strong>
          </div>
          @if ((float) $order->discount > 0)
            <div class="cart-summary__row cart-summary__row--discount">
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

          <x-order-status-summary :order="$order" hide-midtrans-method />

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

        </div>

        @if (session('status'))
          <p class="form-error confirmation__notice">{{ session('status') }}</p>
        @endif

        <div class="confirmation-actions">
          @if (order_can_pay_with_midtrans($order))
            <a class="hero-cta" href="{{ route('payment.pay', $order) }}">{{ __('Bayar sekarang') }}</a>
          @endif
          @if ($waUrl = whatsapp_order_url($order))
            <a
              class="sf-btn sf-btn--wa sf-btn--md"
              href="{{ $waUrl }}"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2C6.55 2 2.07 6.48 2.07 11.97c0 1.76.46 3.45 1.32 4.96L2 22l5.25-1.38a9.93 9.93 0 0 0 4.78 1.22h.01c5.49 0 9.97-4.48 9.97-9.97 0-2.66-1.04-5.16-2.96-7.06ZM12.04 20.16h-.01a8.16 8.16 0 0 1-4.16-1.14l-.3-.18-3.12.82.83-3.04-.19-.31a8.18 8.18 0 0 1-1.25-4.34c0-4.53 3.69-8.21 8.22-8.21 2.19 0 4.25.85 5.8 2.4a8.15 8.15 0 0 1 2.41 5.81c0 4.54-3.69 8.21-8.23 8.21Zm4.5-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.56.12s-.65.8-.8.97c-.15.17-.29.18-.54.06-.25-.12-1.04-.38-1.98-1.22a7.4 7.4 0 0 1-1.36-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.48c-.17 0-.43.06-.65.31-.22.25-.85.83-.85 2.03 0 1.2.87 2.36.99 2.52.12.17 1.71 2.61 4.15 3.66.58.25 1.03.4 1.39.51.58.18 1.11.16 1.53.1.47-.07 1.46-.6 1.66-1.17.21-.57.21-1.06.14-1.16-.06-.1-.22-.16-.47-.28Z"/></svg>
              {{ __('Kabari admin via WhatsApp') }}
            </a>
          @endif
          <a class="sf-btn sf-btn--tertiary sf-btn--md" href="{{ route('shop.index') }}">{{ __('Lanjut belanja') }}</a>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
