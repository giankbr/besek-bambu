@extends('layouts.storefront')

@section('title', 'Pay order ' . $order->number . ' — '.store_name())

@push('head')
  <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
          data-client-key="{{ $clientKey }}"></script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <div class="confirmation">
        <h1 class="confirmation__title">Complete your <em>payment</em></h1>
        <p class="confirmation__lead">Order <strong>{{ $order->number }}</strong> — total <strong>{{ idr($order->total) }}</strong></p>

        <div class="confirmation-card">
          <p class="confirmation-meta">A secure payment window will open. You can pay using credit card, bank transfer, e-wallet, or QRIS.</p>

          <div class="confirmation-actions">
            <button type="button" id="pay-button" class="hero-cta">Pay now</button>
            <a class="cart-link-btn" href="{{ route('checkout.confirmation', $order) }}">View order details</a>
          </div>

          @if (session('status'))
            <p class="form-error" style="margin-top:1rem">{{ session('status') }}</p>
          @endif
        </div>
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
            onClose:   function () { /* user closed popup */ },
          });
        });
      })();
    </script>
  @endpush
@endsection
