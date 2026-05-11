@extends('layouts.storefront')

@section('title', 'My account — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Akun'],
        ]"
        eyebrow="Selamat datang kembali"
      >
        <h1 class="section-title page-head__title cart-title">Halo, <em>{{ $user->name }}</em></h1>
      </x-page-head>

      <div class="account-grid">
        <aside class="account-side">
          <ul class="account-nav">
            <li><a class="account-nav__item account-nav__item--active" href="{{ route('account.index') }}">Overview</a></li>
            <li><a class="account-nav__item" href="{{ route('account.orders') }}">My orders</a></li>
            <li><a class="account-nav__item" href="{{ route('account.wishlist') }}">Wishlist</a></li>
            <li><a class="account-nav__item" href="{{ route('profile.edit') }}">Profile settings</a></li>
            <li>
              <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="account-nav__item account-nav__item--button">Sign out</button>
              </form>
            </li>
          </ul>
        </aside>

        <div class="account-main">
          <div class="confirmation-card">
            <h2 class="confirmation-section-title">Profile</h2>
            <p class="confirmation-meta">{{ $user->name }}</p>
            <p class="confirmation-meta">{{ $user->email }}</p>
            <a class="cart-link-btn" href="{{ route('profile.edit') }}">Edit profile</a>
          </div>

          <div class="confirmation-card" style="margin-top:1.5rem">
            <h2 class="confirmation-section-title">Recent orders</h2>
            @forelse ($recentOrders as $order)
              <div class="account-order-row">
                <div>
                  <a class="cart-item__name" href="{{ route('account.orders.show', $order) }}">{{ $order->number }}</a>
                  <div class="confirmation-meta">{{ $order->created_at->format('M d, Y') }} · {{ $order->items_count }} items</div>
                </div>
                <div class="account-order-row__right">
                  <strong>{{ idr($order->total) }}</strong>
                  <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
              </div>
            @empty
              <p class="confirmation-meta">No orders yet. <a href="{{ route('shop.index') }}">Start shopping</a></p>
            @endforelse

            @if ($recentOrders->isNotEmpty())
              <div style="margin-top:1rem">
                <a class="cart-link-btn" href="{{ route('account.orders') }}">View all orders →</a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
