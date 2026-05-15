@extends('layouts.storefront')

@section('title', __('Tentang').' — Besek Bambu')
@section('meta_description', __('Kenali cerita di balik besek bambu handmade kami: proses anyaman tradisional, bahan berkelanjutan, dan komitmen kualitas dari pengrajin lokal Indonesia.'))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Tentang kami')],
        ]"
        eyebrow="{{ __('Cerita kami') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Dibuat dengan <em>tradisi</em>') !!}</h1>
      </x-page-head>

      <div class="about-grid">
        <div>
          <p class="about-lead">{{ __('Besek Bambu adalah studio kriya asli Indonesia yang menganyam benda sehari-hari dari bambu yang dipanen secara berkelanjutan. Setiap produk dibuat tangan oleh pengrajin yang keluarganya telah menekuni kerajinan ini turun-temurun.') }}</p>

          <h2 class="confirmation-section-title" style="margin-top:2rem">{{ __('Kerajinan kami') }}</h2>
          <p class="about-body">{{ __('Bambu hanya dipanen setelah matang minimal tiga tahun. Kami bekerja sama dengan petani koperasi yang menanam ulang setiap selesai panen. Bilah bambu dibelah, dikeringkan, dan dianyam dengan tangan — tanpa mesin, tanpa bahan kimia, tanpa jalan pintas.') }}</p>

          <h2 class="confirmation-section-title" style="margin-top:1.5rem">{{ __('Mengapa bambu') }}</h2>
          <p class="about-body">{{ __('Bambu tumbuh kembali dalam hitungan bulan, bukan dekade. Tidak butuh pupuk, hampir tanpa air, dan menyerap lebih banyak CO₂ daripada kebanyakan kayu keras. Saat sebuah besek akhirnya kembali ke tanah, ia tidak meninggalkan jejak.') }}</p>

          <h2 class="confirmation-section-title" style="margin-top:1.5rem">{{ __('Janji kami') }}</h2>
          <ul class="about-list">
            <li>{{ __('Bahan 100% alami dan mudah terurai') }}</li>
            <li>{{ __('Upah yang adil untuk setiap pengrajin yang bekerja dengan kami') }}</li>
            <li>{{ __('Pengiriman netral karbon di seluruh Indonesia') }}</li>
            <li>{{ __('Garansi perbaikan seumur hidup untuk setiap produk yang kami jual') }}</li>
          </ul>
        </div>

        <aside class="about-side">
          <div class="confirmation-card">
            <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Kunjungi workshop kami') }}</h3>
            <p class="confirmation-meta">{{ __('Yogyakarta, Indonesia') }}</p>
            <p class="confirmation-meta">{{ __('Buka Sen–Sab · 09.00–17.00') }}</p>
            <a class="cart-link-btn" href="{{ route('contact') }}">{{ __('Hubungi kami') }} →</a>
          </div>
        </aside>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
