<x-mail::message>
# @if ($reason === 'expired')
Payment window expired
@else
Payment could not be completed
@endif

Hi {{ $order->customer_name }},

@if ($reason === 'expired')
The payment window for order **{{ $order->number }}** ({{ idr($order->total) }}) has expired. Your order is not confirmed yet.
@else
We could not complete payment for order **{{ $order->number }}** ({{ idr($order->total) }}). Your order is not confirmed yet.
@endif

@if ($order->canBePaid())
You can try paying again using the link below.

<x-mail::button :url="order_signed_url('payment.pay', $order)">
Pay again
</x-mail::button>
@else
If you'd like to order again, visit our shop or reply to this email and we'll help you out.
@endif

Thanks,<br>
{{ store_name() }}
</x-mail::message>
