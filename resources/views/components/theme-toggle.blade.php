@props([
  'class' => '',
  'mobile' => false,
])

@php
  $btnClass = trim('nav-theme ' . ($mobile ? 'nav-mobile__action nav-mobile__action--theme ' : '') . $class);
@endphp

<button
  type="button"
  {{ $attributes->merge(['class' => $btnClass]) }}
  data-theme-toggle
  data-label-dark="{{ __('nav.theme_dark') }}"
  data-label-light="{{ __('nav.theme_light') }}"
  aria-pressed="false"
  aria-label="{{ __('nav.theme_dark') }}"
>
  <x-icons.moon class="nav-theme__icon nav-theme__icon--moon" />
  <x-icons.sun class="nav-theme__icon nav-theme__icon--sun" hidden />
  @if ($mobile)
    <span class="nav-mobile__action-label">{{ __('nav.theme') }}</span>
  @endif
</button>
