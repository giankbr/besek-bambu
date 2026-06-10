@props([
  'class' => '',
])

@php
  $contactEmail = store_email();
  $contactPhone = store_phone();
  $contactAddress = store_address();
@endphp

<aside {{ $attributes->merge(['class' => trim('store-contact-sidebar '.$class)]) }}>
  @if ($contactAddress)
    <div class="confirmation-card">
      <h3 class="confirmation-section-title store-contact-sidebar__title">{{ __('Lokasi kami') }}</h3>
      @foreach (preg_split('/\r\n|\r|\n/', $contactAddress) as $line)
        @if (trim($line) !== '')
          <p class="confirmation-meta">{{ $line }}</p>
        @endif
      @endforeach
    </div>
  @else
    <div class="confirmation-card">
      <h3 class="confirmation-section-title store-contact-sidebar__title">{{ __('Lokasi kami') }}</h3>
      <p class="confirmation-meta">{{ store_location_area() }}</p>
    </div>
  @endif

  <div class="confirmation-card">
    <h3 class="confirmation-section-title store-contact-sidebar__title">{{ __('Jam buka') }}</h3>
    <p class="confirmation-meta">{{ __('Sen–Sab · 09.00–17.00') }}</p>
    <p class="confirmation-meta">{{ __('Minggu · Tutup') }}</p>
  </div>

  @if ($contactEmail)
    <div class="confirmation-card">
      <h3 class="confirmation-section-title store-contact-sidebar__title">{{ __('Email') }}</h3>
      <p class="confirmation-meta"><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
    </div>
  @endif

  @if ($contactPhone)
    <div class="confirmation-card">
      <h3 class="confirmation-section-title store-contact-sidebar__title">{{ __('Telepon') }}</h3>
      <p class="confirmation-meta">{{ $contactPhone }}</p>
    </div>
  @endif
</aside>
