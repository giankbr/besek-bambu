@props(['products'])

<section class="section container">
  <div class="section-head">
    <div>
      <div class="eyebrow">Eco Essentials · Planet-Friendly</div>
      <div class="section-title">Bestselling ✦ <em>Products</em></div>
    </div>
    <a href="{{ route('shop.index') }}" class="view-more">View more →</a>
  </div>

  <div class="grid-4">
    @foreach ($products as $product)
      <x-product-card :product="$product" />
    @endforeach
  </div>
</section>
