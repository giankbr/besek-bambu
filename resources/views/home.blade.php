@extends('layouts.storefront')

@section('title', meta_title(store_name(), __('Besek Bambu Handmade untuk Hantaran & Kemasan')))
@section('meta_description', __('Pesan besek bambu handmade langsung dari pengrajin Indonesia. Cocok untuk hantaran, hampers, dan kemasan ramah lingkungan untuk UMKM maupun acara spesial.'))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <div class="container page-shell">
      <div class="page-hero-shell">
        <x-hero />
      </div>
      <x-products-section :products="$products" />
      <x-story-band />
      <x-gallery-section :gallery-items="$galleryItems" />
      <x-reviews-section :reviews="$reviews" />
      <x-collage-section />
      <x-newsletter />
    </div>
    <x-site-footer />
  </main>
@endsection
