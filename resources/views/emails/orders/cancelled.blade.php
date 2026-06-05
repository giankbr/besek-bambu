<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Pesanan <strong>:number</strong> telah dibatalkan.', ['number' => e($order->number)]) !!}

@if ($order->payment_status === 'paid')
{{ __('Karena pesanan ini sudah dibayar, tim kami akan menghubungi Anda terkait pengembalian dana. Jika belum ada kabar dalam 2 hari kerja, balas email ini.') }}
@elseif ($order->payment_status === 'pending')
{{ __('Belum ada pembayaran yang masuk, jadi tidak ada tindakan lanjutan dari Anda.') }}
@endif

{{ __('Jika ini kesalahan atau ingin memesan lagi, kami siap membantu.') }}

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
