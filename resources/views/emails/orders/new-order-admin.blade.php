<x-mail::message>
# {{ __('New order received') }}

{{ __('A new order has been placed on your store.') }}

**{{ __('Order') }}:** {{ $order->number }}  
**{{ __('Customer') }}:** {{ $order->customer_name }} ({{ $order->customer_email }})  
@if ($order->customer_phone)
**{{ __('Phone') }}:** {{ $order->customer_phone }}  
@endif
**{{ __('Status') }}:** {{ order_status_label($order->status) }}  
**{{ __('Payment') }}:** {{ payment_status_label($order->payment_status) }}@if ($order->payment_method) ({{ payment_method_label($order->payment_method) }})@endif  
**{{ __('Total') }}:** {{ idr($order->total) }}

<x-mail::table>
| {{ __('Product') }} | {{ __('Qty') }} | {{ __('Price') }} |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}@if ($item->variant_label) ({{ $item->variant_label }})@endif | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('admin.orders.show', $order)">
{{ __('View order') }}
</x-mail::button>

{{ store_name() }}
</x-mail::message>
