@props([
    'user' => null,
])

@php
  $user = $user ?? auth()->user();
@endphp

@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
  <div
    {{ $attributes->class(['account-verify-alert']) }}
    role="alert"
    aria-live="polite"
  >
    <span class="account-verify-alert__badge">{{ __('Belum aktif') }}</span>

    <div class="account-verify-alert__body">
      <p class="account-verify-alert__title">{{ __('Verifikasi email Anda') }}</p>
      <p class="account-verify-alert__text">
        {{ __('Kami sudah mengirim tautan ke :email. Buka email tersebut (cek folder spam) lalu klik tombol verifikasi untuk mengaktifkan akun.', ['email' => $user->email]) }}
      </p>

      @if (session('status') === 'verification-link-sent')
        <p class="account-verify-alert__success" role="status">
          {{ __('Tautan verifikasi baru telah dikirim.') }}
        </p>
      @endif

      <form method="POST" action="{{ route('verification.send') }}" class="account-verify-alert__form">
        @csrf
        <button type="submit" class="account-verify-alert__btn">
          {{ __('Kirim ulang email verifikasi') }}
        </button>
      </form>
    </div>
  </div>
@endif
