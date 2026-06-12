@props(['email' => null])

@php
  $email = trim((string) ($email ?? store_email() ?? ''));
@endphp

@if ($email !== '')
  <!--email_off--><a {{ $attributes->merge(['href' => 'mailto:'.$email]) }}>{{ $email }}</a><!--/email_off-->
@endif
