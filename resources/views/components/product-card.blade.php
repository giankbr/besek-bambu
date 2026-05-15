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
      <span class="add-btn">{{ __('Lihat') }}</span>
    </div>
  </a>

  @auth
    <form
      method="post"
      action="{{ route('wishlist.toggle', $product) }}"
      class="product-card__wish"
      @if ($isSaved)
        data-confirm="{{ __('Produk ini akan dihapus dari wishlist Anda. Lanjutkan?') }}"
        data-confirm-title="{{ __('Hapus dari wishlist?') }}"
        data-confirm-ok="{{ __('Ya, hapus') }}"
      @endif
    >
      @csrf
      <button
        type="submit"
        class="product-card__wish-btn @class(['product-card__wish-btn--on' => $isSaved])"
        aria-label="{{ $isSaved ? __('Hapus dari wishlist') : __('Tambah ke wishlist') }}"
        aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
        title="{{ $isSaved ? __('Tersimpan di wishlist') : __('Simpan ke wishlist') }}"
      >{{ $isSaved ? '♥' : '♡' }}</button>
    </form>
  @else
    <a
      href="{{ route('login') }}"
      class="product-card__wish product-card__wish-btn"
      aria-label="{{ __('Masuk untuk simpan ke wishlist') }}"
      title="{{ __('Masuk untuk menyimpan') }}"
    >♡</a>
  @endauth
</div>
