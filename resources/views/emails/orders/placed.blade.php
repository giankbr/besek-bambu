<x-mail::message>
# Thanks for your order, {{ $order->customer_name }}!

We've received your order **{{ $order->number }}**. Here's a summary:

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_icon }} {{ $item->product_name }} | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

**Total: {{ idr($order->total) }}**

@if ($order->canBePaid())
Please complete your payment to confirm the order.

<x-mail::button :url="order_signed_url('payment.pay', $order)">
Pay now
</x-mail::button>
@endif

We'll email you again as soon as we ship your order.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
