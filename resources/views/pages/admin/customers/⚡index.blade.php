<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Customers')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'sort', except: 'recent')]
    public string $sort = 'recent';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    #[Computed]
    public function customers()
    {
        $query = Order::query()
            ->select([
                DB::raw('LOWER(customer_email) as email_key'),
                DB::raw('MAX(customer_email) as customer_email'),
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('MAX(customer_phone) as customer_phone'),
                DB::raw('MAX(user_id) as user_id'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as total_spent"),
                DB::raw('MAX(created_at) as last_order_at'),
                DB::raw('MIN(created_at) as first_order_at'),
            ])
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->groupBy('email_key');

        if ($this->search !== '') {
            $term = strtolower($this->search);
            $query->havingRaw('LOWER(MAX(customer_email)) LIKE ?', ["%{$term}%"])
                ->orHavingRaw('LOWER(MAX(customer_name)) LIKE ?', ["%{$term}%"]);
        }

        $query = match ($this->sort) {
            'spent' => $query->orderByDesc('total_spent'),
            'orders' => $query->orderByDesc('orders_count'),
            default => $query->orderByDesc('last_order_at'),
        };

        return $query->paginate(15);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Customers') }}</flux:heading>
            <flux:subheading>{{ __('Aggregated by email across all orders.') }}</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by name or email…') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="sort" class="max-w-xs">
                <flux:select.option value="recent">{{ __('Most recent order') }}</flux:select.option>
                <flux:select.option value="spent">{{ __('Highest spent') }}</flux:select.option>
                <flux:select.option value="orders">{{ __('Most orders') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->customers">
            <flux:table.columns>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Account') }}</flux:table.column>
                <flux:table.column>{{ __('Orders') }}</flux:table.column>
                <flux:table.column>{{ __('Total spent') }}</flux:table.column>
                <flux:table.column>{{ __('Last order') }}</flux:table.column>
                <flux:table.column>{{ __('First seen') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->customers as $row)
                    <flux:table.row :key="$row->email_key">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$row->customer_name" size="sm" />
                                <div>
                                    <div class="font-medium">{{ $row->customer_name }}</div>
                                    <flux:text size="sm" class="text-zinc-500">{{ $row->customer_email }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->user_id)
                                <flux:badge color="emerald" size="sm">{{ __('Registered') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Guest') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $row->orders_count }}</flux:table.cell>
                        <flux:table.cell>Rp {{ number_format((float) $row->total_spent, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>{{ \Illuminate\Support\Carbon::parse($row->last_order_at)->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>{{ \Illuminate\Support\Carbon::parse($row->first_order_at)->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="eye"
                                :href="route('admin.customers.show', ['email' => $row->customer_email])"
                                wire:navigate
                            >
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No customers yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
