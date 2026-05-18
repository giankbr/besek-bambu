@extends('layouts.storefront')

@section('title', meta_title(__('Pengaturan profil'), store_name()))

@section('content')
  <x-account-page
    active="profile"
    :crumbs="[
        ['label' => __('Beranda'), 'url' => route('home')],
        ['label' => __('Akun'), 'url' => route('account.index')],
        ['label' => __('Profil')],
    ]"
    eyebrow="{{ __('Pengaturan akun') }}"
  >
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">{!! __('Profil <em>saya</em>') !!}</h1>
    </x-slot:heading>

    @if (session('status') && session('status') !== 'verification-link-sent')
      <div class="cart-flash" role="status">{{ session('status') }}</div>
    @endif

    <x-account-email-verification-alert :user="$user" />

    <section class="confirmation-card account-panel account-profile-form">
      <p class="confirmation-section-title">{{ __('Profil') }}</p>
      <h2 class="account-card-title">{{ __('Perbarui data akun') }}</h2>
      <p class="confirmation-meta account-profile-form__lead">{{ __('Ubah nama dan email yang dipakai untuk pesanan serta notifikasi toko.') }}</p>

      <form method="post" action="{{ route('account.profile.update') }}" class="contact-form">
        @csrf
        @method('patch')

        <label>
          {{ __('Nama') }}
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" />
          @error('name')<span class="form-error">{{ $message }}</span>@enderror
        </label>

        <label>
          {{ __('Email') }}
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" />
          @error('email')<span class="form-error">{{ $message }}</span>@enderror
        </label>

        <div class="account-profile-form__actions">
          <button type="submit" class="hero-cta">{{ __('Simpan perubahan') }}</button>
          <a class="cart-link-btn" href="{{ route('account.index') }}">{{ __('Kembali ke ringkasan') }}</a>
        </div>
      </form>
    </section>
  </x-account-page>
@endsection
