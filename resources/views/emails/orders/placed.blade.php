<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Pesanan <strong>:number</strong> telah kami terima. Ringkasannya:', ['number' => e($order->number)]) !!}

<x-mail::table>
| {{ __('Produk') }} | {{ __('Jumlah') }} | {{ __('Harga') }} |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

**{{ __('Total') }}: {{ idr($order->total) }}**

@if ($order->canBePaid())
{{ __('Silakan selesaikan pembayaran untuk mengonfirmasi pesanan.') }}

<x-mail::button :url="order_signed_url('payment.pay', $order)">
{{ __('Bayar sekarang') }}
</x-mail::button>
@endif

{{ __('Kami akan mengirim email lagi begitu pesanan dikirim.') }}

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
