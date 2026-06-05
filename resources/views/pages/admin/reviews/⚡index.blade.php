<?php

use App\Livewire\Concerns\HasAdminTablePagination;
use App\Models\ProductReview;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Reviews')] class extends Component {
    use HasAdminTablePagination, WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'state', except: '')]
    public string $stateFilter = '';

    public ?int $deletingId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStateFilter(): void { $this->resetPage(); }

    #[Computed]
    public function reviews()
    {
        return ProductReview::query()
            ->with(['product:id,name,slug,icon', 'user:id,name,email'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('title', 'like', "%{$this->search}%")
                      ->orWhere('body', 'like', "%{$this->search}%")
                      ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$this->search}%"))
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->stateFilter === 'approved', fn ($q) => $q->where('is_approved', true))
            ->when($this->stateFilter === 'pending', fn ($q) => $q->where('is_approved', false))
            ->latest()
            ->paginate($this->perPage);
    }

    public function toggleApproval(int $id): void
    {
        try {
            $review = ProductReview::findOrFail($id);
            $review->update(['is_approved' => ! $review->is_approved]);

            Flux::toast(variant: 'success', text: $review->is_approved ? __('Review approved.') : __('Review hidden.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-review')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        try {
            ProductReview::where('id', $this->deletingId)->delete();
            $this->deletingId = null;
            Flux::modal('delete-review')->close();
            Flux::toast(variant: 'success', text: __('Review deleted.'));
            unset($this->reviews);
        } catch (\Throwable $e) {
            Flux::modal('delete-review')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <flux:heading size="xl">{{ __('Reviews') }}</flux:heading>
            <flux:subheading>{{ __('Moderate customer product reviews.') }}</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by product, customer, content...') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="stateFilter" class="max-w-xs">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="approved">{{ __('Approved') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Hidden') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-2 md:hidden">
            @forelse ($this->reviews as $review)
                <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $review->product?->icon }}</span>
                            <span class="text-sm font-medium">{{ $review->product?->name ?? '—' }}</span>
                        </div>
                        @if ($review->is_approved)
                            <flux:badge color="green" size="sm">{{ __('Approved') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Hidden') }}</flux:badge>
                        @endif
                    </div>
                    <div class="mt-2 text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                    @if ($review->title)
                        <div class="mt-1 text-sm font-medium">{{ $review->title }}</div>
                    @endif
                    <div class="mt-0.5 line-clamp-2 text-xs text-zinc-500">{{ $review->body }}</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-xs text-zinc-500">{{ $review->user?->name ?? '—' }} · {{ $review->created_at->diffForHumans() }}</div>
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" :icon="$review->is_approved ? 'eye-slash' : 'check'" wire:click="toggleApproval({{ $review->id }})" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $review->id }})" />
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No reviews yet.') }}</p>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Product') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Rating') }}</flux:table.column>
                <flux:table.column>{{ __('Review') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Posted') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->reviews as $review)
                    <flux:table.row :key="$review->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">{{ $review->product?->icon }}</span>
                                <div>
                                    <div class="font-medium">{{ $review->product?->name ?? '—' }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $review->user?->name ?? '—' }}</div>
                            <flux:text size="sm" class="text-zinc-500">{{ $review->user?->email }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($review->title)
                                <div class="font-medium">{{ $review->title }}</div>
                            @endif
                            <flux:text size="sm" class="text-zinc-500 line-clamp-2 max-w-md">{{ $review->body }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($review->is_approved)
                                <flux:badge color="green" size="sm">{{ __('Approved') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Hidden') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $review->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :icon="$review->is_approved ? 'eye-slash' : 'check'"
                                    wire:click="toggleApproval({{ $review->id }})"
                                >
                                    {{ $review->is_approved ? __('Hide') : __('Approve') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $review->id }})"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No reviews yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        </div>

        <x-admin.list-pagination
            :paginator="$this->reviews"
            :per-page-options="$this->perPageOptions()"
        />
    </div>

    <x-admin.confirm-modal
        name="delete-review"
        :title="__('Delete this review?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
