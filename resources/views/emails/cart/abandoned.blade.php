<x-mail::message>
# Forgot something, {{ $user->name }}?

You left {{ $items->count() }} item{{ $items->count() === 1 ? '' : 's' }} in your cart at **{{ store_name() }}**. They're still waiting for you.

<x-mail::table>
| Item | Qty | Subtotal |
|:-----|:---:|---------:|
@foreach ($items as $item)
| {{ $item->product->icon }} {{ $item->product->name }} | {{ $item->quantity }} | {{ idr($item->line_total) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('cart.show')">
Return to my cart
</x-mail::button>

Stock is limited and items can sell out quickly. If anything's wrong, just reply to this email.

Best,<br>
{{ store_name() }}
</x-mail::message>
