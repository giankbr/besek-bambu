@extends('layouts.storefront')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container shop-head">
      <div class="eyebrow">Eco Essentials · Planet-Friendly</div>
      <h1 class="section-title shop-title">All <em>Products</em></h1>

      <form method="get" action="{{ route('shop.index') }}" class="shop-filter">
        <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Search products..." />
        <select name="category" onchange="this.form.submit()">
          <option value="">All categories</option>
          @foreach ($categories as $category)
            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->title }}</option>
          @endforeach
        </select>
        <button type="submit" class="hero-cta">Filter</button>
      </form>
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
