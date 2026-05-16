@extends('layouts.storefront')

@section('title', meta_title(__('Katalog Besek Bambu'), store_name()))
@section('meta_description', __('Lihat katalog besek bambu berbagai ukuran dan model. Tersedia untuk kebutuhan hampers, seserahan, souvenir, dan kemasan produk ramah lingkungan.'))

@push('head')
  @php
    $itemList = $products->values()->map(function ($product, $index) {
      return [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'url' => route('shop.product', $product),
        'name' => $product->name,
      ];
    })->all();

    $itemListSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'name' => 'Katalog Besek Bambu',
      'numberOfItems' => count($itemList),
      'itemListElement' => $itemList,
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($itemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container shop-page">
      <div class="shop-head">
        <div class="eyebrow">{{ __('Katalog besek · Anyaman bambu') }}</div>
        <h1 class="section-title shop-title">{!! __('Semua <em>produk</em>') !!}</h1>
      </div>

      <form method="get" action="{{ route('shop.index') }}" class="shop-filter">
        <input type="search" name="q" value="{{ $searchTerm }}" placeholder="{{ __('Cari produk…') }}" aria-label="{{ __('Cari produk') }}" />
        <select name="sort" aria-label="{{ __('Urutkan') }}">
          <option value="featured" @selected($sort === 'featured')>{{ __('Unggulan') }}</option>
          <option value="newest" @selected($sort === 'newest')>{{ __('Terbaru') }}</option>
          <option value="price-asc" @selected($sort === 'price-asc')>{{ __('Harga: rendah ke tinggi') }}</option>
          <option value="price-desc" @selected($sort === 'price-desc')>{{ __('Harga: tinggi ke rendah') }}</option>
          <option value="rating" @selected($sort === 'rating')>{{ __('Rating tertinggi') }}</option>
        </select>
        <button type="submit" class="shop-filter__submit">{{ __('Terapkan') }}</button>
      </form>

      <details class="shop-advanced">
        <summary>{{ __('Filter lanjutan') }}</summary>
        <form method="get" action="{{ route('shop.index') }}" class="shop-advanced__form">
          <input type="hidden" name="q" value="{{ $searchTerm }}" />
          <input type="hidden" name="sort" value="{{ $sort }}" />
          <label>
            {{ __('Harga min (Rp)') }}
            <input type="number" name="min_price" value="{{ $minPrice ?: '' }}" min="0" />
          </label>
          <label>
            {{ __('Harga maks (Rp)') }}
            <input type="number" name="max_price" value="{{ $maxPrice ?: '' }}" min="0" />
          </label>
          <label>
            {{ __('Rating minimum') }}
            <select name="min_rating">
              <option value="">{{ __('Semua') }}</option>
              @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected($minRating === $i)>{{ str_repeat('★', $i) }} {{ __('ke atas') }}</option>
              @endfor
            </select>
          </label>
          <button type="submit" class="shop-filter__submit">{{ __('Terapkan filter') }}</button>
          @if ($minPrice || $maxPrice || $minRating)
            <a class="cart-link-btn" href="{{ route('shop.index', ['q' => $searchTerm, 'sort' => $sort]) }}">{{ __('Hapus filter') }}</a>
          @endif
        </form>
      </details>

      @if ($searchTerm)
        <p class="shop-search-notice">
          @if ($products->total() > 0)
            {!! __('Menampilkan :count hasil untuk :term.', ['count' => '<strong>'.$products->total().'</strong>', 'term' => '«<strong>'.e($searchTerm).'</strong>»']) !!}
          @else
            {!! __('Tidak ada hasil untuk :term. Coba kata lain atau', ['term' => '«<strong>'.e($searchTerm).'</strong>»']) !!}
          @endif
          <a href="{{ route('shop.index') }}">{{ __('hapus pencarian') }}</a>.
        </p>
      @endif

      @if ($products->count() === 0)
        <p class="shop-empty sf-empty">{{ __('Tidak ada produk.') }}</p>
      @else
        <div class="grid-4 shop-grid">
          @foreach ($products as $product)
            <a class="product {{ $product->color_class }}" href="{{ route('shop.product', $product) }}">
              <div class="product-img">{{ $product->icon }}</div>
              <div class="product-name">{{ $product->name }}</div>
              <div class="product-stars">{{ str_repeat('★', $product->rating) }}{{ str_repeat('☆', 5 - $product->rating) }}</div>
              <div class="product-foot">
                <span class="product-price">{{ idr($product->price) }}</span>
                @if ($product->stock > 0)
                  <span class="add-btn">{{ __('Lihat') }}</span>
                @else
                  <span class="add-btn add-btn--disabled">{{ __('Habis') }}</span>
                @endif
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
