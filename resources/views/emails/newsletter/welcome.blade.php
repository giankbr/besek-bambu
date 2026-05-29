<x-mail::message>
# {{ __('Terima kasih sudah berlangganan!') }}

{{ __('Halo, :name, terima kasih sudah mendaftar newsletter', ['name' => $subscriber->displayName()]) }} **{{ store_name() }}**.

{{ __('Kami akan mengirim kabar produk, tips packing hantaran, dan cerita dari pengrajin kami ke email ini.') }}
{{ __('Tanpa spam. Hanya yang relevan untuk Anda.') }}

{{ __('Salam,') }}<br>
{{ store_name() }}
</x-mail::message>
