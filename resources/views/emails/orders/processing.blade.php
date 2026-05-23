<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{{ __('Pesanan :number sedang kami siapkan.', ['number' => $order->number]) }}

{{ __('Tim kami sedang menyiapkan barang Anda. Kami akan memberi tahu saat paket diserahkan ke kurir.') }}

**{{ __('Total pesanan') }}:** {{ idr($order->total) }}

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
{{ __('Lihat detail pesanan') }}
</x-mail::button>

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
