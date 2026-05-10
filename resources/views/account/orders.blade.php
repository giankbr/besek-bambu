@extends('layouts.storefront')

@section('title', 'My orders — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('account.index') }}">Account</a>
        <span>/</span>
        <span class="current">Orders</span>
      </nav>

      <div class="eyebrow">History</div>
      <h1 class="section-title cart-title">My <em>orders</em></h1>

      <div class="account-grid">
        <aside class="account-side">
          <ul class="account-nav">
            <li><a class="account-nav__item" href="{{ route('account.index') }}">Overview</a></li>
            <li><a class="account-nav__item account-nav__item--active" href="{{ route('account.orders') }}">My orders</a></li>
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
          @forelse ($orders as $order)
            <a class="confirmation-card account-order-card" href="{{ route('account.orders.show', $order) }}">
              <div class="account-order-row">
                <div>
                  <strong>{{ $order->number }}</strong>
                  <div class="confirmation-meta">{{ $order->created_at->format('M d, Y · H:i') }} · {{ $order->items_count }} items</div>
                </div>
                <div class="account-order-row__right">
                  <strong>{{ idr($order->total) }}</strong>
                  <div class="confirmation-status">
                    <span class="stock-pill stock-pill--in">{{ ucfirst($order->status) }}</span>
                    <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">{{ ucfirst($order->payment_status) }}</span>
                  </div>
                </div>
              </div>
            </a>
          @empty
            <div class="confirmation-card">
              <p class="confirmation-meta">You haven't placed any orders yet.</p>
              <a class="hero-cta" href="{{ route('shop.index') }}">Start shopping</a>
            </div>
          @endforelse

          @if ($orders->hasPages())
            <div style="margin-top:1.5rem">{{ $orders->links() }}</div>
          @endif
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
