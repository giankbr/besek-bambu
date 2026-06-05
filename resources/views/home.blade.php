@extends('layouts.storefront')

@php
  $homeTitle = trim((string) setting('seo_home_meta_title', ''));
  $homeDescription = trim((string) setting('seo_home_meta_description', ''));
@endphp
@section('title', $homeTitle !== '' ? $homeTitle : meta_title(store_name(), __('Besek Bambu Handmade untuk Hantaran & Kemasan')))
@section('meta_description', $homeDescription !== '' ? $homeDescription : default_meta_description())

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
      <x-collage-section :gallery-items="$galleryItems" />
      <x-newsletter :gallery-items="$galleryItems" />
    </div>
    <x-site-footer />
  </main>
@endsection
