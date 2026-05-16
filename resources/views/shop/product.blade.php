@extends('layouts.storefront')

@section('title', $product->meta_title ?: meta_title($product->name, store_name()))
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description ?? (__('Kerajinan bambu buatan tangan.').' '.$product->name)), 155))
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
      @php
        $productCrumbs = [
          ['label' => __('Beranda'), 'url' => route('home')],
          ['label' => __('Belanja'), 'url' => route('shop.index')],
        ];
        if ($product->category) {
          $productCrumbs[] = [
            'label' => $product->category->title,
            'url' => route('shop.category', $product->category),
          ];
        }
        $productCrumbs[] = ['label' => $product->name];

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
      <x-page-head :crumbs="$productCrumbs" eyebrow="{{ __('Detail produk') }}" compact :schema="false" />
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
                  aria-label="{{ __('Gambar sebelumnya') }}"
                >‹</button>
                <button
                  type="button"
                  class="product-detail__nav product-detail__nav--next"
                  @click="next()"
                  aria-label="{{ __('Gambar berikutnya') }}"
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
                  aria-label="{{ __('Lihat gambar :n', ['n' => $i + 1]) }}"
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
              <small style="margin-left:8px;color:var(--muted)">{{ number_format($averageRating, 1) }} · {{ $reviewsCount }} {{ __('ulasan') }}</small>
            @endif
          </div>
          @php
            $moq = max(1, (int) ($product->min_order_quantity ?? 1));
            $leadDays = (int) ($product->production_lead_days ?? 0);
            $waNumber = preg_replace('/\D+/', '', (string) (setting('whatsapp_order_number') ?: setting('store_phone') ?: ''));
            $waText = rawurlencode(__('Halo, saya mau tanya:')." {$product->name} (".route('shop.product', $product).')');
            $hasVariants = $product->hasVariants();
            $variantsPayload = $product->variants->map(fn ($v) => [
              'id' => $v->id,
              'label' => $v->label,
              'price' => (float) $v->effectivePrice(),
              'stock' => (int) $v->stock,
              'is_default' => (bool) $v->is_default,
            ])->values()->all();
            $defaultVariant = $product->defaultVariant();
            $tiersPayload = $product->priceTiers->map(fn ($t) => [
              'min' => (int) $t->min_quantity,
              'price' => (float) $t->unit_price,
            ])->values()->all();
            $hasTiers = ! empty($tiersPayload);
          @endphp

          <div
            x-data='{
              variants: @php echo json_encode($variantsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); @endphp,
              tiers: @php echo json_encode($tiersPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); @endphp,
              hasVariants: @json($hasVariants),
              hasTiers: @json($hasTiers),
              basePrice: @json((float) $product->price),
              baseStock: @json((int) $product->stock),
              moq: {{ $moq }},
              variantId: {{ $defaultVariant?->id ?? 'null' }},
              qty: {{ $moq }},
              get current() {
                if (!this.hasVariants) return { price: this.basePrice, stock: this.baseStock };
                return this.variants.find(v => v.id === this.variantId) || this.variants[0] || { price: this.basePrice, stock: 0 };
              },
              get tierPrice() {
                if (!this.tiers.length) return null;
                const sorted = [...this.tiers].sort((a, b) => a.min - b.min);
                const match = [...sorted].reverse().find(t => t.min <= this.qty);
                return match ? match.price : null;
              },
              get effectivePrice() {
                const tp = this.tierPrice;
                return tp !== null ? tp : this.current.price;
              },
              get savingsPct() {
                const tp = this.tierPrice;
                if (tp === null || tp >= this.current.price) return 0;
                return Math.round((1 - tp / this.current.price) * 100);
              },
              get displayPrice() { return new Intl.NumberFormat("id-ID").format(Math.round(this.effectivePrice)); },
              get displayBase() { return new Intl.NumberFormat("id-ID").format(Math.round(this.current.price)); },
              get availableStock() { return Number(this.current.stock || 0); },
              get effectiveMax() { return Math.max(this.moq, this.availableStock || this.moq); },
              get lineTotal() { return new Intl.NumberFormat("id-ID").format(Math.round(this.effectivePrice * this.qty)); },
              pickVariant(id) {
                this.variantId = id;
                this.$nextTick(() => this.clampQty());
              },
              clampQty() {
                const cap = this.availableStock > 0 ? this.availableStock : this.moq;
                this.qty = Math.min(Math.max(this.moq, Number(this.qty) || this.moq), cap);
              },
            }'
            x-init="$watch(\'qty\', () => clampQty())"
          >
            <div class="product-detail-buy">
              <div class="product-detail__price">
                Rp <span x-text="displayPrice">{{ number_format((float) $product->price, 0, ',', '.') }}</span>
                <template x-if="savingsPct > 0">
                  <span class="product-detail__price-badge">
                    {{ __('Hemat') }} <span x-text="savingsPct"></span>%
                  </span>
                </template>
                <template x-if="savingsPct > 0">
                  <small class="product-detail__price-note">
                    <s>Rp <span x-text="displayBase"></span></s> {{ __('/pcs (harga normal)') }}
                  </small>
                </template>
              </div>

              @if ($product->description)
                <p class="product-detail__desc">{{ $product->description }}</p>
              @endif

              @if ($hasVariants)
                <div class="product-detail-variants">
                  <div class="product-detail-variants__label">{{ __('Pilih ukuran') }}</div>
                  <div class="product-detail-variants__list" role="group" aria-label="{{ __('Pilih ukuran') }}">
                    @foreach ($product->variants as $v)
                      <button
                        type="button"
                        class="variant-chip"
                        @click="pickVariant({{ $v->id }})"
                        :class="variantId === {{ $v->id }} ? 'variant-chip--active' : ''"
                        {{ $v->stock === 0 ? 'disabled' : '' }}
                      >
                        {{ $v->label }}
                        @if ($v->stock === 0)
                          <span class="variant-chip__muted">{{ __('— habis') }}</span>
                        @endif
                      </button>
                    @endforeach
                  </div>
                </div>
              @endif

              <div class="product-detail__stock product-detail-stock-row">
                <template x-if="availableStock > 0">
                  <span class="stock-pill stock-pill--in">{{ __('Tersedia') }} · <span x-text="availableStock"></span> {{ __('stok') }}</span>
                </template>
                <template x-if="availableStock === 0">
                  <span class="stock-pill stock-pill--out">{{ __('Habis') }}</span>
                </template>
                @if ($moq > 1)
                  <span class="stock-pill stock-pill--moq">{{ __('Min. order :n pcs', ['n' => $moq]) }}</span>
                @endif
                @if ($leadDays > 0)
                  <span class="stock-pill stock-pill--lead">{{ __('Produksi :n hari kerja', ['n' => $leadDays]) }}</span>
                @endif
              </div>

              @if ($hasTiers)
                <div class="product-detail-bulk">
                  <div class="product-detail-bulk__title">{{ __('Harga grosir') }}</div>
                  <table class="product-detail-bulk__table">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('Kuantitas') }}</th>
                        <th scope="col">{{ __('Per unit') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($product->priceTiers as $i => $t)
                        @php
                          $next = $product->priceTiers[$i + 1] ?? null;
                          $rangeLabel = $next
                            ? $t->min_quantity.'–'.($next->min_quantity - 1).' pcs'
                            : '≥ '.$t->min_quantity.' pcs';
                        @endphp
                        <tr>
                          <td>{{ $rangeLabel }}</td>
                          <td>{{ idr($t->unit_price) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif

              <form action="{{ route('cart.add') }}" method="post" class="product-detail__cta">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}" />
                @if ($hasVariants)
                  <input type="hidden" name="variant_id" :value="variantId" />
                @endif
                <div class="qty">
                  <label for="qty">{{ __('Jumlah') }}</label>
                  <input
                    id="qty"
                    x-model.number="qty"
                    type="number"
                    name="quantity"
                    :min="moq"
                    :max="effectiveMax"
                    step="1"
                    :disabled="availableStock === 0"
                  />
                </div>
                <button
                  type="submit"
                  class="product-detail__add"
                  :disabled="availableStock === 0"
                  x-text="availableStock === 0 ? @js(__('Habis')) : @js(__('Tambah ke keranjang'))"
                >
                  {{ __('Tambah ke keranjang') }}
                </button>
              </form>
              <p class="product-detail-estimate">
                {{ __('Estimasi total:') }} <strong>Rp <span x-text="lineTotal"></span></strong>
              </p>
            </div>
          </div>

          <div class="product-detail__secondary">
            @if ($waNumber)
              <a
                href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
                target="_blank"
                rel="noopener noreferrer"
                class="product-detail__wa"
              >
                {{ __('Tanya / pesan via WhatsApp') }}
              </a>
            @endif

            @if (config('features.wishlist'))
              @auth
                @php $inWishlist = $product->isInWishlistOf(auth()->id()); @endphp
                <form
                  method="post"
                  action="{{ route('wishlist.toggle', $product) }}"
                  class="product-detail__wishlist"
                  @if ($inWishlist)
                    data-confirm="{{ __('Produk ini akan dihapus dari wishlist Anda. Lanjutkan?') }}"
                    data-confirm-title="{{ __('Hapus dari wishlist?') }}"
                    data-confirm-ok="{{ __('Ya, hapus') }}"
                  @endif
                >
                  @csrf
                  <button type="submit" class="product-detail__wishlist-btn">
                    {{ $inWishlist ? '♥ '.__('Sudah di wishlist') : '♡ '.__('Simpan ke wishlist') }}
                  </button>
                </form>
              @else
                <a class="product-detail__wishlist-btn product-detail__wishlist" href="{{ route('login') }}">♡ {{ __('Simpan ke wishlist') }}</a>
              @endauth
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="section container">
      <div class="section-head">
        <div>
          <div class="eyebrow">{{ __('Ulasan pelanggan') }}</div>
          <div class="section-title">{!! __('Apa kata <em>pembeli</em>') !!}</div>
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
                <strong>{{ $review->user?->name ?? __('Pelanggan') }}</strong>
                <span>· {{ $review->created_at->diffForHumans() }}</span>
              </div>
            </article>
          @empty
            <p class="confirmation-meta">{{ __('Belum ada ulasan. Jadilah yang pertama mengulas produk ini!') }}</p>
          @endforelse
        </div>

        <aside class="review-form-aside">
          @auth
            @if ($canReview)
              <form method="post" action="{{ route('reviews.store', $product) }}" class="review-form confirmation-card">
                @csrf
                <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Tulis ulasan') }}</h3>
                <label class="review-form__label">
                  {{ __('Rating') }}
                  <select name="rating" required>
                    <option value="">{{ __('Pilih…') }}</option>
                    @for ($i = 5; $i >= 1; $i--)
                      <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                    @endfor
                  </select>
                  @error('rating')<span class="form-error">{{ $message }}</span>@enderror
                </label>
                <label class="review-form__label">
                  {{ __('Judul (opsional)') }}
                  <input type="text" name="title" maxlength="120" value="{{ old('title') }}" />
                </label>
                <label class="review-form__label">
                  {{ __('Ulasan Anda') }}
                  <textarea name="body" rows="4" required minlength="10" maxlength="2000">{{ old('body') }}</textarea>
                  @error('body')<span class="form-error">{{ $message }}</span>@enderror
                </label>
                <button type="submit" class="hero-cta">{{ __('Kirim ulasan') }}</button>
              </form>
            @elseif ($hasReviewed)
              <div class="confirmation-card">
                <p class="confirmation-meta" style="margin:0">{{ __('Terima kasih — Anda sudah mengulas produk ini.') }}</p>
              </div>
            @else
              <div class="confirmation-card">
                <p class="confirmation-meta" style="margin:0">{{ __('Hanya pelanggan yang membeli produk ini yang dapat memberi ulasan.') }}</p>
              </div>
            @endif
          @else
            <div class="confirmation-card">
              <p class="confirmation-meta">{{ __('Ingin memberi ulasan?') }}</p>
              <a class="cart-link-btn" href="{{ route('login') }}">{{ __('Masuk ke akun Anda') }}</a>
            </div>
          @endauth
        </aside>
      </div>
    </section>

    @if ($related->count() > 0)
      <section class="section container">
        <div class="section-head">
          <div>
            <div class="eyebrow">{{ __('Anda mungkin juga suka') }}</div>
            <div class="section-title">{!! __('Produk <em>terkait</em>') !!}</div>
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
                <span class="add-btn">{{ __('Lihat') }}</span>
              </div>
            </a>
          @endforeach
        </div>
      </section>
    @endif

    <x-site-footer />
  </main>
@endsection
