<?php

use App\Models\GalleryItem;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Gallery')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    #[Computed]
    public function items()
    {
        return GalleryItem::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('title', 'like', "%{$this->search}%")
                      ->orWhere('subtitle', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('sort_order')
            ->paginate(12);
    }

    public function delete(int $id): void
    {
        try {
            GalleryItem::where('id', $id)->delete();
            Flux::toast(variant: 'success', text: __('Gallery item deleted.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Gallery') }}</flux:heading>
                <flux:subheading>{{ __('Manage images shown in the storefront gallery.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.gallery.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New item') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by title…') }}"
                class="max-w-sm"
            />
        </div>

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column>{{ __('Image') }}</flux:table.column>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Subtitle') }}</flux:table.column>
                <flux:table.column>{{ __('Style') }}</flux:table.column>
                <flux:table.column>{{ __('Drop') }}</flux:table.column>
                <flux:table.column>{{ __('Order') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell>
                            @if ($item->image_url)
                                <img src="{{ image_src($item->image_url) }}" alt="" class="h-12 w-12 rounded-lg object-cover" />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell><div class="font-medium">{{ $item->title }}</div></flux:table.cell>
                        <flux:table.cell>{{ $item->subtitle ?? '—' }}</flux:table.cell>
                        <flux:table.cell><flux:badge color="zinc" size="sm">{{ $item->color_class }}</flux:badge></flux:table.cell>
                        <flux:table.cell>
                            @if ($item->drop)
                                <flux:badge color="amber" size="sm">{{ __('Drop') }}</flux:badge>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $item->sort_order }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    :href="route('admin.gallery.edit', $item)"
                                    wire:navigate
                                />
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $item->id }})"
                                    wire:confirm="{{ __('Delete this gallery item?') }}"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No gallery items yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
