@extends('layouts.storefront')

@section('title', 'Cart — '.store_name())

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <span class="current">Cart</span>
      </nav>

      <div class="eyebrow">Your selection</div>
      <h1 class="section-title cart-title"><em>Shopping</em> cart</h1>

      @if (session('status'))
        <div class="cart-flash">{{ session('status') }}</div>
      @endif

      @if ($items->isEmpty())
        <div class="cart-empty">
          <p>Your cart is empty.</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">Browse products</a>
        </div>
      @else
        <div class="cart-grid">
          <ul class="cart-items">
            @foreach ($items as $item)
              <li class="cart-item">
                <a class="cart-item__media {{ $item->product->color_class }}" href="{{ route('shop.product', $item->product) }}">
                  @if ($item->product->image_url)
                    <img src="{{ image_src($item->product->image_url) }}" alt="{{ $item->product->name }}" loading="lazy" decoding="async" />
                  @else
                    <span class="cart-item__icon">{{ $item->product->icon }}</span>
                  @endif
                </a>
                <div class="cart-item__body">
                  <a class="cart-item__name" href="{{ route('shop.product', $item->product) }}">{{ $item->product->name }}</a>
                  @if ($item->variant_label)
                    <div class="cart-item__variant" style="color:#7d6f5f;font-size:0.9rem">Size: <strong>{{ $item->variant_label }}</strong></div>
                  @endif
                  <div class="cart-item__price">{{ idr($item->unit_price) }}</div>

                  @php
                    $itemStockCap = $item->variant ? (int) $item->variant->stock : (int) $item->product->stock;
                    $itemMoq = max(1, (int) ($item->product->min_order_quantity ?? 1));
                  @endphp
                  <form method="post" action="{{ route('cart.update', $item->key) }}" class="cart-item__qty">
                    @csrf
                    @method('PATCH')
                    <label for="qty-{{ $item->key }}">Qty</label>
                    <input id="qty-{{ $item->key }}" type="number" name="quantity" value="{{ $item->quantity }}" min="{{ $itemMoq }}" max="{{ max($itemMoq, $itemStockCap) }}" />
                    <button type="submit" class="cart-link-btn">Update</button>
                  </form>
                </div>

                <div class="cart-item__right">
                  <div class="cart-item__line">{{ idr($item->line_total) }}</div>
                  <form method="post" action="{{ route('cart.destroy', $item->key) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cart-link-btn cart-link-btn--danger">Remove</button>
                  </form>
                </div>
              </li>
            @endforeach
          </ul>

          <aside class="cart-summary">
            <h2 class="cart-summary__title">Summary</h2>
            <div class="cart-summary__row">
              <span>Subtotal</span>
              <strong>{{ idr($subtotal) }}</strong>
            </div>

            @if ($coupon)
              <div class="cart-summary__row" style="color:#1f7a3a">
                <span>Discount ({{ $coupon->code }})</span>
                <strong>− {{ idr($discount) }}</strong>
              </div>
              <form method="post" action="{{ route('cart.coupon.remove') }}" style="margin-bottom:8px">
                @csrf
                @method('DELETE')
                <button type="submit" class="cart-link-btn">Remove coupon</button>
              </form>
            @else
              <form method="post" action="{{ route('cart.coupon.apply') }}" class="cart-coupon">
                @csrf
                <label for="coupon-code">Promo code</label>
                <div class="cart-coupon__row">
                  <input id="coupon-code" type="text" name="code" placeholder="Enter code" maxlength="64" />
                  <button type="submit" class="cart-link-btn">Apply</button>
                </div>
              </form>
            @endif

            @if ($tax > 0)
              <div class="cart-summary__row">
                <span>{{ $taxInclusive ? 'Tax included ('.rtrim(rtrim(number_format($taxRate, 2), '0'), '.').'%)' : 'Tax ('.rtrim(rtrim(number_format($taxRate, 2), '0'), '.').'%)' }}</span>
                <strong>{{ $taxInclusive ? idr($tax) : '+ '.idr($tax) }}</strong>
              </div>
            @endif

            <div class="cart-summary__row cart-summary__row--muted">
              <span>Shipping</span>
              <span>Calculated at checkout</span>
            </div>
            <div class="cart-summary__total">
              <span>Total</span>
              <strong>{{ idr($preTotal) }}</strong>
            </div>
            <a class="hero-cta cart-summary__cta" href="{{ route('checkout.show') }}">Proceed to checkout</a>
            <a class="cart-link-btn" href="{{ route('shop.index') }}">Continue shopping</a>
          </aside>
        </div>
      @endif
    </section>

    <x-site-footer />
  </main>
@endsection
