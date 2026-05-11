@extends('layouts.storefront')

@section('title', store_name().' — Besek Bambu Handmade untuk Hantaran & Kemasan')
@section('meta_description', 'Pesan besek bambu handmade langsung dari pengrajin Indonesia. Cocok untuk hantaran, hampers, dan kemasan ramah lingkungan untuk UMKM maupun acara spesial.')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <div class="container page-shell">
      <div class="page-hero-shell">
        <x-hero />
      </div>
      <x-products-section :products="$products" />
      <x-story-band />
      <x-categories-section :categories="$categories" />
      <x-gallery-section :gallery-items="$galleryItems" />
      <x-reviews-section :reviews="$reviews" />
      <x-collage-section />
      <x-newsletter />
    </div>
    <x-site-footer />
  </main>
@endsection
