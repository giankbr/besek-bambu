@extends('layouts.storefront')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container shop-page">
      <div class="shop-head">
        <div class="eyebrow">Katalog besek · Anyaman bambu</div>
        <h1 class="section-title shop-title">Semua <em>produk</em></h1>
      </div>

      <form method="get" action="{{ route('shop.index') }}" class="shop-filter">
        <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Cari produk…" aria-label="Cari produk" />
        <select name="category" aria-label="Kategori">
          <option value="">Semua kategori</option>
          @foreach ($categories as $category)
            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->title }}</option>
          @endforeach
        </select>
        <select name="sort" aria-label="Urutkan">
          <option value="featured" @selected($sort === 'featured')>Unggulan</option>
          <option value="newest" @selected($sort === 'newest')>Terbaru</option>
          <option value="price-asc" @selected($sort === 'price-asc')>Harga: rendah ke tinggi</option>
          <option value="price-desc" @selected($sort === 'price-desc')>Harga: tinggi ke rendah</option>
          <option value="rating" @selected($sort === 'rating')>Rating tertinggi</option>
        </select>
        <button type="submit" class="shop-filter__submit">Terapkan</button>
      </form>

      <details class="shop-advanced">
        <summary>Filter lanjutan</summary>
        <form method="get" action="{{ route('shop.index') }}" class="shop-advanced__form">
          <input type="hidden" name="q" value="{{ $searchTerm }}" />
          <input type="hidden" name="category" value="{{ $activeCategory }}" />
          <input type="hidden" name="sort" value="{{ $sort }}" />
          <label>
            Harga min (Rp)
            <input type="number" name="min_price" value="{{ $minPrice ?: '' }}" min="0" />
          </label>
          <label>
            Harga maks (Rp)
            <input type="number" name="max_price" value="{{ $maxPrice ?: '' }}" min="0" />
          </label>
          <label>
            Rating minimum
            <select name="min_rating">
              <option value="">Semua</option>
              @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected($minRating === $i)>{{ str_repeat('★', $i) }} ke atas</option>
              @endfor
            </select>
          </label>
          <button type="submit" class="shop-filter__submit">Terapkan filter</button>
          @if ($minPrice || $maxPrice || $minRating)
            <a class="cart-link-btn" href="{{ route('shop.index', ['q' => $searchTerm, 'category' => $activeCategory, 'sort' => $sort]) }}">Hapus filter</a>
          @endif
        </form>
      </details>

      @if ($searchTerm)
        <p class="shop-search-notice">
          @if ($products->total() > 0)
            Menampilkan <strong>{{ $products->total() }}</strong> hasil untuk «<strong>{{ $searchTerm }}</strong>».
          @else
            Tidak ada hasil untuk «<strong>{{ $searchTerm }}</strong>». Coba kata lain atau
          @endif
          <a href="{{ route('shop.index') }}">hapus pencarian</a>.
        </p>
      @endif

      @if ($products->count() === 0)
        <p class="shop-empty">Tidak ada produk.</p>
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
                  <span class="add-btn">Lihat</span>
                @else
                  <span class="add-btn add-btn--disabled">Habis</span>
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
