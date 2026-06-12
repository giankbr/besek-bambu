@extends('layouts.storefront')

@php
  $blogTitle = meta_title(__('Tips & Panduan Besek Bambu'), store_name());
  $blogDescription = __('Artikel tentang besek bambu: panduan ukuran seserahan, ide isi hantaran, perawatan anyaman, dan tips kemasan ramah lingkungan untuk UMKM.');
@endphp
@section('title', $blogTitle)
@section('meta_description', $blogDescription)

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container blog-section">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Blog')],
        ]"
        eyebrow="{{ __('Tips & inspirasi') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Panduan & <em>inspirasi</em> besek') !!}</h1>
      </x-page-head>

      @if ($posts->count() === 0)
        <p class="shop-empty sf-empty">{{ __('Belum ada artikel. Kembali lagi nanti.') }}</p>
      @else
        <div class="blog-grid">
          @foreach ($posts as $post)
            <article class="blog-card">
              <a href="{{ route('blog.show', $post) }}" class="blog-card__link">
                <time class="blog-card__date" datetime="{{ $post->published_at?->toDateString() }}">
                  {{ $post->published_at?->translatedFormat('d F Y') }}
                </time>
                <h2 class="blog-card__title">{{ $post->title }}</h2>
                <p class="blog-card__excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}</p>
                <span class="blog-card__more">{{ __('Baca selengkapnya') }} →</span>
              </a>
            </article>
          @endforeach
        </div>

        <div class="pagination-wrap">
          {{ $posts->links() }}
        </div>
      @endif
    </section>

    <x-site-footer />
  </main>
@endsection
