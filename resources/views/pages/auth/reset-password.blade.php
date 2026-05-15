@extends('layouts.auth-storefront')

@section('title', __('Reset kata sandi').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Reset kata sandi')"
    :description="__('Masukkan kata sandi baru untuk akun Anda.')"
  />

  <x-auth-session-status :status="session('status')" />

  <form method="POST" action="{{ route('password.update') }}" class="auth-form">
    @csrf

    <input type="hidden" name="token" value="{{ request()->route('token') }}" />

    <label>
      {{ __('Email') }}
      <input
        type="email"
        name="email"
        value="{{ old('email', request('email')) }}"
        required
        autocomplete="email"
      />
      @error('email')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <label>
      {{ __('Kata sandi baru') }}
      <x-auth-password
        name="password"
        required
        autocomplete="new-password"
        placeholder="{{ __('Kata sandi baru') }}"
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

    <button type="submit" class="hero-cta auth-form__submit" data-test="reset-password-button">
      {{ __('Simpan kata sandi') }}
    </button>
  </form>
@endsection
