@php
  $googleAnalyticsId = trim((string) setting('seo_google_analytics_id', config('services.google.analytics_id', '')));
@endphp
@if ($googleAnalyticsId !== '')
<link rel="preconnect" href="https://www.googletagmanager.com">
<link rel="dns-prefetch" href="https://www.googletagmanager.com">
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', @json($googleAnalyticsId));
</script>
@endif
