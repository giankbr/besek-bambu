<x-mail::message>
# Your order is on its way

Hi {{ $order->customer_name }},

Good news — order **{{ $order->number }}** has been handed over to the courier and is on its way to you.

@if ($order->shipping_courier)
**Courier:** {{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}
@endif

@if ($order->tracking_number)
**Tracking number:** `{{ $order->tracking_number }}`
@endif

@if ($order->shipping_etd)
**Estimated delivery:** {{ $order->shipping_etd }} {{ Str::contains($order->shipping_etd, 'day') ? '' : 'days' }}
@endif

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_icon }} {{ $item->product_name }} | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

**Shipping to:**
{{ $order->shipping_address }}

We'll let you know once it has been delivered. Thanks for shopping with us!

Best,<br>
{{ store_name() }}
</x-mail::message>
