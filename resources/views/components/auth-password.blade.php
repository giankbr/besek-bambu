<div class="auth-password">
  <input
    type="password"
    {{ $attributes }}
  />
  <button
    type="button"
    class="auth-password__toggle"
    aria-label="{{ __('Tampilkan kata sandi') }}"
    aria-pressed="false"
    data-label-show="{{ __('Tampilkan kata sandi') }}"
    data-label-hide="{{ __('Sembunyikan kata sandi') }}"
  >
    <x-icons.eye class="auth-password__icon auth-password__icon--show" />
    <x-icons.eye-off class="auth-password__icon auth-password__icon--hide" hidden />
  </button>
</div>
