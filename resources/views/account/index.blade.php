@extends('layouts.storefront')

@section('title', 'My account — Besek Bambu')

@section('content')
  <x-account-page active="overview" :crumbs="[['label' => 'Beranda', 'url' => route('home')], ['label' => 'Akun']]" eyebrow="Selamat datang kembali">
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">Halo, <em>{{ $user->name }}</em></h1>
    </x-slot:heading>

    <div class="account-overview">
      <section class="confirmation-card account-panel account-profile-card">
        <div>
          <p class="confirmation-section-title">Profile</p>
          <h2 class="account-card-title">{{ $user->name }}</h2>
          <p class="confirmation-meta">{{ $user->email }}</p>
        </div>
        <a class="cart-link-btn" href="{{ route('account.profile') }}">Edit profile</a>
      </section>

      <section class="confirmation-card account-panel account-status-card">
        <p class="confirmation-section-title">Account</p>
        <div class="account-status-card__value">{{ $recentOrders->count() }}</div>
        <p class="confirmation-meta">{{ \Illuminate\Support\Str::plural('recent order', $recentOrders->count()) }}</p>
      </section>
    </div>

    <section class="confirmation-card account-panel account-orders-panel">
      <div class="account-section-head">
        <div>
          <p class="confirmation-section-title">Recent orders</p>
          <h2 class="account-card-title">Aktivitas pesanan</h2>
        </div>
        @if ($recentOrders->isNotEmpty())
          <a class="cart-link-btn" href="{{ route('account.orders') }}">View all orders</a>
        @endif
      </div>

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
        <div class="account-empty-state">
          <p class="account-empty-state__title">No orders yet.</p>
          <p class="confirmation-meta">Start exploring the catalogue and your latest orders will appear here.</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">Start shopping</a>
        </div>
      @endforelse
    </section>
  </x-account-page>
@endsection
