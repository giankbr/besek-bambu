@extends('layouts.storefront')

@section('title', __('Kontak').' — '.store_name())
@section('meta_description', __('Hubungi :store untuk pemesanan besek bambu, kebutuhan grosir, custom logo, atau pertanyaan seputar produk dan pengiriman.', ['store' => store_name()]))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Kontak')],
        ]"
        eyebrow="{{ __('Halo') }}"
      >
        <h1 class="section-title page-head__title cart-title">{!! __('Hubungi <em>kami</em>') !!}</h1>
      </x-page-head>

      <div class="contact-grid">
        <div>
          <p class="confirmation__lead" style="margin-bottom:1.5rem">{{ __('Punya pertanyaan tentang produk, ingin pesan dalam jumlah besar, atau sekadar menyapa? Kami senang mendengar dari Anda.') }}</p>

          @if (session('status'))
            <div class="confirmation-card" style="background:#eef7ee">
              <p class="confirmation-meta" style="margin:0">{{ session('status') }}</p>
            </div>
          @endif

          <form method="post" action="{{ route('contact.submit') }}" class="contact-form">
            @csrf
            <div class="checkout-row">
              <label>
                {{ __('Nama') }}
                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required />
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
              </label>
              <label>
                {{ __('Email') }}
                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required />
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
              </label>
            </div>
            <label>
              {{ __('Subjek') }}
              <input type="text" name="subject" value="{{ old('subject') }}" required />
              @error('subject')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <label>
              {{ __('Pesan') }}
              <textarea name="message" rows="6" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>
              @error('message')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="hero-cta">{{ __('Kirim pesan') }}</button>
          </form>
        </div>

        @php
          $contactEmail = store_email();
          $contactPhone = store_phone();
          $contactAddress = store_address();
        @endphp
        <aside class="contact-side">
          @if ($contactAddress)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Workshop') }}</h3>
              @foreach (preg_split('/\r\n|\r|\n/', $contactAddress) as $line)
                @if (trim($line) !== '')
                  <p class="confirmation-meta">{{ $line }}</p>
                @endif
              @endforeach
            </div>
          @else
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Workshop') }}</h3>
              <p class="confirmation-meta">Jl. Kasongan, Bantul</p>
              <p class="confirmation-meta">Yogyakarta, Indonesia 55184</p>
            </div>
          @endif

          <div class="confirmation-card">
            <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Jam buka') }}</h3>
            <p class="confirmation-meta">{{ __('Sen–Sab · 09.00–17.00') }}</p>
            <p class="confirmation-meta">{{ __('Minggu · Tutup') }}</p>
          </div>

          @if ($contactEmail)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Email') }}</h3>
              <p class="confirmation-meta"><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
            </div>
          @endif

          @if ($contactPhone)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">{{ __('Telepon') }}</h3>
              <p class="confirmation-meta">{{ $contactPhone }}</p>
            </div>
          @endif
        </aside>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
