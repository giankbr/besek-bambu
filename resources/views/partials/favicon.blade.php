@php $favicon = store_logo_url(); @endphp
@if ($favicon)
  <link rel="icon" href="{{ $favicon }}" />
@endif
