@extends('layouts.storefront')

@section('title', $post->resolvedMetaTitle())
@section('meta_description', $post->resolvedMetaDescription())
@section('og_type', 'article')
@if ($post->resolvedOgImage())
  @section('meta_image', $post->resolvedOgImage())
@endif

@push('head')
  @if ($post->published_at)
    <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}" />
  @endif
  @if ($post->updated_at)
    <meta property="article:modified_time" content="{{ $post->updated_at->toAtomString() }}" />
  @endif
  @if ($post->author_name)
    <meta property="article:author" content="{{ $post->author_name }}" />
  @endif
  <meta property="og:image:alt" content="{{ $post->localizedTitle() }}" />

  @php
    $articleSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'Article',
      'headline' => $post->localizedTitle(),
      'description' => $post->resolvedMetaDescription(),
      'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID',
      'datePublished' => optional($post->published_at)->toAtomString(),
      'dateModified' => optional($post->updated_at)->toAtomString(),
      'author' => [
        '@type' => 'Person',
        'name' => $post->author_name ?: store_name(),
        'url' => route('about'),
      ],
      'publisher' => [
        '@type' => 'Organization',
        'name' => store_name(),
        'logo' => store_logo_url() ? [
          '@type' => 'ImageObject',
          'url' => store_logo_url(),
          'width' => 200,
          'height' => 60,
        ] : null,
      ],
      'mainEntityOfPage' => route('blog.show', $post),
      'image' => $post->resolvedOgImage(),
    ];
    $articleSchema = array_filter($articleSchema, fn ($v) => $v !== null && $v !== '');
  @endphp
  <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <article class="container blog-article">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Blog'), 'url' => route('blog.index')],
            ['label' => $post->localizedTitle()],
        ]"
        eyebrow="{{ __('Artikel') }}"
        :schema="false"
      >
        <h1 class="section-title page-head__title cart-title">{{ $post->localizedTitle() }}</h1>
        <p class="blog-article__meta">
          <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('d F Y') }}</time>
          @if ($post->author_name)
            <span aria-hidden="true">·</span>
            <span>{{ $post->author_name }}</span>
          @endif
        </p>
      </x-page-head>

      <x-blog-share :title="$post->localizedTitle()" :url="route('blog.show', $post)" />

      <div class="blog-article__body">
        {!! $post->localizedBody() !!}
      </div>

      @if ($related->isNotEmpty())
        <section class="blog-related">
          <h2 class="about-block__title">{{ __('Artikel lainnya') }}</h2>
          <div class="blog-grid blog-grid--compact">
            @foreach ($related as $item)
              <article class="blog-card">
                <a href="{{ route('blog.show', $item) }}" class="blog-card__link">
                  <h3 class="blog-card__title">{{ $item->localizedTitle() }}</h3>
                  <span class="blog-card__more">{{ __('Baca') }} →</span>
                </a>
              </article>
            @endforeach
          </div>
        </section>
      @endif

      <div class="blog-article__cta">
        <a href="{{ route('shop.index') }}" class="hero-cta">{{ __('Lihat katalog besek') }}</a>
        <a href="{{ route('wholesale') }}" class="cart-link-btn">{{ __('Pesan grosir / custom') }}</a>
      </div>
    </article>

    <x-site-footer />
  </main>
@endsection
