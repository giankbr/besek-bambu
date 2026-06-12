@extends('layouts.storefront')

@section('title', meta_title(__('Grosir & Custom Besek Bambu'), store_name()))
@section('meta_description', __('Pesan besek bambu grosir mulai 25 pcs, custom logo & ukuran untuk UMKM, toko oleh-oleh, hampers, dan acara. Pengrajin di :location, kirim seluruh Indonesia.', ['location' => store_location_area()]))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container about-section wholesale-section">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Grosir & Custom')],
        ]"
        eyebrow="{{ __('Untuk bisnis & acara') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Grosir & <em>custom</em> besek bambu') !!}</h1>
      </x-page-head>

      <div class="about-grid">
        <div class="about-main">
          <p class="about-lead">{{ __('Butuh besek bambu dalam jumlah banyak untuk hampers brand, seserahan, parcel Lebaran, atau kemasan produk UMKM? Kami siap melayani pesanan grosir dan custom langsung dari pengrajin di :location.', ['location' => store_location_area()]) }}</p>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Pesanan grosir') }}</h2>
            <p class="about-body">{{ __('Minimal pesanan grosir mulai 25 buah per model. Harga unit menyesuaikan volume: semakin banyak, semakin hemat. Cocok untuk toko oleh-oleh, event organizer, catering, dan brand yang rutin butuh kemasan ramah lingkungan.') }}</p>
            <ul class="about-list">
              <li>{{ __('Harga tier otomatis di katalog untuk pembelian volume') }}</li>
              <li>{{ __('Produksi 2 sampai 7 hari per item, tergantung ukuran dan jumlah') }}</li>
              <li>{{ __('Pengiriman aman ke seluruh Indonesia') }}</li>
            </ul>
          </section>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Custom logo & ukuran') }}</h2>
            <p class="about-body">{{ __('Ingin besek dengan logo brand, pita khusus, atau ukuran di luar katalog? Tim kami bisa diskusikan kebutuhan Anda, mulai dari mockup sederhana hingga pesanan khusus untuk pernikahan atau acara korporat.') }}</p>
            <ul class="about-list">
              <li>{{ __('Ukuran custom sesuai brief') }}</li>
              <li>{{ __('Branding: stiker logo, pita, atau hiasan tambahan') }}</li>
              <li>{{ __('Konsultasi gratis sebelum produksi') }}</li>
            </ul>
          </section>

          <section class="about-block">
            <h2 class="about-block__title">{{ __('Siapa yang cocok') }}</h2>
            <ul class="about-list">
              <li>{{ __('UMKM kuliner & F&B yang ingin kemasan premium') }}</li>
              <li>{{ __('Penyelenggara hampers & parcel hari raya') }}</li>
              <li>{{ __('Wedding planner & seserahan pernikahan') }}</li>
              <li>{{ __('Corporate gifting & merchandise ramah lingkungan') }}</li>
            </ul>
          </section>

          <div class="wholesale-cta">
            <a href="{{ route('contact') }}?subject={{ urlencode(__('Pertanyaan grosir / custom besek bambu')) }}" class="hero-cta">{{ __('Minta penawaran') }}</a>
            <a href="{{ route('shop.index') }}" class="cart-link-btn">{{ __('Lihat katalog') }}</a>
          </div>
        </div>

        <x-store-contact-sidebar class="about-side" />
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
