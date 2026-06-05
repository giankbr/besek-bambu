<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

@if ($reason === 'expired')
{!! __('Batas waktu pembayaran untuk pesanan <strong>:number</strong> (:total) telah berakhir. Pesanan belum dikonfirmasi.', ['number' => e($order->number), 'total' => e(idr($order->total))]) !!}
@else
{!! __('Pembayaran untuk pesanan <strong>:number</strong> (:total) tidak dapat diselesaikan. Pesanan belum dikonfirmasi.', ['number' => e($order->number), 'total' => e(idr($order->total))]) !!}
@endif

@if ($order->canBePaid())
{{ __('Anda dapat mencoba membayar lagi melalui tautan di bawah.') }}

<x-mail::button :url="order_signed_url('payment.pay', $order)">
{{ __('Bayar lagi') }}
</x-mail::button>
@else
{{ __('Jika ingin memesan ulang, kunjungi toko kami atau balas email ini.') }}
@endif

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
