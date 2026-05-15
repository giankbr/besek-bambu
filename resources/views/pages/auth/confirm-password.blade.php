@extends('layouts.auth-storefront')

@section('title', __('Konfirmasi kata sandi').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Konfirmasi kata sandi')"
    :description="__('Untuk keamanan, masukkan kata sandi Anda sebelum melanjutkan.')"
  />

  <x-auth-session-status :status="session('status')" />

  <form method="POST" action="{{ route('password.confirm.store') }}" class="auth-form">
    @csrf

    <label>
      {{ __('Kata sandi') }}
      <x-auth-password
        name="password"
        required
        autofocus
        autocomplete="current-password"
        placeholder="{{ __('Kata sandi') }}"
      />
      @error('password')
        <span class="form-error">{{ $message }}</span>
      @enderror
    </label>

    <button type="submit" class="hero-cta auth-form__submit" data-test="confirm-password-button">
      {{ __('Lanjutkan') }}
    </button>
  </form>
@endsection
