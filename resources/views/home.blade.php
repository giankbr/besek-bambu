@extends('layouts.storefront')

@php
  $homeTitle = trim((string) setting('seo_home_meta_title', ''));
  $homeDescription = trim((string) setting('seo_home_meta_description', ''));
  $resolvedHomeTitle = $homeTitle !== '' ? $homeTitle : default_home_meta_title();
  $resolvedHomeDescription = $homeDescription !== '' ? $homeDescription : default_meta_description();
@endphp
@section('title', $resolvedHomeTitle)
@section('meta_description', $resolvedHomeDescription)
@section('schema_graph_extra')
@php
  $schemaGraphExtra = [
    seo_webpage_node(url('/'), $resolvedHomeTitle, $resolvedHomeDescription, default_og_image_url()),
  ];

  foreach ($products as $product) {
    $schemaGraphExtra[] = seo_product_schema_node($product);
  }
@endphp
{!! json_encode($schemaGraphExtra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
@endsection

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
