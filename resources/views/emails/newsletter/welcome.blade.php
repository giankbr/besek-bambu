<x-mail::message>
# {{ __('Terima kasih sudah berlangganan!') }}

{{ __('Halo, terima kasih sudah mendaftar newsletter :store.', ['store' => store_name()]) }}

{{ __('Gunakan kode berikut untuk diskon :percent% pada pembelian besek berikutnya:', ['percent' => 10]) }}

@if ($couponCode)
<x-mail::panel>
**{{ $couponCode }}**
</x-mail::panel>
@endif

{{ __('Kode berlaku sekali pakai. Masukkan saat checkout di keranjang belanja.') }}

<x-mail::button :url="$shopUrl">
{{ __('Belanja sekarang') }}
</x-mail::button>

{{ __('Sampai jumpa di inbox — kami akan kirim tips packing hantaran dan promo lainnya.') }}

{{ __('Salam,') }}<br>
{{ store_name() }}
</x-mail::message>
