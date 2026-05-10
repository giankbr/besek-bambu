<x-mail::message>
# Low stock alert

The following product has crossed the low-stock threshold of **{{ $threshold }}** unit{{ $threshold === 1 ? '' : 's' }}.

- **Product:** {{ $product->name }}
- **SKU / slug:** {{ $product->slug }}
- **Current stock:** **{{ $product->stock }}**

<x-mail::button :url="route('admin.products.edit', $product)">
Open product
</x-mail::button>

You can change the threshold or recipient in **Admin → Settings → Store**.

Best,<br>
{{ store_name() }}
</x-mail::message>
