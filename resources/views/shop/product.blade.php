@extends('layouts.storefront')

@section('title', $product->name . ' — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('shop.index') }}">Shop</a>
        @if ($product->category)
          <span>/</span>
          <a href="{{ route('shop.category', $product->category) }}">{{ $product->category->title }}</a>
        @endif
        <span>/</span>
        <span class="current">{{ $product->name }}</span>
      </nav>

      <div class="product-detail">
        <div class="product-detail__media {{ $product->color_class }}">
          @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" />
          @else
            <div class="product-detail__icon">{{ $product->icon }}</div>
          @endif
        </div>

        <div class="product-detail__body">
          @if ($product->category)
            <a class="product-detail__cat" href="{{ route('shop.category', $product->category) }}">{{ $product->category->title }}</a>
          @endif
          <h1 class="product-detail__name">{{ $product->name }}</h1>
          <div class="product-stars">{{ str_repeat('★', $product->rating) }}{{ str_repeat('☆', 5 - $product->rating) }}</div>
          <div class="product-detail__price">${{ number_format($product->price, 2) }}</div>

          @if ($product->description)
            <p class="product-detail__desc">{{ $product->description }}</p>
          @endif

          <div class="product-detail__stock">
            @if ($product->stock > 0)
              <span class="stock-pill stock-pill--in">In stock · {{ $product->stock }} available</span>
            @else
              <span class="stock-pill stock-pill--out">Sold out</span>
            @endif
          </div>

          <form action="{{ route('cart.add') }}" method="post" class="product-detail__cta">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}" />
            <div class="qty">
              <label for="qty">Qty</label>
              <input id="qty" type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" {{ $product->stock === 0 ? 'disabled' : '' }} />
            </div>
            <button type="submit" class="hero-cta" {{ $product->stock === 0 ? 'disabled' : '' }}>
              {{ $product->stock === 0 ? 'Sold out' : 'Add to cart' }}
            </button>
          </form>
        </div>
      </div>
    </section>

    @if ($related->count() > 0)
      <section class="section container">
        <div class="section-head">
          <div>
            <div class="eyebrow">You may also like</div>
            <div class="section-title">Related ✦ <em>Products</em></div>
          </div>
        </div>
        <div class="grid-4">
          @foreach ($related as $r)
            <a class="product {{ $r->color_class }}" href="{{ route('shop.product', $r) }}">
              <div class="product-img">{{ $r->icon }}</div>
              <div class="product-name">{{ $r->name }}</div>
              <div class="product-stars">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</div>
              <div class="product-foot">
                <span class="product-price">${{ number_format($r->price, 2) }}</span>
                <span class="add-btn">View</span>
              </div>
            </a>
          @endforeach
        </div>
      </section>
    @endif

    <x-site-footer />
  </main>
@endsection
