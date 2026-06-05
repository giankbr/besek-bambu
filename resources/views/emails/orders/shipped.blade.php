<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{!! __('Kabar baik! Pesanan <strong>:number</strong> telah diserahkan ke kurir dan sedang dalam perjalanan menuju Anda.', ['number' => e($order->number)]) !!}

@if ($order->shipping_courier)
**{{ __('Kurir') }}:** {{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}
@endif

@if ($order->tracking_number)
**{{ __('Nomor resi') }}:** `{{ $order->tracking_number }}`
@endif

@if ($order->shipping_etd)
**{{ __('Estimasi tiba') }}:** {{ str_ireplace(['day', 'days'], __('hari'), $order->shipping_etd) }}
@endif

<x-mail::table>
| {{ __('Produk') }} | {{ __('Jumlah') }} | {{ __('Harga') }} |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

**{{ __('Dikirim ke') }}:**
{{ $order->shipping_address }}

{{ __('Kami akan memberi tahu setelah paket sampai. Terima kasih sudah berbelanja!') }}

{{ __('Terima kasih,') }}<br>
{{ store_name() }}
</x-mail::message>
