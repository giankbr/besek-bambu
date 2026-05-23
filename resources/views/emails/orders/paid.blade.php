<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Pembayaran untuk pesanan <strong>:num</strong> (:total) telah kami terima.', ['num' => e($order->number), 'total' => e(idr($order->total))]) !!}

{{ __('Tim kami mulai menyiapkan pesanan Anda.') }}

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
{{ __('Lihat detail pesanan') }}
</x-mail::button>

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
