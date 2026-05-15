@extends('layouts.storefront')

@section('title', 'My wishlist — Besek Bambu')

@section('content')
  <x-account-page
    active="wishlist"
    :crumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Akun', 'url' => route('account.index')],
        ['label' => 'Wishlist'],
    ]"
    eyebrow="Disimpan nanti"
  >
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">Wishlist <em>saya</em></h1>
    </x-slot:heading>

    @if (session('status'))
      <div class="cart-flash" role="status">{{ session('status') }}</div>
    @endif

    <section class="confirmation-card account-panel account-orders-panel">
      <div class="account-section-head">
        <div>
          <p class="confirmation-section-title">Wishlist</p>
          <h2 class="account-card-title">Produk tersimpan</h2>
        </div>
      </div>

      @if ($products->isEmpty())
        <div class="account-empty-state">
          <p class="account-empty-state__title">Wishlist masih kosong.</p>
          <p class="confirmation-meta">Simpan produk favorit Anda untuk dibeli nanti.</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">Browse products</a>
        </div>
      @else
        <div class="account-wishlist-grid grid-4">
          @foreach ($products as $product)
            <div class="wishlist-cell">
              <x-product-card :product="$product" />
              <div class="wishlist-cell__actions">
                @if ($product->stock > 0)
                  <form method="post" action="{{ route('cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <input type="hidden" name="quantity" value="1" />
                    <button type="submit" class="cart-link-btn">Add to cart</button>
                  </form>
                @else
                  <span class="cart-link-btn cart-link-btn--disabled">Sold out</span>
                @endif
                <form method="post" action="{{ route('wishlist.toggle', $product) }}">
                  @csrf
                  <button type="submit" class="cart-link-btn cart-link-btn--danger">Remove</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </x-account-page>
@endsection
