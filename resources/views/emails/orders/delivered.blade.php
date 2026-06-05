<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Pesanan <strong>:number</strong> telah sampai di tujuan. Semoga Anda puas!', ['number' => e($order->number)]) !!}

{{ __('Jika ada yang kurang sesuai, balas email ini dan kami akan bantu.') }}

{{ __('Boleh cerita pengalaman Anda? Ulasan membantu pembeli lain menemukan kami.') }}

@if (config('app.url'))
<x-mail::button :url="config('app.url').'/account/orders'">
{{ __('Lihat pesanan') }}
</x-mail::button>
@endif

{{ __('Terima kasih telah memilih kami,') }}<br>
{{ store_name() }}
</x-mail::message>
