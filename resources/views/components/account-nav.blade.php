@props(['active' => 'overview'])

@php
  $items = [
      'overview' => ['label' => 'Overview', 'route' => 'account.index'],
      'orders' => ['label' => 'My orders', 'route' => 'account.orders'],
      'wishlist' => ['label' => 'Wishlist', 'route' => 'account.wishlist'],
      'profile' => ['label' => 'Profile settings', 'route' => 'account.profile'],
  ];
@endphp

<aside class="account-side account-side--panel" aria-label="Account menu">
  <div class="account-side__intro">
    <span class="account-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
    <div>
      <p class="account-side__label">Akun saya</p>
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
      <form method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="account-nav__item account-nav__item--button">Sign out</button>
      </form>
    </li>
  </ul>
</aside>
