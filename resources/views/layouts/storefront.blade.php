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
  $googleAnalyticsId = trim((string) setting('seo_google_analytics_id', ''));
  $storeAddress = preg_replace('/\s+/', ' ', trim((string) setting('store_address')));
  $socials = collect([
    setting('social_instagram'),
    setting('social_facebook'),
    setting('social_tiktok'),
    setting('social_whatsapp'),
  ])->filter()->values()->all();
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}" />
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
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:site_name" content="{{ $brandName }}" />
<meta property="og:locale" content="{{ $activeLocale === 'en' ? 'en_US' : 'id_ID' }}" />
<meta property="og:locale:alternate" content="{{ $alternateLocale }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $pageTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $metaImage }}" />
@if ($twitterHandle)
  <meta name="twitter:site" content="{{ $twitterHandle }}" />
@endif

@php
  $orgSchema = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $brandName,
    'url' => url('/'),
    'logo' => store_logo_url() ?: null,
    'email' => store_email() ?: null,
    'telephone' => store_phone() ?: null,
    'sameAs' => count($socials) > 0 ? $socials : null,
  ]);
  $siteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'url' => url('/'),
    'name' => $brandName,
    'potentialAction' => [
      '@type' => 'SearchAction',
      'target' => url('/shop').'?q={search_term_string}',
      'query-input' => 'required name=search_term_string',
    ],
  ];
  $localBusinessSchema = array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => $brandName,
    'url' => url('/'),
    'image' => $metaImage ?: null,
    'telephone' => store_phone() ?: null,
    'email' => store_email() ?: null,
    'address' => $storeAddress !== '' ? [
      '@type' => 'PostalAddress',
      'streetAddress' => $storeAddress,
      'addressCountry' => 'ID',
    ] : null,
    'sameAs' => count($socials) > 0 ? $socials : null,
  ]);
@endphp
<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($siteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($localBusinessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

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
