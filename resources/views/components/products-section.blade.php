@props(['products'])

<section class="section">
  <div class="section-head">
    <div>
      <div class="eyebrow">{{ __('Pilihan utama · Anyaman bambu') }}</div>
      <div class="section-title">{!! __('Produk <em>terlaris</em>') !!}</div>
    </div>
    <a href="{{ route('shop.index') }}" class="view-more">{{ __('Lihat semua') }} →</a>
  </div>

  <div class="grid-4">
    @foreach ($products as $product)
      <x-product-card :product="$product" />
    @endforeach
  </div>
</section>
