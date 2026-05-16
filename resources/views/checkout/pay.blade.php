@extends('layouts.storefront')

@section('title', meta_title(__('Bayar pesanan').' '.$order->number, store_name()))
@section('meta_robots', 'noindex,follow')

@push('head')
  <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
          data-client-key="{{ $clientKey }}"></script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Keranjang'), 'url' => route('cart.show')],
            ['label' => __('Checkout'), 'url' => route('checkout.show')],
            ['label' => $order->number],
        ]"
        eyebrow="{{ __('Pembayaran') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Selesaikan <em>pembayaran</em> Anda') !!}</h1>
      </x-page-head>

      <div class="checkout-grid payment-page">
        <div class="payment-page__main">
          <div class="payment-panel">
            <p class="payment-panel__lead">
              {!! __('Pesanan :num — total :total', ['num' => '<strong>'.e($order->number).'</strong>', 'total' => '<strong>'.idr($order->total).'</strong>']) !!}
            </p>

            <x-checkout-midtrans-methods />

            <div class="payment-panel__actions">
              <button type="button" id="pay-button" class="hero-cta payment-panel__cta">{{ __('Bayar sekarang') }}</button>
              <a class="cart-link-btn" href="{{ route('checkout.confirmation', $order) }}">{{ __('Lihat detail pesanan') }}</a>
            </div>

            @if (session('status'))
              <p class="form-error payment-panel__error">{{ session('status') }}</p>
            @endif
          </div>
        </div>

        <aside class="cart-summary checkout-summary payment-page__summary">
          <h2 class="cart-summary__title">{{ __('Ringkasan pesanan') }}</h2>
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
          <p class="confirmation-meta payment-page__status">
            <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">
              {{ __('Pembayaran:') }} {{ ucfirst($order->payment_status) }}
            </span>
          </p>
        </aside>
      </div>
    </section>

    <x-site-footer />
  </main>

  @push('scripts')
    <script>
      (function () {
        var btn = document.getElementById('pay-button');
        if (!btn || typeof window.snap === 'undefined') return;

        btn.addEventListener('click', function () {
          window.snap.pay(@json($snapToken), {
            onSuccess: function () { window.location.href = @json(route('checkout.confirmation', $order)); },
            onPending: function () { window.location.href = @json(route('checkout.confirmation', $order)); },
            onError:   function () { window.location.href = @json(route('checkout.confirmation', $order)); },
            onClose:   function () {},
          });
        });
      })();
    </script>
  @endpush
@endsection
