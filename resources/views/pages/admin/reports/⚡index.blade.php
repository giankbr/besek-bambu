<?php

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Reports')] class extends Component {
    #[Url(as: 'range')]
    public string $range = '30d';

    public array $ranges = [
        '7d' => '7 days',
        '30d' => '30 days',
        '90d' => '90 days',
        '12m' => '12 months',
    ];

    private function dateBounds(): array
    {
        $now = Carbon::now()->endOfDay();
        $from = match ($this->range) {
            '7d' => Carbon::now()->subDays(6)->startOfDay(),
            '90d' => Carbon::now()->subDays(89)->startOfDay(),
            '12m' => Carbon::now()->subMonths(11)->startOfMonth(),
            default => Carbon::now()->subDays(29)->startOfDay(),
        };

        return [$from, $now];
    }

    private function paidQuery()
    {
        [$from, $to] = $this->dateBounds();

        return Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to]);
    }

    #[Computed]
    public function kpis(): array
    {
        [$from, $to] = $this->dateBounds();

        $revenue = (float) $this->paidQuery()->sum('total');
        $orders = (int) Order::whereBetween('created_at', [$from, $to])->count();
        $paidOrders = (int) $this->paidQuery()->count();
        $aov = $paidOrders > 0 ? $revenue / $paidOrders : 0;

        $customers = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->pluck('customer_email')
            ->map(fn ($email) => strtolower((string) $email))
            ->unique()
            ->count();

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'paidOrders' => $paidOrders,
            'aov' => $aov,
            'customers' => $customers,
        ];
    }

    #[Computed]
    public function timeseries(): Collection
    {
        [$from, $to] = $this->dateBounds();
        $byMonth = $this->range === '12m';
        $driver = DB::connection()->getDriverName();

        if ($byMonth) {
            $bucket = match ($driver) {
                'sqlite' => "strftime('%Y-%m', paid_at)",
                'pgsql' => "to_char(paid_at, 'YYYY-MM')",
                default => "DATE_FORMAT(paid_at, '%Y-%m')",
            };
        } else {
            $bucket = match ($driver) {
                'sqlite' => "strftime('%Y-%m-%d', paid_at)",
                'pgsql' => "to_char(paid_at, 'YYYY-MM-DD')",
                default => 'DATE(paid_at)',
            };
        }

        $rows = $this->paidQuery()
            ->select(
                DB::raw("$bucket as bucket"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
            )
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket')
            ->map(fn ($v) => (float) $v);

        $series = collect();

        if ($byMonth) {
            $cursor = $from->copy()->startOfMonth();
            while ($cursor <= $to) {
                $key = $cursor->format('Y-m');
                $series->push([
                    'label' => $cursor->format('M'),
                    'iso' => $key,
                    'value' => (float) ($rows[$key] ?? 0),
                ]);
                $cursor->addMonth();
            }
            return $series;
        }

        $cursor = $from->copy()->startOfDay();
        while ($cursor <= $to) {
            $key = $cursor->format('Y-m-d');
            $series->push([
                'label' => $cursor->format('M d'),
                'iso' => $key,
                'value' => (float) ($rows[$key] ?? 0),
            ]);
            $cursor->addDay();
        }

        return $series;
    }

    #[Computed]
    public function topProducts(): Collection
    {
        [$from, $to] = $this->dateBounds();

        return OrderItem::query()
            ->select(
                'product_id',
                DB::raw('MAX(product_name) as product_name'),
                DB::raw('MAX(product_icon) as product_icon'),
                DB::raw('SUM(quantity) as units'),
                DB::raw('SUM(line_total) as revenue'),
            )
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('payment_status', 'paid')
                    ->whereBetween('paid_at', [$from, $to]);
            })
            ->groupBy('product_id')
            ->orderByDesc('units')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function topCategories(): Collection
    {
        [$from, $to] = $this->dateBounds();

        return OrderItem::query()
            ->select(
                'categories.id',
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('SUM(order_items.line_total) as revenue'),
                DB::raw('SUM(order_items.quantity) as units'),
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.paid_at', [$from, $to])
            ->groupBy('categories.id')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
                <flux:subheading>{{ __('Sales performance over time.') }}</flux:subheading>
            </div>
            <flux:select wire:model.live="range" class="max-w-xs">
                @foreach ($ranges as $key => $label)
                    <flux:select.option value="{{ $key }}">{{ __('Last') }} {{ __($label) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Revenue (paid)') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">Rp {{ number_format($this->kpis['revenue'], 0, ',', '.') }}</div>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Paid orders') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ number_format($this->kpis['paidOrders']) }}</div>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('All orders') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ number_format($this->kpis['orders']) }}</div>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Avg order value') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">Rp {{ number_format($this->kpis['aov'], 0, ',', '.') }}</div>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Customers') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ number_format($this->kpis['customers']) }}</div>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg">{{ __('Revenue trend') }}</flux:heading>
            <flux:subheading>{{ __('Paid orders only, in IDR.') }}</flux:subheading>

            @php
                $series = $this->timeseries;
                $maxValue = max(1, $series->max('value'));
                $width = 800;
                $height = 220;
                $padding = ['t' => 10, 'r' => 12, 'b' => 28, 'l' => 56];
                $plotW = $width - $padding['l'] - $padding['r'];
                $plotH = $height - $padding['t'] - $padding['b'];
                $count = max(1, $series->count());
                $barWidth = $plotW / $count;
                $tickStep = max(1, (int) ceil($count / 8));
            @endphp

            <div class="mt-6 overflow-x-auto">
                <svg viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="xMidYMid meet" class="w-full min-w-[640px]">
                    @for ($i = 0; $i <= 4; $i++)
                        @php
                            $y = $padding['t'] + ($plotH * $i / 4);
                            $val = $maxValue - ($maxValue * $i / 4);
                        @endphp
                        <line x1="{{ $padding['l'] }}" x2="{{ $width - $padding['r'] }}" y1="{{ $y }}" y2="{{ $y }}" stroke="currentColor" stroke-opacity="0.1" />
                        <text x="{{ $padding['l'] - 8 }}" y="{{ $y + 4 }}" text-anchor="end" font-size="10" fill="currentColor" fill-opacity="0.55">
                            {{ $val >= 1000000 ? number_format($val / 1000000, 1).'M' : ($val >= 1000 ? number_format($val / 1000, 0).'k' : (int) $val) }}
                        </text>
                    @endfor

                    @foreach ($series as $i => $point)
                        @php
                            $bw = max(2, $barWidth - 4);
                            $bx = $padding['l'] + ($i * $barWidth) + (($barWidth - $bw) / 2);
                            $bh = $point['value'] > 0 ? max(1, ($point['value'] / $maxValue) * $plotH) : 0;
                            $by = $padding['t'] + $plotH - $bh;
                            $isTick = ($i % $tickStep) === 0 || $i === $series->count() - 1;
                        @endphp
                        <rect x="{{ $bx }}" y="{{ $by }}" width="{{ $bw }}" height="{{ $bh }}" rx="2" class="fill-emerald-500/80 hover:fill-emerald-400">
                            <title>{{ $point['label'] }}: Rp {{ number_format($point['value'], 0, ',', '.') }}</title>
                        </rect>
                        @if ($isTick)
                            <text x="{{ $bx + ($bw / 2) }}" y="{{ $height - 8 }}" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity="0.6">
                                {{ $point['label'] }}
                            </text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </flux:card>

        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <flux:heading size="lg">{{ __('Top products') }}</flux:heading>
                <flux:subheading>{{ __('By units sold (paid).') }}</flux:subheading>

                <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->topProducts as $product)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $product->product_icon }}</span>
                                <div>
                                    <div class="font-medium">{{ $product->product_name }}</div>
                                    <flux:text size="sm" class="text-zinc-500">Rp {{ number_format((float) $product->revenue, 0, ',', '.') }}</flux:text>
                                </div>
                            </div>
                            <flux:badge color="emerald" size="sm">{{ $product->units }} {{ __('sold') }}</flux:badge>
                        </div>
                    @empty
                        <flux:text class="py-6 text-center text-zinc-500">{{ __('No paid orders in this period.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg">{{ __('Top categories') }}</flux:heading>
                <flux:subheading>{{ __('By revenue (paid).') }}</flux:subheading>

                <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->topCategories as $category)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <div class="font-medium">{{ $category->category_name }}</div>
                                <flux:text size="sm" class="text-zinc-500">{{ $category->units }} {{ __('units') }}</flux:text>
                            </div>
                            <div class="font-semibold">Rp {{ number_format((float) $category->revenue, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <flux:text class="py-6 text-center text-zinc-500">{{ __('No paid orders in this period.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>
</section>
