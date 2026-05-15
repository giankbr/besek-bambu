@extends('layouts.storefront')

@section('title', 'Profile settings — Besek Bambu')

@section('content')
  <x-account-page
    active="profile"
    :crumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Akun', 'url' => route('account.index')],
        ['label' => 'Profil'],
    ]"
    eyebrow="Pengaturan akun"
  >
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">Profil <em>saya</em></h1>
    </x-slot:heading>

    @if (session('status'))
      <div class="cart-flash" role="status">{{ session('status') }}</div>
    @endif

    <section class="confirmation-card account-panel account-profile-form">
      <p class="confirmation-section-title">Profile</p>
      <h2 class="account-card-title">Perbarui data akun</h2>
      <p class="confirmation-meta account-profile-form__lead">Ubah nama dan email yang dipakai untuk pesanan serta notifikasi toko.</p>

      <form method="post" action="{{ route('account.profile.update') }}" class="contact-form">
        @csrf
        @method('patch')

        <label>
          Nama
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" />
          @error('name')<span class="form-error">{{ $message }}</span>@enderror
        </label>

        <label>
          Email
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" />
          @error('email')<span class="form-error">{{ $message }}</span>@enderror
        </label>

        <div class="account-profile-form__actions">
          <button type="submit" class="hero-cta">Simpan perubahan</button>
          <a class="cart-link-btn" href="{{ route('account.index') }}">Kembali ke overview</a>
        </div>
      </form>
    </section>
  </x-account-page>
@endsection
