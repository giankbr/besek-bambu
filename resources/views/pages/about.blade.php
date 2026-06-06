@extends('layouts.storefront')

@section('title', meta_title(__('Tentang'), store_name()))
@section('meta_description', __('Kenali cerita di balik besek bambu handmade kami: proses anyaman tradisional, bahan berkelanjutan, dan komitmen kualitas dari pengrajin lokal Indonesia.'))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container about-section">
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
        <div class="about-main">
          <p class="about-lead">{{ __('Besek Bambu adalah studio kriya asli Indonesia yang menganyam benda sehari-hari dari bambu yang dipanen secara berkelanjutan. Setiap produk dibuat tangan oleh pengrajin yang keluarganya telah menekuni kerajinan ini turun-temurun.') }}</p>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Kerajinan kami') }}</h2>
            <p class="about-body">{{ __('Bambu hanya dipanen setelah matang minimal tiga tahun. Kami bekerja sama dengan petani koperasi yang menanam ulang setiap selesai panen. Bilah bambu dibelah, dikeringkan, dan dianyam dengan tangan, tanpa mesin, tanpa bahan kimia, tanpa jalan pintas.') }}</p>
          </section>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Mengapa bambu') }}</h2>
            <p class="about-body">{{ __('Bambu tumbuh kembali dalam hitungan bulan, bukan dekade. Tidak butuh pupuk, hampir tanpa air, dan menyerap lebih banyak CO₂ daripada kebanyakan kayu keras. Saat sebuah besek akhirnya kembali ke tanah, ia tidak meninggalkan jejak.') }}</p>
          </section>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Standar kami') }}</h2>
            <ul class="about-list">
              <li>{{ __('Bahan 100% alami dan mudah terurai') }}</li>
              <li>{{ __('Pengemasan aman untuk pengiriman ke seluruh Indonesia') }}</li>
              <li>{{ __('Kualitas anyaman dicek sebelum dikirim ke pelanggan') }}</li>
            </ul>
          </section>
        </div>

        <x-store-contact-sidebar class="about-side" />
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
