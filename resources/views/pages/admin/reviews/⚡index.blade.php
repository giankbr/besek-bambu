<?php

use App\Models\ProductReview;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Reviews')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'state', except: '')]
    public string $stateFilter = '';

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
            ->paginate(15);
    }

    public function toggleApproval(int $id): void
    {
        $review = ProductReview::findOrFail($id);
        $review->update(['is_approved' => ! $review->is_approved]);

        Flux::toast(variant: 'success', text: $review->is_approved ? __('Review approved.') : __('Review hidden.'));
    }

    public function delete(int $id): void
    {
        ProductReview::where('id', $id)->delete();
        Flux::toast(variant: 'success', text: __('Review deleted.'));
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
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

        <flux:table :paginate="$this->reviews">
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
                                    wire:click="delete({{ $review->id }})"
                                    wire:confirm="{{ __('Delete this review?') }}"
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
</section>
