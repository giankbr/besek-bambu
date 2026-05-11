@props(['product'])

@php
  $isSaved = auth()->check() ? $product->isInWishlistOf(auth()->id()) : false;
@endphp

<div class="product-card-wrap">
  <a class="product {{ $product->color_class }}" href="{{ route('shop.product', $product) }}">
    <div class="product-img">{{ $product->icon }}</div>
    <div class="product-name">{{ $product->name }}</div>
    <div class="product-stars">{{ str_repeat('★', $product->rating) }}{{ str_repeat('☆', 5 - $product->rating) }}</div>
    <div class="product-foot">
      <span class="product-price">{{ idr($product->price) }}</span>
      <span class="add-btn">Lihat</span>
    </div>
  </a>

  @auth
    <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="product-card__wish">
      @csrf
      <button
        type="submit"
        class="product-card__wish-btn @class(['product-card__wish-btn--on' => $isSaved])"
        aria-label="{{ $isSaved ? __('Remove from wishlist') : __('Add to wishlist') }}"
        aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
        title="{{ $isSaved ? __('Saved to wishlist') : __('Save to wishlist') }}"
      >{{ $isSaved ? '♥' : '♡' }}</button>
    </form>
  @else
    <a
      href="{{ route('login') }}"
      class="product-card__wish product-card__wish-btn"
      aria-label="{{ __('Sign in to save to wishlist') }}"
      title="{{ __('Sign in to save') }}"
    >♡</a>
  @endauth
</div>
