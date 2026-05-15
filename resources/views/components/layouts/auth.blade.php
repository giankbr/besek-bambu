@props([
  'title' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="auth-flux-light" data-auth-light="1">
<head>
  <script>
    document.documentElement.classList.remove('dark')
    document.documentElement.style.colorScheme = 'light'
  </script>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>
    @if (filled($title))
      {{ $title }} — {{ store_name() }}
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

  @vite(['resources/css/app.css', 'resources/css/storefront.css', 'resources/js/app.js'])
</head>
<body class="storefront-body auth-storefront-page">
  <x-navbar />
  <main id="main-content" class="page-main auth-storefront-main">
    <div class="container auth-storefront-inner">
      <div class="auth-storefront-card">
        {{ $slot }}
      </div>
    </div>
  </main>
  <x-floating-whatsapp />

  @persist('toast')
    <flux:toast.group>
      <flux:toast />
    </flux:toast.group>
  @endpersist

  @fluxScripts
  <script>
    ;(function () {
      const root = document.documentElement
      root.classList.remove('dark')
      root.style.colorScheme = 'light'
      if (root.dataset.authLight !== '1') return
      const observer = new MutationObserver(() => {
        if (root.classList.contains('dark')) {
          root.classList.remove('dark')
        }
      })
      observer.observe(root, { attributes: true, attributeFilter: ['class'] })
    })()
  </script>
</body>
</html>
