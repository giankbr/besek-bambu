@props(['products'])

<section class="section container">
  <div class="section-head">
    <div>
      <div class="eyebrow">Pilihan utama · Anyaman bambu</div>
      <div class="section-title">Produk ✦ <em>terlaris</em></div>
    </div>
    <a href="{{ route('shop.index') }}" class="view-more">Lihat semua →</a>
  </div>

  <div class="grid-4">
    @foreach ($products as $product)
      <x-product-card :product="$product" />
    @endforeach
  </div>
</section>
