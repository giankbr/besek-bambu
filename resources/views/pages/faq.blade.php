@extends('layouts.storefront')

@section('title', 'FAQ — Besek Bambu')
@section('meta_description', __('Pertanyaan umum tentang besek bambu: cara perawatan, metode pembayaran, estimasi pengiriman, dan kebijakan pemesanan.'))

@php
  $faqs = [
    [
      'q' => __('Bagaimana produk Anda dibuat?'),
      'a' => __('Setiap produk dianyam tangan oleh pengrajin di Yogyakarta dari bambu yang dipanen secara alami. Produksi memakan waktu 2–7 hari per item, tergantung ukuran dan kerumitan.'),
    ],
    [
      'q' => __('Bagaimana cara merawat besek saya?'),
      'a' => __('Lap dengan kain lembap dan keringkan di tempat teduh. Hindari paparan air atau sinar matahari langsung dalam waktu lama. Dengan perawatan yang tepat, besek bisa awet bertahun-tahun.'),
    ],
    [
      'q' => __('Metode pembayaran apa saja yang diterima?'),
      'a' => __('Kami menerima semua kartu kredit utama, transfer bank (BCA, BNI, Mandiri, Permata), e-wallet (GoPay, OVO, ShopeePay), dan QRIS — diproses aman via Midtrans.'),
    ],
    [
      'q' => __('Berapa lama pengiriman?'),
      'a' => __('Di Jawa, 2–4 hari kerja. Luar Jawa, 4–7 hari kerja. Pengiriman internasional tersedia atas permintaan.'),
    ],
    [
      'q' => __('Bisakah saya mengembalikan produk?'),
      'a' => __('Ya — kami menerima pengembalian dalam 14 hari setelah barang diterima untuk produk yang belum dipakai dan dalam kondisi asli. Pesanan custom tidak dapat dikembalikan.'),
    ],
    [
      'q' => __('Apakah ada harga grosir?'),
      'a' => __('Tentu! Hubungi kami untuk pesanan 25 buah atau lebih — kami senang bekerja sama dengan restoran, peritel, dan event planner.'),
    ],
    [
      'q' => __('Apakah produk Anda aman untuk makanan?'),
      'a' => __('Ya. Kami tidak memakai pernis, pewarna, atau bahan finishing. Bambu dicuci, dikeringkan, dan dianyam — tidak ada yang lain.'),
    ],
  ];
@endphp

@push('head')
  @php
    $faqSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => array_map(fn ($f) => [
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text' => $f['a'],
        ],
      ], $faqs),
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
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
        @foreach ($faqs as $faq)
          <details class="faq-item">
            <summary>{{ $faq['q'] }}</summary>
            <p>{{ $faq['a'] }}</p>
          </details>
        @endforeach
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
