<x-layouts::auth :title="__('Masuk')">
  <div class="flex flex-col gap-6">
    <x-auth-header
      :title="__('Masuk ke akun pembeli')"
      :description="__('Gunakan email dan kata sandi untuk checkout, wishlist, dan riwayat pesanan.')"
      :eyebrow="__('Toko online')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
      @csrf

      <flux:input
        name="email"
        :label="__('Email')"
        :value="old('email')"
        type="email"
        required
        autofocus
        autocomplete="email"
        placeholder="nama@email.com"
      />

      <div class="relative">
        <flux:input
          name="password"
          :label="__('Kata sandi')"
          type="password"
          required
          autocomplete="current-password"
          :placeholder="__('Kata sandi')"
          viewable
        />

        @if (Route::has('password.request'))
          <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
            {{ __('Lupa kata sandi?') }}
          </flux:link>
        @endif
      </div>

      <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

      <div class="flex items-center justify-end">
        <flux:button variant="primary" type="submit" class="w-full auth-storefront-submit" data-test="login-button">
          {{ __('Masuk') }}
        </flux:button>
      </div>
    </form>

    @if (Route::has('register'))
      <div class="auth-storefront-footer-link space-x-1 text-center text-sm rtl:space-x-reverse">
        <span>{{ __('Belum punya akun?') }}</span>
        <flux:link :href="route('register')" wire:navigate>{{ __('Daftar sebagai pembeli') }}</flux:link>
      </div>
    @endif
  </div>
</x-layouts::auth>
