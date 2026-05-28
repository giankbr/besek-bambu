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

    public ?int $deletingId = null;

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

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-coupon')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        try {
            Coupon::where('id', $this->deletingId)->delete();
            $this->deletingId = null;
            Flux::modal('delete-coupon')->close();
            Flux::toast(variant: 'success', text: __('Coupon deleted.'));
            unset($this->coupons);
        } catch (\Throwable $e) {
            Flux::modal('delete-coupon')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }

    public function toggleActive(int $id): void
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->update(['is_active' => ! $coupon->is_active]);
            Flux::toast(variant: 'success', text: $coupon->is_active ? __('Coupon activated.') : __('Coupon paused.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-4 md:p-6">
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

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-2 md:hidden">
            @forelse ($this->coupons as $coupon)
                <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-semibold">{{ $coupon->code }}</span>
                            @if ($coupon->label)
                                <div class="text-xs text-zinc-500">{{ $coupon->label }}</div>
                            @endif
                        </div>
                        @if ($coupon->is_active)
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Paused') }}</flux:badge>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-zinc-500">
                        <span>{{ ucfirst($coupon->type) }}: {{ $coupon->type === 'percent' ? rtrim(rtrim((string) $coupon->value, '0'), '.').'%' : idr($coupon->value) }}</span>
                        <span>{{ __('Min') }}: {{ idr($coupon->min_order) }}</span>
                        <span>{{ __('Used') }}: {{ $coupon->used_count }}{{ $coupon->usage_limit ? '/'.$coupon->usage_limit : '' }}</span>
                        @if ($coupon->expires_at)
                            <span>{{ __('Exp') }}: {{ $coupon->expires_at->format('d M Y') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex justify-end gap-1">
                        <flux:button size="sm" variant="ghost" :icon="$coupon->is_active ? 'pause' : 'play'" wire:click="toggleActive({{ $coupon->id }})" />
                        <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.coupons.edit', $coupon)" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $coupon->id }})" />
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No coupons yet.') }}</p>
            @endforelse
            {{ $this->coupons->links() }}
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
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
                                    wire:click="confirmDelete({{ $coupon->id }})"
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
    </div>

    <x-admin.confirm-modal
        name="delete-coupon"
        :title="__('Delete this coupon?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
