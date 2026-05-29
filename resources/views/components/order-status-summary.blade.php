@props(['order', 'hideMidtransMethod' => false])

<div class="confirmation-status">
  <div class="confirmation-status__row">
    <span class="confirmation-status__label">{{ __('Pesanan') }}</span>
    <span class="stock-pill stock-pill--in">{{ order_status_label($order->status) }}</span>
  </div>
  <div class="confirmation-status__row">
    <span class="confirmation-status__label">{{ __('Pembayaran') }}</span>
    <span class="stock-pill {{ $order->isPaid() ? 'stock-pill--in' : 'stock-pill--low' }}">{{ payment_status_label($order->payment_status) }}</span>
  </div>
  @if ($order->payment_method && ! ($hideMidtransMethod && $order->payment_method === 'midtrans'))
    <div class="confirmation-status__row">
      <span class="confirmation-status__label">{{ __('Metode pembayaran') }}</span>
      <span class="stock-pill stock-pill--neutral">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
    </div>
  @endif
</div>
