@props([
  'title',
  'description',
  'eyebrow' => null,
])

<header class="auth-storefront-intro">
  <p class="eyebrow auth-storefront-eyebrow">{{ $eyebrow ?? __('Toko online') }}</p>
  <h1 class="section-title auth-storefront-heading">{{ $title }}</h1>
  <p class="auth-storefront-lead">{{ $description }}</p>
</header>
