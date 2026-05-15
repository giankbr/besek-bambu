@extends('layouts.storefront')

@section('title', 'My orders — Besek Bambu')

@section('content')
  <x-account-page
    active="orders"
    :crumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Akun', 'url' => route('account.index')],
        ['label' => 'Pesanan'],
    ]"
    eyebrow="Riwayat"
  >
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">Pesanan <em>saya</em></h1>
    </x-slot:heading>

    <section class="confirmation-card account-panel account-orders-panel">
      <div class="account-section-head">
        <div>
          <p class="confirmation-section-title">Orders</p>
          <h2 class="account-card-title">Semua pesanan</h2>
        </div>
      </div>

      @forelse ($orders as $order)
        <a class="account-order-card account-order-card--inline" href="{{ route('account.orders.show', $order) }}">
          <div class="account-order-row">
            <div>
              <strong>{{ $order->number }}</strong>
              <div class="confirmation-meta">{{ $order->created_at->format('M d, Y · H:i') }} · {{ $order->items_count }} items</div>
              @if ($order->hasTracking())
                <div class="confirmation-meta account-order-row__tracking">
                  {{ strtoupper($order->shipping_courier) }} · <code>{{ $order->tracking_number }}</code>
                </div>
              @endif
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
        <div class="account-empty-state">
          <p class="account-empty-state__title">Belum ada pesanan.</p>
          <p class="confirmation-meta">Mulai belanja dan riwayat pesanan Anda akan muncul di sini.</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">Start shopping</a>
        </div>
      @endforelse

      @if ($orders->hasPages())
        <div class="account-pagination">{{ $orders->links() }}</div>
      @endif
    </section>
  </x-account-page>
@endsection
