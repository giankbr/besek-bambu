<x-mail::message>
# Refund processed

Hi {{ $order->customer_name }},

We've processed a refund for order **{{ $order->number }}** ({{ idr($order->total) }}).

Depending on your bank or payment provider, funds may take a few business days to appear in your account.

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
View order details
</x-mail::button>

If you have questions, reply to this email.

Thanks,<br>
{{ store_name() }}
</x-mail::message>
