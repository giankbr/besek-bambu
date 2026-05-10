<x-mail::message>
# Your order was cancelled

Hi {{ $order->customer_name }},

Order **{{ $order->number }}** has been cancelled.

@if ($order->payment_status === 'paid')
Since this order was already paid, our team will reach out shortly with refund details. If you don't hear back within 2 business days, please reply to this email.
@elseif ($order->payment_status === 'pending')
No payment had been settled yet, so nothing further is needed from you.
@endif

If this was a mistake or you'd like to place the order again, we're here to help.

Best,<br>
{{ store_name() }}
</x-mail::message>
