@extends('layouts.auth-storefront')

@section('title', __('Masuk').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Masuk ke akun pembeli')"
    :description="__('Gunakan email dan kata sandi untuk checkout dan riwayat pesanan.')"
  />

  <x-auth-session-status :status="session('status')" />

  <form method="POST" action="{{ route('login.store') }}" class="auth-form">
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

    <div class="auth-form__field">
      <div class="auth-form__label-row">
        <label for="login-password">{{ __('Kata sandi') }}</label>
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" class="auth-form__inline-link">{{ __('Lupa kata sandi?') }}</a>
        @endif
      </div>
      <x-auth-password
        id="login-password"
        name="password"
        required
        autocomplete="current-password"
        placeholder="{{ __('Kata sandi') }}"
      />
      @error('password')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </div>

    <div class="auth-form__row">
      <label class="auth-form__check">
        <input type="checkbox" name="remember" value="1" @checked(old('remember')) />
        <span>{{ __('Ingat saya') }}</span>
      </label>
    </div>

    <button type="submit" class="hero-cta auth-form__submit" data-test="login-button">
      {{ __('Masuk') }}
    </button>
  </form>

  @if (Route::has('register'))
    <p class="auth-storefront-footer-link">
      <span>{{ __('Belum punya akun?') }}</span>
      <a href="{{ route('register') }}">{{ __('Daftar sebagai pembeli') }}</a>
    </p>
  @endif
@endsection
