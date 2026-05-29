<x-mail::message>
# {{ __('Terima kasih sudah berlangganan!') }}

{{ __('Halo, terima kasih sudah mendaftar newsletter :store.', ['store' => store_name()]) }}

{{ __('Kami akan mengirim kabar produk, tips packing hantaran, dan cerita dari pengrajin kami ke email ini. Tanpa spam — hanya yang relevan untuk Anda.') }}

{{ __('Salam,') }}<br>
{{ store_name() }}
</x-mail::message>
