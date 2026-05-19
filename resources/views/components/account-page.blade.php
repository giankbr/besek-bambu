@props([
    'active' => 'overview',
    'crumbs' => [],
    'eyebrow' => null,
])

<x-navbar />
<main id="main-content" class="page-main">
  <section class="container">
    <x-page-head :crumbs="$crumbs" :eyebrow="$eyebrow">
      @isset($heading)
        {{ $heading }}
      @endisset
    </x-page-head>

    <div class="account-grid">
      <x-account-nav :active="$active" />

      <div class="account-main">
        @if (session('status') && session('status') !== 'verification-link-sent')
          <div class="cart-flash" role="status">{{ session('status') }}</div>
        @endif

        {{ $slot }}
      </div>
    </div>
  </section>

  <x-site-footer />
</main>
