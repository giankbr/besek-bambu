@extends('layouts.storefront')

@section('title', '404 — Halaman tidak ditemukan — '.store_name())

@section('meta_description', 'Halaman yang Anda cari tidak ada atau tautannya sudah tidak berlaku. Kembali ke beranda atau jelajahi katalog besek kami.')

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
    .error-404__badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 88px;
      height: 88px;
      border-radius: 24px;
      background: var(--green-soft, #e9efe2);
      color: var(--green-deep, #2c4a3a);
      font-size: 2.5rem;
      line-height: 1;
      margin-bottom: 1.5rem;
      box-shadow: 0 12px 32px rgba(44, 74, 58, 0.12);
    }
    .error-404__code {
      font-family: var(--font-heading, 'Sora', system-ui, sans-serif);
      font-size: clamp(3rem, 10vw, 4.5rem);
      font-weight: 600;
      letter-spacing: -0.04em;
      color: var(--green-deep, #2c4a3a);
      line-height: 1;
      margin-bottom: 0.5rem;
    }
    .error-404__title {
      font-family: var(--font-heading, 'Sora', system-ui, sans-serif);
      font-size: clamp(1.25rem, 3vw, 1.5rem);
      font-weight: 500;
      color: var(--ink, #1f2a26);
      margin-bottom: 0.75rem;
    }
    .error-404__lead {
      font-size: 0.9375rem;
      line-height: 1.6;
      color: var(--muted, #6b7670);
      max-width: 28rem;
      margin: 0 auto 2rem;
    }
    .error-404__actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      justify-content: center;
    }
    .error-404__btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.65rem 1.35rem;
      border-radius: 999px;
      font-size: 0.875rem;
      font-weight: 500;
      text-decoration: none;
      transition: opacity 0.15s ease, transform 0.1s ease;
    }
    .error-404__btn:hover { opacity: 0.92; transform: translateY(-1px); }
    .error-404__btn--primary {
      background: var(--green-deep, #2c4a3a);
      color: #fff;
    }
    .error-404__btn--ghost {
      background: var(--card, #fff);
      color: var(--ink, #1f2a26);
      border: 1px solid var(--line, #e7e3d8);
    }
  </style>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main" role="main">
    <div class="container error-404">
      <div class="error-404__badge" aria-hidden="true" title="Besek">🧺</div>
      <p class="error-404__code">404</p>
      <h1 class="error-404__title">Halaman tidak ditemukan</h1>
      <p class="error-404__lead">
        Alamat ini tidak ada atau sudah dipindahkan. Periksa penulisan URL, atau kembali ke beranda untuk melanjutkan belanja besek anyaman bambu.
      </p>
      <div class="error-404__actions">
        <a class="error-404__btn error-404__btn--primary" href="{{ route('home') }}">Ke beranda</a>
        <a class="error-404__btn error-404__btn--ghost" href="{{ route('shop.index') }}">Lihat katalog</a>
      </div>
    </div>
  </main>
@endsection
