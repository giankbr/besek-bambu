@extends('layouts.storefront')

@section('title', meta_title($category->title, store_name()))
@section('meta_description', __('Jelajahi kategori :cat dari koleksi besek bambu handmade :store. Pilihan produk siap kirim ke seluruh Indonesia.', ['cat' => $category->title, 'store' => store_name()]))

@push('head')
  @php
    $categoryItemList = $products->values()->map(function ($product, $index) {
      return [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => route('shop.product', $product),
        'name' => $product->name,
      ];
    })->all();

    $categoryItemListSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'name' => 'Kategori '.$category->title,
      'numberOfItems' => count($categoryItemList),
      'itemListElement' => $categoryItemList,
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($categoryItemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <div class="cat-banner" style="background-image: linear-gradient(rgba(0,0,0,.2), rgba(0,0,0,.5)), url('{{ image_src($category->image_url) }}');">
        <div class="cat-banner__inner">
          <div class="eyebrow eyebrow--light">{{ __('Kategori') }}</div>
          <h1 class="cat-banner__title"><em>{{ $category->title }}</em></h1>
          <p class="cat-banner__count">{{ $products->total() }} {{ __('produk') }}</p>
        </div>
      </div>
    </section>

    <section class="container">
      @if ($products->count() === 0)
        <p class="shop-empty sf-empty">{{ __('Belum ada produk di kategori ini.') }}</p>
      @else
        <div class="grid-4 shop-grid">
          @foreach ($products as $product)
            <a class="product {{ $product->color_class }}" href="{{ route('shop.product', $product) }}">
              <div class="product-img">{{ $product->icon }}</div>
              <div class="product-name">{{ $product->name }}</div>
              <div class="product-stars">{{ str_repeat('★', $product->rating) }}{{ str_repeat('☆', 5 - $product->rating) }}</div>
              <div class="product-foot">
                <span class="product-price">{{ idr($product->price) }}</span>
                <span class="add-btn">{{ __('Lihat') }}</span>
              </div>
            </a>
          @endforeach
        </div>

        <div class="pagination-wrap">
          {{ $products->links() }}
        </div>
      @endif
    </section>

    <x-site-footer />
  </main>
@endsection
