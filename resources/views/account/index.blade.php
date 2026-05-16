@extends('layouts.storefront')

@section('title', meta_title(__('Akun saya'), store_name()))

@section('content')
  <x-account-page active="overview" :crumbs="[['label' => __('Beranda'), 'url' => route('home')], ['label' => __('Akun')]]" eyebrow="{{ __('Selamat datang kembali') }}">
    <x-slot:heading>
      <h1 class="section-title page-head__title cart-title">{!! __('Halo, :name', ['name' => '<em>'.e($user->name).'</em>']) !!}</h1>
    </x-slot:heading>

    <div class="account-overview">
      <section class="confirmation-card account-panel account-profile-card">
        <div>
          <p class="confirmation-section-title">{{ __('Profil') }}</p>
          <h2 class="account-card-title">{{ $user->name }}</h2>
          <p class="confirmation-meta">{{ $user->email }}</p>
        </div>
        <a class="cart-link-btn" href="{{ route('account.profile') }}">{{ __('Edit profil') }}</a>
      </section>

      <section class="confirmation-card account-panel account-status-card">
        <p class="confirmation-section-title">{{ __('Akun') }}</p>
        <div class="account-status-card__value">{{ $recentOrders->count() }}</div>
        <p class="confirmation-meta">{{ __('pesanan terbaru') }}</p>
      </section>
    </div>

    <section class="confirmation-card account-panel account-orders-panel">
      <div class="account-section-head">
        <div>
          <p class="confirmation-section-title">{{ __('Pesanan terbaru') }}</p>
          <h2 class="account-card-title">{{ __('Aktivitas pesanan') }}</h2>
        </div>
        @if ($recentOrders->isNotEmpty())
          <a class="cart-link-btn" href="{{ route('account.orders') }}">{{ __('Lihat semua pesanan') }}</a>
        @endif
      </div>

      @forelse ($recentOrders as $order)
        <div class="account-order-row">
          <div>
            <a class="cart-item__name" href="{{ route('account.orders.show', $order) }}">{{ $order->number }}</a>
            <div class="confirmation-meta">{{ $order->created_at->format('M d, Y') }} · {{ __(':n item', ['n' => $order->items_count]) }}</div>
          </div>
          <div class="account-order-row__right">
            <strong>{{ idr($order->total) }}</strong>
            <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">{{ ucfirst($order->payment_status) }}</span>
          </div>
        </div>
      @empty
        <div class="account-empty-state">
          <p class="account-empty-state__title">{{ __('Belum ada pesanan.') }}</p>
          <p class="confirmation-meta">{{ __('Mulai jelajahi katalog dan pesanan terbaru Anda akan muncul di sini.') }}</p>
          <a class="hero-cta" href="{{ route('shop.index') }}">{{ __('Mulai belanja') }}</a>
        </div>
      @endforelse
    </section>
  </x-account-page>
@endsection
