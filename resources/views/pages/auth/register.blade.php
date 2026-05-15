@extends('layouts.auth-storefront')

@section('title', __('Daftar').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Buat akun pembeli')"
    :description="__('Satu akun untuk berbelanja dan melacak pesanan besek bambu Anda.')"
  />

  <x-auth-session-status :status="session('status')" />

  <form method="POST" action="{{ route('register.store') }}" class="auth-form">
    @csrf

    <label>
      {{ __('Nama lengkap') }}
      <input
        type="text"
        name="name"
        value="{{ old('name') }}"
        required
        autofocus
        autocomplete="name"
        placeholder="{{ __('Nama di paket / invoice') }}"
      />
      @error('name')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <label>
      {{ __('Email') }}
      <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        required
        autocomplete="email"
        placeholder="nama@email.com"
      />
      @error('email')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <label>
      {{ __('Kata sandi') }}
      <x-auth-password
        name="password"
        required
        autocomplete="new-password"
        placeholder="{{ __('Minimal sesuai aturan keamanan') }}"
      />
      @error('password')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <label>
      {{ __('Ulangi kata sandi') }}
      <x-auth-password
        name="password_confirmation"
        required
        autocomplete="new-password"
        placeholder="{{ __('Ulangi kata sandi') }}"
      />
    </label>

    <button type="submit" class="hero-cta auth-form__submit" data-test="register-user-button">
      {{ __('Daftar') }}
    </button>
  </form>

  <p class="auth-storefront-footer-link">
    <span>{{ __('Sudah punya akun?') }}</span>
    <a href="{{ route('login') }}">{{ __('Masuk') }}</a>
  </p>
@endsection
