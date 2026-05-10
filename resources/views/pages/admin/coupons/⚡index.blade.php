<?php

use App\Models\Coupon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Coupons')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    #[Computed]
    public function coupons()
    {
        return Coupon::query()
            ->when($this->search !== '', function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('label', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(15);
    }

    public function delete(int $id): void
    {
        Coupon::where('id', $id)->delete();
        Flux::toast(variant: 'success', text: __('Coupon deleted.'));
    }

    public function toggleActive(int $id): void
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);
        Flux::toast(variant: 'success', text: $coupon->is_active ? __('Coupon activated.') : __('Coupon paused.'));
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Coupons') }}</flux:heading>
                <flux:subheading>{{ __('Promo codes for your storefront.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.coupons.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New coupon') }}
            </flux:button>
        </div>

        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search by code…') }}"
            class="max-w-sm"
        />

        <flux:table :paginate="$this->coupons">
            <flux:table.columns>
                <flux:table.column>{{ __('Code') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Value') }}</flux:table.column>
                <flux:table.column>{{ __('Min order') }}</flux:table.column>
                <flux:table.column>{{ __('Usage') }}</flux:table.column>
                <flux:table.column>{{ __('Expires') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->coupons as $coupon)
                    <flux:table.row :key="$coupon->id">
                        <flux:table.cell>
                            <span class="font-mono font-medium">{{ $coupon->code }}</span>
                            @if ($coupon->label)
                                <flux:text size="sm" class="text-zinc-500">{{ $coupon->label }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ ucfirst($coupon->type) }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $coupon->type === 'percent' ? rtrim(rtrim((string) $coupon->value, '0'), '.').'%' : idr($coupon->value) }}
                        </flux:table.cell>
                        <flux:table.cell>{{ idr($coupon->min_order) }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $coupon->expires_at?->format('M d, Y') ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($coupon->is_active)
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Paused') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :icon="$coupon->is_active ? 'pause' : 'play'"
                                    wire:click="toggleActive({{ $coupon->id }})"
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    :href="route('admin.coupons.edit', $coupon)"
                                    wire:navigate
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $coupon->id }})"
                                    wire:confirm="{{ __('Delete this coupon?') }}"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500">
                            {{ __('No coupons yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
