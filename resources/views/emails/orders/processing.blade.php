<x-mail::message>
# {{ mail_greeting($order->customer_name) }}

{{ __('Your order :number is now being processed.', ['number' => $order->number]) }}

{{ __('Our team is preparing your items. We will notify you when the package has been handed to the courier.') }}

**{{ __('Order total') }}:** {{ idr($order->total) }}

<x-mail::button :url="order_signed_url('checkout.confirmation', $order)">
{{ __('View order details') }}
</x-mail::button>

{{ __('Thanks,') }}<br>
{{ store_name() }}
</x-mail::message>
