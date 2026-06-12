@extends('layouts.storefront')

@section('title', meta_title(__('FAQ Besek Bambu — Perawatan, Pengiriman & Grosir'), store_name()))
@section('meta_description', __('Pertanyaan umum tentang besek bambu: cara perawatan anyaman, estimasi produksi, pengiriman, pembayaran, pesanan grosir, dan custom logo.'))

@php
  $faqs = store_faqs();
@endphp

@push('head')
  <script type="application/ld+json">{!! json_encode(faq_page_schema($faqs), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container faq-section">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('FAQ')],
        ]"
        eyebrow="{{ __('Pusat bantuan') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Pertanyaan yang <em>sering diajukan</em>') !!}</h1>
      </x-page-head>

      <div class="faq-list">
        <x-faq-list :faqs="$faqs" />
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
