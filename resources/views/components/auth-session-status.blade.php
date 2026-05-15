@props([
    'status',
])

@if ($status)
  <p {{ $attributes->class(['auth-storefront-status']) }} role="status">
    {{ $status }}
  </p>
@endif
