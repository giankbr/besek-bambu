@extends('layouts.auth-storefront')

@section('title', __('Lupa kata sandi').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Lupa kata sandi')"
    :description="__('Masukkan email Anda untuk menerima tautan reset kata sandi.')"
  />

  <x-auth-session-status :status="session('status')" />

  <form method="POST" action="{{ route('password.email') }}" class="auth-form">
    @csrf

    <label>
      {{ __('Email') }}
      <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        required
        autofocus
        autocomplete="email"
        placeholder="nama@email.com"
      />
      @error('email')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <button type="submit" class="hero-cta auth-form__submit" data-test="email-password-reset-link-button">
      {{ __('Kirim tautan reset') }}
    </button>
  </form>

  <p class="auth-storefront-footer-link">
    <a href="{{ route('login') }}">{{ __('Kembali ke masuk') }}</a>
  </p>
@endsection
