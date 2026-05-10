@extends('layouts.storefront')

@section('title', 'Tracking ' . $order->number . ' — '.store_name())

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('account.index') }}">Account</a>
        <span>/</span>
        <a href="{{ route('account.orders') }}">Orders</a>
        <span>/</span>
        <a href="{{ route('account.orders.show', $order) }}">{{ $order->number }}</a>
        <span>/</span>
        <span class="current">Tracking</span>
      </nav>

      <div class="eyebrow">Package tracking</div>
      <h1 class="section-title cart-title">Tracking <em>{{ $order->number }}</em></h1>

      <div class="confirmation-card">
        @if ($order->hasTracking())
          <div class="confirmation-status">
            <span class="stock-pill stock-pill--in">{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</span>
            <span class="stock-pill stock-pill--low">AWB: {{ $order->tracking_number }}</span>
          </div>
        @endif

        @if ($error)
          <div style="margin-top:1rem;padding:0.75rem 1rem;background:#fdecec;border:1px solid #f5c6cb;border-radius:0.5rem;color:#a33">
            {{ $error }}
          </div>
        @endif

        @if (! empty($tracking['summary']))
          @php $sum = $tracking['summary']; @endphp
          <h2 class="confirmation-section-title">Summary</h2>
          <div style="display:grid;grid-template-columns:140px 1fr;gap:0.4rem 1rem;font-size:14px">
            @if (! empty($sum['shipper_name']))
              <span style="color:#7d6f5f">From</span>
              <span>{{ $sum['shipper_name'] }}</span>
            @endif
            @if (! empty($sum['receiver_name']))
              <span style="color:#7d6f5f">To</span>
              <span>{{ $sum['receiver_name'] }}</span>
            @endif
            @if (! empty($sum['origin']))
              <span style="color:#7d6f5f">Origin</span>
              <span>{{ $sum['origin'] }}</span>
            @endif
            @if (! empty($sum['destination']))
              <span style="color:#7d6f5f">Destination</span>
              <span>{{ $sum['destination'] }}</span>
            @endif
            @if (! empty($sum['status']))
              <span style="color:#7d6f5f">Status</span>
              <span><strong>{{ $sum['status'] }}</strong></span>
            @endif
          </div>
        @endif

        @if (! empty($tracking['delivery_status']))
          @php $ds = $tracking['delivery_status']; @endphp
          <div style="margin-top:1rem;padding:0.75rem 1rem;background:#eef7ee;border:1px solid #c8e6cb;border-radius:0.5rem">
            <strong>{{ $ds['status'] ?? 'Update' }}</strong>
            @if (! empty($ds['pod_receiver']))
              <p class="confirmation-meta" style="margin:0.25rem 0 0">Received by: {{ $ds['pod_receiver'] }}</p>
            @endif
            @if (! empty($ds['pod_date']))
              <p class="confirmation-meta" style="margin:0">{{ $ds['pod_date'] }} {{ $ds['pod_time'] ?? '' }}</p>
            @endif
          </div>
        @endif

        @if (! empty($tracking['manifest']))
          <h2 class="confirmation-section-title">Timeline</h2>
          <ol style="list-style:none;padding-left:1.5rem;border-left:2px solid #e5e0d6;margin:0">
            @foreach (array_reverse($tracking['manifest']) as $event)
              <li style="position:relative;padding-bottom:1rem">
                <span style="position:absolute;left:-1.65rem;top:0.4rem;width:0.6rem;height:0.6rem;border-radius:9999px;background:#1f7a3a"></span>
                <div style="font-size:14px;font-weight:600">{{ $event['manifest_description'] ?? $event['manifest_code'] ?? '—' }}</div>
                <div style="font-size:12px;color:#7d6f5f">
                  {{ trim(($event['manifest_date'] ?? '').' '.($event['manifest_time'] ?? '')) }}
                  @if (! empty($event['city_name']))
                    · {{ $event['city_name'] }}
                  @endif
                </div>
              </li>
            @endforeach
          </ol>
        @endif

        @if (empty($tracking) && ! $error)
          <p class="confirmation-meta">No tracking information available yet. Please check back later.</p>
        @endif

        <div class="confirmation-actions" style="margin-top:1.25rem">
          <a class="cart-link-btn" href="{{ route('account.orders.show', $order) }}">← Back to order</a>
          <a class="cart-link-btn" href="{{ route('account.orders.track', $order) }}">Refresh</a>
        </div>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection
