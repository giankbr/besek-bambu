<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

We've received your payment for order **{{ $order->number }}** ({{ idr($order->total) }}).

{{ __('You will receive a separate email when we start processing your order.') }}

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
View order details
</x-mail::button>

Thanks,<br>
{{ store_name() }}
</x-mail::message>
