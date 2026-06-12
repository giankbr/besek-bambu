<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
@include('partials.theme-init')
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
  $brandName = store_name();
  $brandTagline = setting('store_tagline');
  $defaultTitle = meta_title($brandName, $brandTagline ?: __('Besek Handmade untuk Hantaran & Kemasan'));
  $pageTitle = trim($__env->yieldContent('title', $defaultTitle));
  $metaDescription = trim($__env->yieldContent('meta_description', default_meta_description()));
  $metaImage = trim($__env->yieldContent('meta_image', default_og_image_url() ?: asset('images/og-default.jpg')));
  $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
  $ogType = trim($__env->yieldContent('og_type', 'website'));
  $robots = trim($__env->yieldContent('meta_robots', 'index,follow,max-image-preview:large'));
  $twitterHandle = twitter_handle();
  $activeLocale = app()->getLocale();
  $alternateLocale = $activeLocale === 'en' ? 'id_ID' : 'en_US';
  $googleVerification = trim((string) setting('seo_google_site_verification', ''));
  $bingVerification = trim((string) setting('seo_bing_site_verification', ''));
  $googleAnalyticsId = trim((string) setting('seo_google_analytics_id', config('services.google.analytics_id', '')));
@endphp

<title>{!! seo_meta_text($pageTitle) !!}</title>
@include('partials.favicon')
<meta name="description" content="{!! seo_meta_text($metaDescription) !!}" />
<meta name="robots" content="{{ $robots }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />
<link rel="alternate" hreflang="id" href="{{ localized_url('id') }}" />
<link rel="alternate" hreflang="en" href="{{ localized_url('en') }}" />
<link rel="alternate" hreflang="x-default" href="{{ localized_url('id') }}" />
@if ($googleVerification !== '')
  <meta name="google-site-verification" content="{{ $googleVerification }}" />
@endif
@if ($bingVerification !== '')
  <meta name="msvalidate.01" content="{{ $bingVerification }}" />
@endif

<meta property="og:type" content="{{ $ogType }}" />
<meta property="og:title" content="{!! seo_meta_text($pageTitle) !!}" />
<meta property="og:description" content="{!! seo_meta_text($metaDescription) !!}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:site_name" content="{{ $brandName }}" />
<meta property="og:locale" content="{{ $activeLocale === 'en' ? 'en_US' : 'id_ID' }}" />
<meta property="og:locale:alternate" content="{{ $alternateLocale }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{!! seo_meta_text($pageTitle) !!}" />
<meta name="twitter:description" content="{!! seo_meta_text($metaDescription) !!}" />
<meta name="twitter:image" content="{{ $metaImage }}" />
@if ($twitterHandle)
  <meta name="twitter:site" content="{{ $twitterHandle }}" />
@endif

@php
  $schemaExtraJson = trim((string) $__env->yieldContent('schema_graph_extra'));
  $schemaExtra = [];
  if ($schemaExtraJson !== '') {
      try {
          $decoded = json_decode($schemaExtraJson, true, 512, JSON_THROW_ON_ERROR);
          $schemaExtra = is_array($decoded) ? $decoded : [];
      } catch (JsonException) {
          $schemaExtra = [];
      }
  }
@endphp
<script type="application/ld+json">{!! seo_json_ld(seo_schema_graph($schemaExtra)) !!}</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
@vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
@if ($googleAnalyticsId !== '')
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @json($googleAnalyticsId));
  </script>
@endif
@stack('head')
</head>
<body class="storefront-body">
@yield('content')
<x-confirm-dialog />
<x-floating-whatsapp />
@stack('scripts')
</body>
</html>
