@extends('layouts.storefront')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container shop-head">
      <div class="eyebrow">Eco Essentials · Planet-Friendly</div>
      <h1 class="section-title shop-title">All <em>Products</em></h1>

      <form method="get" action="{{ route('shop.index') }}" class="shop-filter">
        <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Search products..." />
        <select name="category">
          <option value="">All categories</option>
          @foreach ($categories as $category)
            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->title }}</option>
          @endforeach
        </select>
        <select name="sort">
          <option value="featured" @selected($sort === 'featured')>Featured</option>
          <option value="newest" @selected($sort === 'newest')>Newest</option>
          <option value="price-asc" @selected($sort === 'price-asc')>Price: low to high</option>
          <option value="price-desc" @selected($sort === 'price-desc')>Price: high to low</option>
          <option value="rating" @selected($sort === 'rating')>Top rated</option>
        </select>
        <button type="submit" class="hero-cta">Apply</button>
      </form>

      <details class="shop-advanced">
        <summary>Advanced filters</summary>
        <form method="get" action="{{ route('shop.index') }}" class="shop-advanced__form">
          <input type="hidden" name="q" value="{{ $searchTerm }}" />
          <input type="hidden" name="category" value="{{ $activeCategory }}" />
          <input type="hidden" name="sort" value="{{ $sort }}" />
          <label>
            Min price (Rp)
            <input type="number" name="min_price" value="{{ $minPrice ?: '' }}" min="0" />
          </label>
          <label>
            Max price (Rp)
            <input type="number" name="max_price" value="{{ $maxPrice ?: '' }}" min="0" />
          </label>
          <label>
            Min rating
            <select name="min_rating">
              <option value="">Any</option>
              @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected($minRating === $i)>{{ str_repeat('★', $i) }} & up</option>
              @endfor
            </select>
          </label>
          <button type="submit" class="cart-link-btn">Apply filters</button>
          @if ($minPrice || $maxPrice || $minRating)
            <a class="cart-link-btn" href="{{ route('shop.index', ['q' => $searchTerm, 'category' => $activeCategory, 'sort' => $sort]) }}">Clear</a>
          @endif
        </form>
      </details>
    </section>

    <section class="container">
      @if ($products->count() === 0)
        <p class="shop-empty">No products found.</p>
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
                  <span class="add-btn">View</span>
                @else
                  <span class="add-btn add-btn--disabled">Sold out</span>
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
