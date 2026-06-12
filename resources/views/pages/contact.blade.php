@extends('layouts.storefront')

@section('title', meta_title(__('Kontak'), store_name()))
@section('meta_description', __('Hubungi :store untuk pemesanan besek bambu, kebutuhan grosir, custom logo, atau pertanyaan seputar produk dan pengiriman.', ['store' => store_name()]))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container contact-section">
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
            {{-- Honeypot: hidden from humans; bots that fill it are dropped server-side. --}}
            <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden" aria-hidden="true">
              <label>Website
                <input type="text" name="website" tabindex="-1" autocomplete="off" />
              </label>
            </div>
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
              <input type="text" name="subject" value="{{ old('subject', request('subject')) }}" required />
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

        <x-store-contact-sidebar class="contact-side" />
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
