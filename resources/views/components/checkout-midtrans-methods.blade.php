@php
  $logos = [
      ['file' => 'visa.svg', 'alt' => 'Visa'],
      ['file' => 'mastercard.svg', 'alt' => 'Mastercard'],
      ['file' => 'jcb.svg', 'alt' => 'JCB'],
      ['file' => 'amex.svg', 'alt' => 'American Express'],
      ['file' => 'bca.svg', 'alt' => 'BCA'],
      ['file' => 'bni.svg', 'alt' => 'BNI'],
      ['file' => 'mandiri.svg', 'alt' => 'Bank Mandiri'],
      ['file' => 'permata.svg', 'alt' => 'PermataBank'],
      ['file' => 'cimb.svg', 'alt' => 'CIMB Niaga'],
      ['file' => 'bsi.svg', 'alt' => 'BSI'],
      ['file' => 'gopay.svg', 'alt' => 'GoPay'],
      ['file' => 'shopee.svg', 'alt' => 'ShopeePay'],
      ['file' => 'qris.svg', 'alt' => 'QRIS'],
      ['file' => 'dana.svg', 'alt' => 'DANA'],
  ];
@endphp

<div class="checkout-midtrans" role="group" aria-label="{{ __('Metode pembayaran Midtrans') }}">
  <ul class="checkout-midtrans__channels">
    @foreach ($logos as $logo)
      <li class="checkout-midtrans__chip">
        <img
          src="{{ asset('images/payments/'.$logo['file']) }}"
          alt="{{ $logo['alt'] }}"
          class="checkout-midtrans__logo"
          width="72"
          height="32"
          loading="lazy"
          decoding="async"
        />
      </li>
    @endforeach
  </ul>
</div>
