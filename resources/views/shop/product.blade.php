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
          @php
            $displayRating = $reviewsCount > 0 ? (int) round($averageRating) : $product->rating;
          @endphp
          <div class="product-stars">
            {{ str_repeat('★', $displayRating) }}{{ str_repeat('☆', 5 - $displayRating) }}
            @if ($reviewsCount > 0)
              <small style="margin-left:8px;color:var(--muted)">{{ number_format($averageRating, 1) }} · {{ $reviewsCount }} {{ Str::plural('review', $reviewsCount) }}</small>
            @endif
          </div>
          <div class="product-detail__price">{{ idr($product->price) }}</div>

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

    <section class="section container">
      <div class="section-head">
        <div>
          <div class="eyebrow">Customer reviews</div>
          <div class="section-title">What buyers ✦ <em>say</em></div>
        </div>
      </div>

      @if (session('status'))
        <div class="confirmation-card" style="margin-bottom:1rem;background:#eef7ee">
          <p class="confirmation-meta" style="margin:0">{{ session('status') }}</p>
        </div>
      @endif

      <div class="reviews-grid">
        <div class="reviews-list">
          @forelse ($reviews as $review)
            <article class="review-card">
              <div class="product-stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
              @if ($review->title)
                <h3 class="review-card__title">{{ $review->title }}</h3>
              @endif
              <p class="review-card__body">{{ $review->body }}</p>
              <div class="review-card__meta">
                <strong>{{ $review->user?->name ?? 'Customer' }}</strong>
                <span>· {{ $review->created_at->diffForHumans() }}</span>
              </div>
            </article>
          @empty
            <p class="confirmation-meta">No reviews yet. Be the first to review this product!</p>
          @endforelse
        </div>

        <aside class="review-form-aside">
          @auth
            @if ($canReview)
              <form method="post" action="{{ route('reviews.store', $product) }}" class="review-form confirmation-card">
                @csrf
                <h3 class="confirmation-section-title" style="margin-top:0">Write a review</h3>
                <label class="review-form__label">
                  Rating
                  <select name="rating" required>
                    <option value="">Select…</option>
                    @for ($i = 5; $i >= 1; $i--)
                      <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                    @endfor
                  </select>
                  @error('rating')<span class="form-error">{{ $message }}</span>@enderror
                </label>
                <label class="review-form__label">
                  Title (optional)
                  <input type="text" name="title" maxlength="120" value="{{ old('title') }}" />
                </label>
                <label class="review-form__label">
                  Your review
                  <textarea name="body" rows="4" required minlength="10" maxlength="2000">{{ old('body') }}</textarea>
                  @error('body')<span class="form-error">{{ $message }}</span>@enderror
                </label>
                <button type="submit" class="hero-cta">Submit review</button>
              </form>
            @elseif ($hasReviewed)
              <div class="confirmation-card">
                <p class="confirmation-meta" style="margin:0">Thanks — you've already reviewed this product.</p>
              </div>
            @else
              <div class="confirmation-card">
                <p class="confirmation-meta" style="margin:0">Only customers who purchased this product can leave a review.</p>
              </div>
            @endif
          @else
            <div class="confirmation-card">
              <p class="confirmation-meta">Want to leave a review?</p>
              <a class="cart-link-btn" href="{{ route('login') }}">Sign in to your account</a>
            </div>
          @endauth
        </aside>
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
                <span class="product-price">{{ idr($r->price) }}</span>
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
