@extends('layouts.auth-storefront')

@section('title', meta_title(__('Autentikasi dua faktor'), store_name()))

@section('auth')
  <x-auth-header
    :title="__('Kode autentikasi')"
    :description="__('Masukkan kode dari aplikasi autentikator, atau gunakan kode pemulihan darurat.')"
  />

  <form method="POST" action="{{ route('two-factor.login.store') }}" class="auth-form">
    @csrf

    <label>
      {{ __('Kode 6 digit') }}
      <input
        type="text"
        name="code"
        inputmode="numeric"
        autocomplete="one-time-code"
        maxlength="6"
        pattern="[0-9]{6}"
        placeholder="000000"
      />
      @error('code')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <p class="auth-form__hint">{{ __('atau') }}</p>

    <label>
      {{ __('Kode pemulihan') }}
      <input
        type="text"
        name="recovery_code"
        autocomplete="one-time-code"
        placeholder="{{ __('Kode pemulihan') }}"
      />
      @error('recovery_code')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <button type="submit" class="hero-cta auth-form__submit">
      {{ __('Lanjutkan') }}
    </button>
  </form>
@endsection
