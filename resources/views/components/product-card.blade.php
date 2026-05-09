@props(['product'])

<a class="product {{ $product->color_class }}" href="{{ route('shop.product', $product) }}">
  <div class="product-img">{{ $product->icon }}</div>
  <div class="product-name">{{ $product->name }}</div>
  <div class="product-stars">{{ str_repeat('★', $product->rating) }}{{ str_repeat('☆', 5 - $product->rating) }}</div>
  <div class="product-foot">
    <span class="product-price">${{ number_format($product->price, 2) }}</span>
    <span class="add-btn">View</span>
  </div>
</a>
