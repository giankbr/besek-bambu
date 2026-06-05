@php
  $logos = midtrans_payment_logos();
@endphp

<div class="checkout-midtrans" role="group" aria-label="{{ __('Metode pembayaran online') }}">
  <p class="checkout-midtrans__lead">{{ __('Kartu, transfer bank, e-wallet, dan QRIS tersedia saat pembayaran.') }}</p>
  <ul class="checkout-midtrans__channels">
    @foreach ($logos as $logo)
      <li class="checkout-midtrans__chip">
        <img
          src="{{ asset('images/payments/'.$logo['file']) }}"
          alt="{{ $logo['alt'] }}"
          class="checkout-midtrans__logo"
          width="80"
          height="32"
          loading="lazy"
          decoding="async"
        />
      </li>
    @endforeach
  </ul>
</div>
