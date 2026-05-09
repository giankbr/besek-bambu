@extends('layouts.storefront')

@section('content')
  <x-navbar />
  <x-hero />
  <x-products-section :products="$products" />
  <x-story-band />
  <x-categories-section :categories="$categories" />
  <x-gallery-section :gallery-items="$galleryItems" />
  <x-reviews-section :reviews="$reviews" />
  <x-collage-section />
  <x-newsletter />
  <x-site-footer />
@endsection
