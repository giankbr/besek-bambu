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
        {{ $slot }}
      </div>
    </div>
  </section>

  <x-site-footer />
</main>
