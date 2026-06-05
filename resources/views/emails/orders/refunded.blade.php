<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Kami telah memproses pengembalian dana untuk pesanan <strong>:number</strong> (:total).', ['number' => e($order->number), 'total' => e(idr($order->total))]) !!}

{{ __('Dana dapat membutuhkan beberapa hari kerja untuk muncul di rekening Anda, tergantung bank atau penyedia pembayaran.') }}

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
{{ __('Lihat detail pesanan') }}
</x-mail::button>

{{ __('Jika ada pertanyaan, balas email ini.') }}

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
