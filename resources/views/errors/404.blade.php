@extends('layouts.storefront')

@section('title', meta_title('404', __('Halaman tidak ditemukan'), store_name()))

@section('meta_description', __('Halaman yang Anda cari tidak ada atau tautannya sudah tidak berlaku. Kembali ke beranda atau jelajahi katalog besek kami.'))

@push('head')
  <meta name="robots" content="noindex, follow" />
  <style>
    .error-404 {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: clamp(2rem, 6vw, 4rem) var(--page-padding-x, 1rem);
      min-height: min(70dvh, 640px);
    }
    .error-404__code {
      font-family: var(--font-heading, 'Inter', system-ui, sans-serif);
      font-size: clamp(3rem, 10vw, 4.5rem);
      font-weight: 600;
      letter-spacing: -0.04em;
      color: var(--green-deep, #2f4f3e);
      line-height: 1;
      margin-bottom: 0.5rem;
    }
    .error-404__title {
      font-family: var(--font-heading, 'Inter', system-ui, sans-serif);
      font-size: clamp(1.25rem, 3vw, 1.5rem);
      font-weight: 500;
      color: var(--ink, #1c2421);
      margin-bottom: 0.75rem;
    }
    .error-404__lead {
      font-size: 0.9375rem;
      line-height: 1.6;
      color: var(--muted, #6b7268);
      max-width: 28rem;
      margin: 0 auto 2rem;
    }
    .error-404__actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
    }

    .error-404__actions .hero-cta,
    .error-404__actions .sf-btn {
      --btn-radius: 999px;
      padding: 12px 26px;
      font-size: 14px;
    }
  </style>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main" role="main">
    <div class="container error-404">
      <p class="error-404__code">404</p>
      <h1 class="error-404__title">{{ __('Halaman tidak ditemukan') }}</h1>
      <p class="error-404__lead">
        {{ __('Alamat ini tidak ada atau sudah dipindahkan. Periksa penulisan URL, atau kembali ke beranda untuk melanjutkan belanja besek anyaman bambu.') }}
      </p>
      <div class="error-404__actions">
        <a class="hero-cta" href="{{ route('home') }}">{{ __('Ke beranda') }}</a>
        <a class="sf-btn sf-btn--tertiary sf-btn--md" href="{{ route('shop.index') }}">{{ __('Lihat katalog') }}</a>
      </div>
    </div>
  </main>
@endsection
