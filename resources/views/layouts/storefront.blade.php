<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
  $brandName = store_name();
  $brandTagline = setting('store_tagline');
  $defaultTitle = $brandName.($brandTagline ? ' — '.$brandTagline : ' — Eco-Friendly Kitchenware');
  $pageTitle = trim($__env->yieldContent('title', $defaultTitle));
  $metaDescription = trim($__env->yieldContent('meta_description', 'Handcrafted bamboo kitchenware from Indonesia. Sustainable, biodegradable, and made by artisans.'));
  $metaImage = trim($__env->yieldContent('meta_image', store_logo_url() ?: asset('images/og-default.jpg')));
  $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
  $ogType = trim($__env->yieldContent('og_type', 'website'));
  $twitterHandle = trim((string) (setting('social_twitter') ?? ''));
  $socials = collect([
    setting('social_instagram'),
    setting('social_facebook'),
    setting('social_tiktok'),
    setting('social_whatsapp'),
  ])->filter()->values()->all();
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $metaDescription }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />

<meta property="og:type" content="{{ $ogType }}" />
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:site_name" content="{{ $brandName }}" />
<meta property="og:locale" content="id_ID" />

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
@endphp
<script type="application/ld+json">{!! json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($siteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('head')
</head>
<body class="storefront-body">
@yield('content')
<x-floating-whatsapp />
@stack('scripts')
</body>
</html>
