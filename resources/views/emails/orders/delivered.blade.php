<x-mail::message>
# Your order has been delivered

Hi {{ $order->customer_name }},

Order **{{ $order->number }}** was marked as delivered. We hope you love it!

If anything looks off, just reply to this email and we'll make it right.

Mind sharing how it went? Reviews help other shoppers find us.

@if (config('app.url'))
<x-mail::button :url="config('app.url').'/account/orders'">
View order
</x-mail::button>
@endif

Thanks for choosing us,<br>
{{ store_name() }}
</x-mail::message>
