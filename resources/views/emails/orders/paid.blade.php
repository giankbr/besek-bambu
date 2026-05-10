<x-mail::message>
# Payment received — thank you, {{ $order->customer_name }}!

We've received your payment for order **{{ $order->number }}** ({{ idr($order->total) }}).

Your order is now being prepared for shipment. We'll notify you as soon as it's on the way.

<x-mail::button :url="route('checkout.confirmation', $order)">
View order details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
