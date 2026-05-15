@props(['active' => 'overview'])

@php
  $items = [
      'overview' => ['label' => __('Ringkasan'), 'route' => 'account.index'],
      'orders' => ['label' => __('Pesanan saya'), 'route' => 'account.orders'],
      'profile' => ['label' => __('Pengaturan profil'), 'route' => 'account.profile'],
  ];
  if (config('features.wishlist')) {
      $wishlistNav = ['label' => __('Wishlist'), 'route' => 'account.wishlist'];
      $items = array_slice($items, 0, 2, true) + ['wishlist' => $wishlistNav] + array_slice($items, 2, null, true);
  }
@endphp

<aside class="account-side account-side--panel" aria-label="{{ __('Menu akun') }}">
  <div class="account-side__intro">
    <span class="account-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
    <div>
      <p class="account-side__label">{{ __('Akun saya') }}</p>
      <strong>{{ auth()->user()->name }}</strong>
    </div>
  </div>

  <ul class="account-nav">
    @foreach ($items as $key => $item)
      <li>
        <a
          class="account-nav__item @if ($active === $key) account-nav__item--active @endif"
          href="{{ route($item['route']) }}"
          @if ($active === $key) aria-current="page" @endif
        >
          {{ $item['label'] }}
        </a>
      </li>
    @endforeach
    <li>
      <form
        method="post"
        action="{{ route('logout') }}"
        data-confirm="{{ __('Anda akan keluar dari akun. Lanjutkan?') }}"
        data-confirm-title="{{ __('Keluar dari akun?') }}"
        data-confirm-ok="{{ __('Ya, keluar') }}"
      >
        @csrf
        <button type="submit" class="account-nav__item account-nav__item--button">{{ __('Keluar') }}</button>
      </form>
    </li>
  </ul>
</aside>
