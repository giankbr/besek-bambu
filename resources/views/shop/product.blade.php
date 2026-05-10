@extends('layouts.storefront')

@section('title', $product->meta_title ?: ($product->name . ' — Besek Bambu'))
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description ?? ('Handcrafted bamboo kitchenware. ' . $product->name)), 155))
@section('og_type', 'product')
@php
  $ogSrc = $product->og_image ? image_src($product->og_image) : ($product->image_url ? image_src($product->image_url) : null);
@endphp
@if ($ogSrc)
  @section('meta_image', $ogSrc)
@endif

@push('head')
  <script src="//unpkg.com/alpinejs" defer></script>

  <meta property="product:price:amount" content="{{ (int) round((float) $product->price) }}" />
  <meta property="product:price:currency" content="IDR" />
  <meta property="product:availability" content="{{ $product->stock > 0 ? 'in stock' : 'out of stock' }}" />
  @if ($product->category)
    <meta property="product:category" content="{{ $product->category->title }}" />
  @endif

  @php
    $schemaImages = collect();
    if ($product->image_url) { $schemaImages->push(image_src($product->image_url)); }
    foreach ($product->images as $img) {
      $src = image_src($img->path);
      if ($src && ! $schemaImages->contains($src)) { $schemaImages->push($src); }
    }
    $schemaImages = $schemaImages->values()->all();

    $productSchema = array_filter([
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product->name,
      'description' => strip_tags((string) $product->description),
      'sku' => 'BSK-'.$product->id,
      'mpn' => 'BSK-'.$product->id,
      'image' => count($schemaImages) > 0 ? $schemaImages : null,
      'category' => $product->category?->title,
      'brand' => ['@type' => 'Brand', 'name' => store_name()],
      'offers' => [
        '@type' => 'Offer',
        'price' => (float) $product->price,
        'priceCurrency' => 'IDR',
        'priceValidUntil' => now()->addYear()->toDateString(),
        'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
        'url' => route('shop.product', $product),
        'seller' => ['@type' => 'Organization', 'name' => store_name()],
      ],
      'aggregateRating' => $reviewsCount > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => $averageRating,
        'reviewCount' => $reviewsCount,
        'bestRating' => 5,
        'worstRating' => 1,
      ] : null,
    ]);

    $breadcrumbItems = [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => 'Shop', 'item' => route('shop.index')],
    ];
    if ($product->category) {
      $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $product->category->title, 'item' => route('shop.category', $product->category)];
    }
    $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $product->category ? 4 : 3, 'name' => $product->name, 'item' => route('shop.product', $product)];

    $breadcrumbSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => $breadcrumbItems,
    ];
  @endphp

  <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
  <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

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

      @php
        $galleryImages = $product->images;
        $primary = $galleryImages->firstWhere('is_primary', true) ?? $galleryImages->first();
        $heroSrc = $primary ? image_src($primary->path) : ($product->image_url ? image_src($product->image_url) : null);

        $allMedia = collect();
        if ($product->image_url) {
          $allMedia->push(image_src($product->image_url));
        }
        foreach ($galleryImages as $img) {
          $src = image_src($img->path);
          if ($src && ! $allMedia->contains($src)) {
            $allMedia->push($src);
          }
        }
        $allMedia = $allMedia->values()->all();
        $hasMultiple = count($allMedia) > 1;
      @endphp
      <div
        class="product-detail"
        x-data='{
          images: @js($allMedia),
          index: 0,
          get active() { return this.images[this.index] ?? null },
          next() { if (this.images.length) this.index = (this.index + 1) % this.images.length },
          prev() { if (this.images.length) this.index = (this.index - 1 + this.images.length) % this.images.length },
        }'
        @keydown.window.arrow-right="next()"
        @keydown.window.arrow-left="prev()"
      >
        <div class="product-detail__media {{ $product->color_class }}">
          @if ($heroSrc)
            <div class="product-detail__hero">
              <img :src="active" src="{{ $heroSrc }}" alt="{{ $product->name }}" />
              @if ($hasMultiple)
                <button
                  type="button"
                  class="product-detail__nav product-detail__nav--prev"
                  @click="prev()"
                  aria-label="Previous image"
                >‹</button>
                <button
                  type="button"
                  class="product-detail__nav product-detail__nav--next"
                  @click="next()"
                  aria-label="Next image"
                >›</button>
                <div class="product-detail__counter" x-text="(index + 1) + ' / ' + images.length"></div>
              @endif
            </div>
          @else
            <div class="product-detail__icon">{{ $product->icon }}</div>
          @endif

          @if ($hasMultiple)
            <div class="product-detail__thumbs">
              @foreach ($allMedia as $i => $src)
                <button
                  type="button"
                  class="product-detail__thumb"
                  :class="index === {{ $i }} ? 'product-detail__thumb--active' : ''"
                  @click="index = {{ $i }}"
                  aria-label="View image {{ $i + 1 }}"
                >
                  <img src="{{ $src }}" alt="" loading="lazy" />
                </button>
              @endforeach
            </div>
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
          @php
            $moq = max(1, (int) ($product->min_order_quantity ?? 1));
            $leadDays = (int) ($product->production_lead_days ?? 0);
            $waNumber = preg_replace('/\D+/', '', (string) (setting('whatsapp_order_number') ?: setting('store_phone') ?: ''));
            $waText = rawurlencode("Halo, saya mau tanya: {$product->name} (".route('shop.product', $product).')');
            $hasVariants = $product->hasVariants();
            $variantsPayload = $product->variants->map(fn ($v) => [
              'id' => $v->id,
              'label' => $v->label,
              'price' => (float) $v->effectivePrice(),
              'stock' => (int) $v->stock,
              'is_default' => (bool) $v->is_default,
            ])->values()->all();
            $defaultVariant = $product->defaultVariant();
          @endphp

          <div
            x-data='{
              variants: @php echo json_encode($variantsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); @endphp,
              hasVariants: @json($hasVariants),
              basePrice: @json((float) $product->price),
              baseStock: @json((int) $product->stock),
              moq: {{ $moq }},
              variantId: {{ $defaultVariant?->id ?? 'null' }},
              get current() {
                if (!this.hasVariants) return { price: this.basePrice, stock: this.baseStock };
                return this.variants.find(v => v.id === this.variantId) || this.variants[0] || { price: this.basePrice, stock: 0 };
              },
              get displayPrice() { return new Intl.NumberFormat("id-ID").format(Math.round(this.current.price)); },
              get availableStock() { return Number(this.current.stock || 0); },
              get effectiveMax() { return Math.max(this.moq, this.availableStock || this.moq); },
              pickVariant(id) {
                this.variantId = id;
                this.$nextTick(() => {
                  if (this.$refs.qtyInput) {
                    const cap = this.availableStock > 0 ? this.availableStock : this.moq;
                    this.$refs.qtyInput.value = Math.min(Math.max(this.moq, Number(this.$refs.qtyInput.value) || this.moq), cap);
                  }
                });
              },
            }'
          >
            <div class="product-detail__price">Rp <span x-text="displayPrice">{{ number_format((float) $product->price, 0, ',', '.') }}</span></div>

            @if ($product->description)
              <p class="product-detail__desc">{{ $product->description }}</p>
            @endif

            @if ($hasVariants)
              <div style="margin:14px 0">
                <div style="font-weight:600;font-size:0.95rem;margin-bottom:6px">Pilih ukuran</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                  @foreach ($product->variants as $v)
                    <button
                      type="button"
                      @click="pickVariant({{ $v->id }})"
                      :class="variantId === {{ $v->id }} ? 'variant-chip variant-chip--active' : 'variant-chip'"
                      {{ $v->stock === 0 ? 'disabled' : '' }}
                      style="padding:8px 14px;border-radius:999px;border:1px solid #e5e0d6;background:#fff;cursor:pointer;font-weight:600;font-size:0.9rem"
                    >
                      {{ $v->label }}
                      @if ($v->stock === 0)
                        <small style="color:#b91c1c">— habis</small>
                      @endif
                    </button>
                  @endforeach
                </div>
              </div>
            @endif

            <div class="product-detail__stock" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
              <template x-if="availableStock > 0">
                <span class="stock-pill stock-pill--in">In stock · <span x-text="availableStock"></span> available</span>
              </template>
              <template x-if="availableStock === 0">
                <span class="stock-pill stock-pill--out">Sold out</span>
              </template>
              @if ($moq > 1)
                <span class="stock-pill" style="background:#fff8e1;color:#8a6d11">Min. order {{ $moq }} pcs</span>
              @endif
              @if ($leadDays > 0)
                <span class="stock-pill" style="background:#eaf2ff;color:#1e4faf">Lead time {{ $leadDays }} hari kerja</span>
              @endif
            </div>

            <form action="{{ route('cart.add') }}" method="post" class="product-detail__cta">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}" />
              @if ($hasVariants)
                <input type="hidden" name="variant_id" :value="variantId" />
              @endif
              <div class="qty">
                <label for="qty">Qty</label>
                <input
                  id="qty"
                  x-ref="qtyInput"
                  type="number"
                  name="quantity"
                  value="{{ $moq }}"
                  :min="moq"
                  :max="effectiveMax"
                  step="1"
                  :disabled="availableStock === 0"
                />
              </div>
              <button type="submit" class="hero-cta" :disabled="availableStock === 0" x-text="availableStock === 0 ? 'Sold out' : 'Add to cart'">Add to cart</button>
            </form>
          </div>

          @if ($waNumber)
            <a
              href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
              target="_blank"
              rel="noopener"
              class="cart-link-btn"
              style="margin-top:8px;background:#25D366;color:#fff;border-color:#25D366"
            >
              💬 Tanya / Order via WhatsApp
            </a>
          @endif

          @auth
            <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="product-detail__wishlist">
              @csrf
              <button type="submit" class="cart-link-btn">
                {{ $product->isInWishlistOf(auth()->id()) ? '♥ Saved to wishlist' : '♡ Save to wishlist' }}
              </button>
            </form>
          @else
            <a class="cart-link-btn product-detail__wishlist" href="{{ route('login') }}">♡ Save to wishlist</a>
          @endauth
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
