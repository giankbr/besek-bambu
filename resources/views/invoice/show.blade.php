<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ meta_title('Invoice '.$order->number, store_name()) }}</title>
  <style>
    :root {
      --ink: #1f1a14;
      --muted: #7d6f5f;
      --line: #e5e0d6;
      --accent: #1f7a3a;
      --paper: #ffffff;
    }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      background: #f7f3ec;
      color: var(--ink);
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 13px;
      line-height: 1.5;
    }
    .invoice-toolbar {
      max-width: 760px;
      margin: 24px auto 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 24px;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 10px 10px 0 0;
    }
    .invoice-toolbar a {
      color: var(--muted);
      text-decoration: none;
      font-size: 13px;
    }
    .invoice-toolbar button {
      background: var(--accent);
      color: #fff;
      border: 0;
      padding: 8px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .invoice {
      max-width: 760px;
      margin: 0 auto 32px;
      padding: 32px 36px;
      background: var(--paper);
      border: 1px solid var(--line);
      border-top: 0;
      border-radius: 0 0 10px 10px;
    }
    .invoice-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 24px;
      padding-bottom: 18px;
      border-bottom: 2px solid var(--ink);
    }
    .invoice-head h1 {
      margin: 0 0 4px;
      font-size: 22px;
      letter-spacing: 0.5px;
    }
    .invoice-head .brand {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .invoice-head .meta {
      color: var(--muted);
      font-size: 12px;
    }
    .invoice-head .right { text-align: right; }
    .pill {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      background: #eef7ee;
      color: var(--accent);
      border: 1px solid #c8e6cb;
    }
    .pill.unpaid { background: #fff7e6; color: #b27c0a; border-color: #f0d9a8; }
    .pill.cancelled { background: #fdecec; color: #a33; border-color: #f5c6cb; }

    .columns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-top: 24px;
    }
    .columns h3 {
      margin: 0 0 6px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--muted);
    }
    .columns p { margin: 0; }

    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 28px;
    }
    table.items thead th {
      text-align: left;
      padding: 10px 8px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: var(--muted);
      border-bottom: 1px solid var(--line);
    }
    table.items thead th.right { text-align: right; }
    table.items tbody td {
      padding: 12px 8px;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
    }
    table.items tbody td.right { text-align: right; }
    .totals {
      margin-top: 18px;
      margin-left: auto;
      width: 280px;
    }
    .totals .row {
      display: flex;
      justify-content: space-between;
      padding: 4px 0;
      font-size: 13px;
    }
    .totals .row.total {
      font-size: 15px;
      font-weight: 700;
      border-top: 2px solid var(--ink);
      margin-top: 6px;
      padding-top: 8px;
    }

    .footer {
      margin-top: 32px;
      padding-top: 16px;
      border-top: 1px dashed var(--line);
      font-size: 11px;
      color: var(--muted);
      text-align: center;
    }
    .footer p { margin: 2px 0; }

    @media print {
      body { background: #fff; }
      .invoice-toolbar { display: none; }
      .invoice { border: 0; padding: 0; max-width: none; margin: 0; border-radius: 0; }
      @page { margin: 18mm; }
    }
  </style>
</head>
<body>
  <div class="invoice-toolbar">
    <a href="{{ url()->previous() }}">← Back</a>
    <button onclick="window.print()">Print / Save as PDF</button>
  </div>

  <div class="invoice">
    <header class="invoice-head">
      <div>
        <div class="brand">{{ store_name() }}</div>
        @if (store_email())
          <div class="meta">{{ store_email() }}</div>
        @endif
        @if (store_phone())
          <div class="meta">{{ store_phone() }}</div>
        @endif
        @if (store_address())
          <div class="meta">{{ store_address() }}</div>
        @endif
      </div>
      <div class="right">
        <h1>INVOICE</h1>
        <div class="meta"><strong>{{ $order->number }}</strong></div>
        <div class="meta">{{ $order->created_at->format('d M Y · H:i') }}</div>
        <div style="margin-top:8px">
          @php
            $payClass = match ($order->payment_status) {
              'paid' => '',
              'unpaid', 'pending' => 'unpaid',
              'failed', 'expired' => 'cancelled',
              default => '',
            };
          @endphp
          <span class="pill {{ $payClass }}">{{ ucfirst($order->payment_status) }}</span>
        </div>
      </div>
    </header>

    <div class="columns">
      <div>
        <h3>Bill to</h3>
        <p><strong>{{ $order->customer_name }}</strong></p>
        <p>{{ $order->customer_email }}</p>
        <p>{{ $order->customer_phone }}</p>
      </div>
      <div>
        <h3>Ship to</h3>
        <p>{{ $order->customer_name }}</p>
        <p style="white-space:pre-line">{{ $order->shipping_address }}</p>
        @if ($order->shipping_city_name)
          <p>{{ $order->shipping_city_name }}{{ $order->shipping_province ? ', '.$order->shipping_province : '' }}</p>
        @endif
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>Item</th>
          <th class="right">Qty</th>
          <th class="right">Price</th>
          <th class="right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->items as $item)
          <tr>
            <td>
              <strong>{{ $item->product_icon }} {{ $item->product_name }}</strong>
            </td>
            <td class="right">{{ $item->quantity }}</td>
            <td class="right">{{ idr($item->price) }}</td>
            <td class="right">{{ idr($item->line_total) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="totals">
      <div class="row">
        <span>Subtotal</span>
        <span>{{ idr($order->subtotal) }}</span>
      </div>
      @if ((float) $order->discount > 0)
        <div class="row">
          <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
          <span>− {{ idr($order->discount) }}</span>
        </div>
      @endif
      @if ((float) $order->tax > 0)
        <div class="row">
          <span>{{ $order->tax_inclusive ? 'Tax included' : 'Tax' }} ({{ rtrim(rtrim(number_format((float) $order->tax_rate, 2), '0'), '.') }}%)</span>
          <span>{{ $order->tax_inclusive ? idr($order->tax) : '+ '.idr($order->tax) }}</span>
        </div>
      @endif
      @if ((float) $order->shipping_cost > 0)
        <div class="row">
          <span>Shipping{{ $order->shipping_courier ? ' ('.strtoupper($order->shipping_courier).' '.$order->shipping_service.')' : '' }}</span>
          <span>{{ idr($order->shipping_cost) }}</span>
        </div>
      @endif
      <div class="row total">
        <span>Total</span>
        <span>{{ idr($order->total) }}</span>
      </div>
    </div>

    @if ($order->payment_method || $order->paid_at || $order->tracking_number)
      <div style="margin-top:32px;padding-top:16px;border-top:1px solid var(--line)">
        <div class="columns">
          @if ($order->payment_method)
            <div>
              <h3>Payment</h3>
              <p>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</p>
              @if ($order->paid_at)
                <p class="meta" style="color:var(--muted)">Paid: {{ $order->paid_at->format('d M Y · H:i') }}</p>
              @endif
            </div>
          @endif
          @if ($order->tracking_number)
            <div>
              <h3>Tracking</h3>
              <p>{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</p>
              <p style="font-family:monospace">{{ $order->tracking_number }}</p>
            </div>
          @endif
        </div>
      </div>
    @endif

    @if ($order->notes)
      <div style="margin-top:24px;padding:12px;background:#faf6ec;border-radius:6px">
        <h3 style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted)">Notes</h3>
        <p style="margin:0">{{ $order->notes }}</p>
      </div>
    @endif

    <div class="footer">
      <p>Thank you for shopping with {{ store_name() }}!</p>
      <p>Generated {{ now()->format('d M Y · H:i') }}</p>
    </div>
  </div>
</body>
</html>
