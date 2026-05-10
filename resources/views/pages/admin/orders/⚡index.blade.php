<?php

use App\Models\Order;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Orders')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    #[Url(as: 'pay', except: '')]
    public string $paymentFilter = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    public array $selected = [];

    public string $bulkStatus = '';

    public function updatedSearch(): void { $this->resetPage(); $this->selected = []; }
    public function updatedStatusFilter(): void { $this->resetPage(); $this->selected = []; }
    public function updatedPaymentFilter(): void { $this->resetPage(); $this->selected = []; }
    public function updatedDateFrom(): void { $this->resetPage(); $this->selected = []; }
    public function updatedDateTo(): void { $this->resetPage(); $this->selected = []; }

    public function clearFilters(): void
    {
        $this->reset('search', 'statusFilter', 'paymentFilter', 'dateFrom', 'dateTo');
        $this->resetPage();
        $this->selected = [];
    }

    private function baseQuery()
    {
        return Order::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('number', 'like', "%{$this->search}%")
                      ->orWhere('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('customer_email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->paymentFilter !== '', fn ($q) => $q->where('payment_status', $this->paymentFilter))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));
    }

    #[Computed]
    public function orders()
    {
        return $this->baseQuery()
            ->withCount('items')
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function pageIds(): array
    {
        return $this->orders->pluck('id')->all();
    }

    public function toggleSelectAll(bool $checked): void
    {
        if ($checked) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $this->pageIds)));
            return;
        }

        $this->selected = array_values(array_diff($this->selected, $this->pageIds));
    }

    public function bulkUpdateStatus(): void
    {
        try {
            $this->validate([
                'bulkStatus' => ['required', Rule::in(Order::STATUSES)],
                'selected' => ['array', 'min:1'],
                'selected.*' => ['integer'],
            ], [
                'bulkStatus.required' => __('Pick a status to apply.'),
                'selected.min' => __('Select at least one order.'),
            ]);

            $count = Order::whereIn('id', $this->selected)->update(['status' => $this->bulkStatus]);

            $this->selected = [];
            $this->bulkStatus = '';

            Flux::toast(variant: 'success', text: __(':count order(s) updated.', ['count' => $count]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function exportCsv()
    {
        $query = $this->baseQuery()->with('items');
        $filename = 'orders-'.Carbon::now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Number', 'Date', 'Customer', 'Email', 'Phone',
                'Status', 'Payment status', 'Payment method',
                'Subtotal', 'Discount', 'Shipping', 'Total',
                'Coupon', 'Items',
            ]);

            $query->orderBy('created_at')->chunk(500, function ($orders) use ($out) {
                foreach ($orders as $order) {
                    $items = $order->items
                        ->map(fn ($i) => $i->quantity.'× '.$i->product_name)
                        ->implode('; ');

                    fputcsv($out, [
                        $order->number,
                        optional($order->created_at)->format('Y-m-d H:i'),
                        $order->customer_name,
                        $order->customer_email,
                        $order->customer_phone,
                        $order->status,
                        $order->payment_status,
                        $order->payment_method,
                        $order->subtotal,
                        $order->discount,
                        $order->shipping_cost,
                        $order->total,
                        $order->coupon_code,
                        $items,
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function statuses(): array
    {
        return Order::STATUSES;
    }

    public function paymentStatuses(): array
    {
        return Order::PAYMENT_STATUSES;
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Orders') }}</flux:heading>
                <flux:subheading>{{ __('All customer orders.') }}</flux:subheading>
            </div>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">
                {{ __('Export CSV') }}
            </flux:button>
        </div>

        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search…') }}"
            />
            <flux:select wire:model.live="statusFilter">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach ($this->statuses() as $status)
                    <flux:select.option value="{{ $status }}">{{ ucfirst($status) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="paymentFilter">
                <flux:select.option value="">{{ __('All payments') }}</flux:select.option>
                @foreach ($this->paymentStatuses() as $payment)
                    <flux:select.option value="{{ $payment }}">{{ ucfirst($payment) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input
                type="date"
                wire:model.live="dateFrom"
                :placeholder="__('From')"
            />
            <flux:input
                type="date"
                wire:model.live="dateTo"
                :placeholder="__('To')"
            />
        </div>

        @if ($search !== '' || $statusFilter !== '' || $paymentFilter !== '' || $dateFrom !== '' || $dateTo !== '')
            <div>
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>
            </div>
        @endif

        @if (count($selected) > 0)
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>
                    <span class="font-semibold">{{ count($selected) }}</span>
                    {{ __('selected') }}
                </flux:text>
                <flux:select wire:model="bulkStatus" placeholder="{{ __('Set status to…') }}" class="max-w-xs">
                    <flux:select.option value="">{{ __('Set status to…') }}</flux:select.option>
                    @foreach ($this->statuses() as $status)
                        <flux:select.option value="{{ $status }}">{{ ucfirst($status) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:button
                    size="sm"
                    variant="primary"
                    icon="check"
                    wire:click="bulkUpdateStatus"
                    wire:confirm="{{ __('Apply status to all selected orders?') }}"
                >
                    {{ __('Apply') }}
                </flux:button>
                <flux:button size="sm" variant="ghost" wire:click="$set('selected', [])">
                    {{ __('Clear selection') }}
                </flux:button>
            </div>
        @endif

        <flux:table :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column class="w-10">
                    <flux:checkbox
                        :checked="count($selected) > 0 && count(array_diff($this->pageIds, $selected)) === 0"
                        x-on:change="$wire.toggleSelectAll($event.target.checked)"
                    />
                </flux:table.column>
                <flux:table.column>{{ __('Order') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Items') }}</flux:table.column>
                <flux:table.column>{{ __('Total') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Payment') }}</flux:table.column>
                <flux:table.column>{{ __('Placed') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->orders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model.live="selected" value="{{ $order->id }}" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-mono text-sm">{{ $order->number }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $order->customer_name }}</div>
                            <flux:text size="sm" class="text-zinc-500">{{ $order->customer_email }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>{{ $order->items_count }}</flux:table.cell>
                        <flux:table.cell>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $color = match ($order->status) {
                                    'pending' => 'amber',
                                    'paid' => 'blue',
                                    'shipped' => 'indigo',
                                    'delivered' => 'green',
                                    'cancelled' => 'red',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$color" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $payColor = match ($order->payment_status) {
                                    'paid' => 'green',
                                    'pending' => 'amber',
                                    'unpaid' => 'zinc',
                                    'failed', 'expired' => 'red',
                                    'refunded' => 'purple',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$payColor" size="sm">{{ ucfirst($order->payment_status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $order->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="eye"
                                :href="route('admin.orders.show', $order)"
                                wire:navigate
                            >
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center text-zinc-500">
                            {{ __('No orders match your filters.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
