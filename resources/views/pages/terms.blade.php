@extends('layouts.storefront')

@section('title', meta_title(__('Syarat & Ketentuan'), store_name()))
@section('meta_description', __('Syarat dan ketentuan belanja di :store: pesanan, pembayaran, pengiriman, pengembalian barang, dan ketentuan pesanan custom besek bambu.', ['store' => store_name()]))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container legal-section">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Syarat & Ketentuan')],
        ]"
        eyebrow="{{ __('Ketentuan belanja') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Syarat & <em>Ketentuan</em>') !!}</h1>
      </x-page-head>

      <div class="legal-prose">
        <p class="legal-updated">{{ __('Terakhir diperbarui: :date', ['date' => now()->translatedFormat('d F Y')]) }}</p>

        <p>{{ __('Dengan mengakses situs :store dan melakukan pemesanan, Anda setuju dengan syarat dan ketentuan berikut. Mohon baca dengan saksama sebelum checkout.', ['store' => store_name()]) }}</p>

        <h2>{{ __('1. Ketentuan umum') }}</h2>
        <p>{{ __('Produk yang dijual adalah besek bambu handmade. Variasi warna, ukuran, dan anyaman wajar terjadi karena sifat kerajinan tangan. Foto produk bersifat ilustrasi; detail minor dapat sedikit berbeda dari unit yang dikirim.') }}</p>

        <h2>{{ __('2. Akun pelanggan') }}</h2>
        <ul>
          <li>{{ __('Anda bertanggung jawab menjaga kerahasiaan akun dan kata sandi.') }}</li>
          <li>{{ __('Informasi yang Anda berikan harus benar dan dapat dihubungi.') }}</li>
          <li>{{ __('Kami berhak menangguhkan akun jika terindikasi penyalahgunaan atau penipuan.') }}</li>
        </ul>

        <h2>{{ __('3. Pesanan & harga') }}</h2>
        <ul>
          <li>{{ __('Harga tercantum dalam Rupiah (IDR) dan dapat berubah tanpa pemberitahuan sebelum checkout.') }}</li>
          <li>{{ __('Pesanan dianggap masuk setelah Anda menyelesaikan proses checkout dan kami menerima konfirmasi pembayaran (jika berlaku).') }}</li>
          <li>{{ __('Stok terbatas. Jika stok habis setelah pesanan dibuat, kami akan menghubungi Anda untuk penggantian atau pengembalian dana.') }}</li>
          <li>{{ __('Harga grosir / tier volume mengikuti ketentuan di halaman produk atau penawaran tertulis dari kami.') }}</li>
        </ul>

        <h2>{{ __('4. Pembayaran') }}</h2>
        <p>{{ __('Pembayaran online diproses melalui Midtrans (kartu, transfer bank, e-wallet, QRIS, dan metode lain yang tersedia). Pesanan dapat dibatalkan otomatis jika pembayaran tidak diselesaikan dalam batas waktu yang ditentukan gateway pembayaran.') }}</p>

        <h2>{{ __('5. Pengiriman') }}</h2>
        <ul>
          <li>{{ __('Ongkos kirim dihitung berdasarkan tujuan, berat, dan layanan kurir yang Anda pilih saat checkout.') }}</li>
          <li>{{ __('Estimasi produksi handmade biasanya 2–7 hari per item, ditambah waktu pengiriman kurir.') }}</li>
          <li>{{ __('Opsi ambil sendiri (pickup) tersedia jika diaktifkan di toko, tanpa biaya pengiriman.') }}</li>
          <li>{{ __('Pastikan alamat pengiriman lengkap dan benar. Keterlambatan akibat data alamat salah bukan tanggung jawab kami.') }}</li>
        </ul>

        <h2>{{ __('6. Pengembalian & pembatalan') }}</h2>
        <ul>
          <li>{{ __('Pengembalian diterima dalam 14 hari setelah barang diterima, untuk produk yang belum dipakai dan dalam kondisi asli.') }}</li>
          <li>{{ __('Biaya pengiriman kembali (retur) ditanggung pembeli, kecuali kesalahan ada pada kami.') }}</li>
          <li>{{ __('Pesanan custom (logo, ukuran khusus, pesanan personal) tidak dapat dikembalikan kecuali ada cacat produksi dari pihak kami.') }}</li>
          <li>{{ __('Pembatalan pesanan yang sudah dibayar akan diproses pengembalian dananya sesuai kebijakan pembayaran.') }}</li>
        </ul>

        <h2>{{ __('7. Produk custom & grosir') }}</h2>
        <p>{{ __('Pesanan grosir dan custom memerlukan konfirmasi detail (jumlah, desain, waktu produksi) sebelum produksi dimulai. Perubahan setelah produksi dimulai mungkin tidak dapat dilakukan. Minimal pesanan grosir mengikuti informasi di halaman') }} <a href="{{ route('wholesale') }}">{{ __('Grosir & Custom') }}</a>.</p>

        <h2>{{ __('8. Hak kekayaan intelektual') }}</h2>
        <p>{{ __('Konten situs (teks, foto, desain, logo merek) dilindungi hukum. Dilarang menyalin atau menggunakan tanpa izin tertulis, kecuali untuk keperluan pribadi non-komersial.') }}</p>

        <h2>{{ __('9. Batasan tanggung jawab') }}</h2>
        <p>{{ __('Kami berupaya menampilkan informasi produk seakurat mungkin. Kami tidak bertanggung jawab atas kerugian tidak langsung akibat keterlambatan kurir, force majeure, atau gangguan teknis di luar kendali wajar kami.') }}</p>

        <h2>{{ __('10. Hukum yang berlaku') }}</h2>
        <p>{{ __('Syarat ini diatur oleh hukum Republik Indonesia. Sengketa akan diselesaikan secara musyawarah terlebih dahulu; jika tidak tercapai, melalui jalur hukum yang berlaku.') }}</p>

        <h2>{{ __('11. Hubungi kami') }}</h2>
        <p>
          {{ __('Pertanyaan tentang syarat ini dapat disampaikan melalui') }}
          <a href="{{ route('contact') }}">{{ __('halaman kontak') }}</a>
          @if (store_email())
            {{ __('atau') }} <x-store-email-link />.
          @else
            .
          @endif
        </p>

        <p>
          <a href="{{ route('privacy') }}">{{ __('Baca juga Kebijakan Privasi') }}</a>
        </p>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
