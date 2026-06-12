@extends('layouts.storefront')

@section('title', meta_title(__('Kebijakan Privasi'), store_name()))
@section('meta_description', __('Kebijakan privasi :store: data yang kami kumpulkan saat Anda berbelanja, membuat akun, atau menghubungi kami, serta hak Anda atas data pribadi.', ['store' => store_name()]))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container legal-section">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Kebijakan Privasi')],
        ]"
        eyebrow="{{ __('Transparansi data') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Kebijakan <em>Privasi</em>') !!}</h1>
      </x-page-head>

      <div class="legal-prose">
        <p class="legal-updated">{{ __('Terakhir diperbarui: :date', ['date' => now()->translatedFormat('d F Y')]) }}</p>

        <p>{{ __(':store ("kami") menghormati privasi Anda. Kebijakan ini menjelaskan data pribadi yang kami kumpulkan melalui situs web, proses pesanan, akun pelanggan, formulir kontak, dan layanan terkait.', ['store' => store_name()]) }}</p>

        <h2>{{ __('1. Data yang kami kumpulkan') }}</h2>
        <ul>
          <li>{{ __('Data identitas & kontak: nama, alamat email, nomor telepon.') }}</li>
          <li>{{ __('Data pesanan: alamat pengiriman, catatan pesanan, riwayat belanja, kupon yang digunakan.') }}</li>
          <li>{{ __('Data akun (jika Anda mendaftar): nama, email, kata sandi terenkripsi.') }}</li>
          <li>{{ __('Pesan kontak: isi formulir hubungi kami yang Anda kirimkan.') }}</li>
          <li>{{ __('Newsletter (jika Anda berlangganan): alamat email.') }}</li>
          <li>{{ __('Data teknis: alamat IP, jenis perangkat/browser, log aktivitas dasar untuk keamanan situs.') }}</li>
        </ul>

        <h2>{{ __('2. Tujuan penggunaan data') }}</h2>
        <ul>
          <li>{{ __('Memproses dan mengirim pesanan Anda.') }}</li>
          <li>{{ __('Menghubungi Anda terkait status pesanan, pembayaran, atau pertanyaan.') }}</li>
          <li>{{ __('Mengelola akun pelanggan dan riwayat pesanan.') }}</li>
          <li>{{ __('Mencegah penipuan, penyalahgunaan, dan gangguan layanan.') }}</li>
          <li>{{ __('Meningkatkan pengalaman belanja dan layanan pelanggan.') }}</li>
          <li>{{ __('Mengirim informasi promo hanya jika Anda setuju berlangganan newsletter.') }}</li>
        </ul>

        <h2>{{ __('3. Pembagian data kepada pihak ketiga') }}</h2>
        <p>{{ __('Kami tidak menjual data pribadi Anda. Data dapat dibagikan hanya kepada pihak yang membantu operasional toko, antara lain:') }}</p>
        <ul>
          <li>{{ __('Midtrans — pemrosesan pembayaran online.') }}</li>
          <li>{{ __('Jasa kurir / penyedia ongkir — pengiriman barang ke alamat Anda.') }}</li>
          <li>{{ __('Penyedia hosting, email, dan infrastruktur IT yang mendukung situs ini.') }}</li>
        </ul>
        <p>{{ __('Mitra tersebut hanya memproses data sesuai instruksi kami dan untuk tujuan layanan yang disebutkan.') }}</p>

        <h2>{{ __('4. Cookie & analitik') }}</h2>
        <p>{{ __('Situs menggunakan cookie sesi untuk keranjang belanja, login, preferensi bahasa, dan keamanan. Jika Google Analytics atau alat analitik serupa diaktifkan, data penggunaan anonim dapat dikumpulkan untuk memahami performa situs. Anda dapat mengatur atau menolak cookie melalui pengaturan browser.') }}</p>

        <h2>{{ __('5. Penyimpanan & keamanan') }}</h2>
        <p>{{ __('Data disimpan selama diperlukan untuk memenuhi tujuan di atas, termasuk kewajiban hukum dan akuntansi pesanan. Kami menerapkan langkah wajar untuk melindungi data dari akses tidak sah, namun tidak ada sistem online yang 100% aman.') }}</p>

        <h2>{{ __('6. Hak Anda') }}</h2>
        <p>{{ __('Sesuai peraturan perlindungan data pribadi yang berlaku di Indonesia, Anda berhak untuk:') }}</p>
        <ul>
          <li>{{ __('Mengetahui dan mengakses data pribadi Anda yang kami simpan.') }}</li>
          <li>{{ __('Memperbarui atau memperbaiki data yang tidak akurat.') }}</li>
          <li>{{ __('Meminta penghapusan data dalam kondisi tertentu.') }}</li>
          <li>{{ __('Menarik persetujuan untuk komunikasi pemasaran.') }}</li>
        </ul>
        <p>
          {{ __('Untuk mengajukan permintaan terkait data pribadi, hubungi kami melalui') }}
          <a href="{{ route('contact') }}">{{ __('halaman kontak') }}</a>
          @if (store_email())
            {{ __('atau email') }} <x-store-email-link />.
          @else
            .
          @endif
        </p>

        <h2>{{ __('7. Perubahan kebijakan') }}</h2>
        <p>{{ __('Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini. Tanggal pembaruan terakhir dicantumkan di atas.') }}</p>

        <p>
          <a href="{{ route('terms') }}">{{ __('Baca juga Syarat & Ketentuan') }}</a>
        </p>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
