<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
  $pageTitle = trim($__env->yieldContent('title', 'Besek Bambu — Eco-Friendly Kitchenware'));
  $metaDescription = trim($__env->yieldContent('meta_description', 'Handcrafted bamboo kitchenware from Indonesia. Sustainable, biodegradable, and made by artisans.'));
  $metaImage = trim($__env->yieldContent('meta_image', asset('images/og-default.jpg')));
  $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />

<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:site_name" content="Besek Bambu" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $pageTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $metaImage }}" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('head')
</head>
<body class="storefront-body">
@yield('content')
@stack('scripts')
</body>
</html>
