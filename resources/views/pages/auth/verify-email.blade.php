@extends('layouts.auth-storefront')

@section('title', __('Verifikasi email').' — '.store_name())

@section('auth')
  <x-auth-header
    :title="__('Verifikasi email')"
    :description="__('Klik tautan di email yang kami kirim untuk mengaktifkan akun Anda.')"
  />

  @if (session('status') === 'verification-link-sent')
    <x-auth-session-status :status="__('Tautan verifikasi baru telah dikirim ke email Anda.')" />
  @endif

  <div class="auth-form auth-form--stacked">
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="hero-cta auth-form__submit">
        {{ __('Kirim ulang email verifikasi') }}
      </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="auth-form__ghost" data-test="logout-button">
        {{ __('Keluar') }}
      </button>
    </form>
  </div>
@endsection
