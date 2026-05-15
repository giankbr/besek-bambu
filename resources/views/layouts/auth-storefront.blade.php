<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>
    @hasSection('title')
      @yield('title')
    @else
      {{ store_name() }}
    @endif
  </title>

  <link rel="icon" href="/favicon.ico" sizes="any" />
  <link rel="icon" href="/favicon.svg" type="image/svg+xml" />
  <link rel="apple-touch-icon" href="/apple-touch-icon.png" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap"
    rel="stylesheet"
  />

  @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
</head>
<body class="storefront-body auth-storefront-page">
  <main id="main-content" class="page-main auth-storefront-main">
    <div class="auth-storefront-inner">
      <div class="auth-storefront-card">
        <x-auth-brand />
        @yield('auth')
      </div>
    </div>
  </main>
  <x-floating-whatsapp />
</body>
</html>
