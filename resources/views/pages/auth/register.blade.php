<x-layouts::auth :title="__('Daftar')">
  <div class="flex flex-col gap-6">
    <x-auth-header
      :title="__('Buat akun pembeli')"
      :description="__('Satu akun untuk berbelanja, menyimpan wishlist, dan melacak pesanan besek bambu Anda.')"
      :eyebrow="__('Toko online')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
      @csrf
      <flux:input
        name="name"
        :label="__('Nama lengkap')"
        :value="old('name')"
        type="text"
        required
        autofocus
        autocomplete="name"
        :placeholder="__('Nama di paket / invoice')"
      />

      <flux:input
        name="email"
        :label="__('Email')"
        :value="old('email')"
        type="email"
        required
        autocomplete="email"
        placeholder="nama@email.com"
      />

      <flux:input
        name="password"
        :label="__('Kata sandi')"
        type="password"
        required
        autocomplete="new-password"
        :placeholder="__('Minimal sesuai aturan keamanan')"
        viewable
      />

      <flux:input
        name="password_confirmation"
        :label="__('Ulangi kata sandi')"
        type="password"
        required
        autocomplete="new-password"
        :placeholder="__('Ulangi kata sandi')"
        viewable
      />

      <div class="flex items-center justify-end">
        <flux:button type="submit" variant="primary" class="w-full auth-storefront-submit" data-test="register-user-button">
          {{ __('Daftar') }}
        </flux:button>
      </div>
    </form>

    <div class="auth-storefront-footer-link space-x-1 text-center text-sm rtl:space-x-reverse">
      <span>{{ __('Sudah punya akun?') }}</span>
      <flux:link :href="route('login')" wire:navigate>{{ __('Masuk') }}</flux:link>
    </div>
  </div>
</x-layouts::auth>
