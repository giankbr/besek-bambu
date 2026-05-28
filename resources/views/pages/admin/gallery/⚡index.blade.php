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

    public ?int $deletingId = null;

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

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-gallery-item')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        try {
            GalleryItem::where('id', $this->deletingId)->delete();
            $this->deletingId = null;
            Flux::modal('delete-gallery-item')->close();
            Flux::toast(variant: 'success', text: __('Gallery item deleted.'));
            unset($this->items);
        } catch (\Throwable $e) {
            Flux::modal('delete-gallery-item')->close();
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

        {{-- Mobile grid --}}
        <div class="grid grid-cols-2 gap-3 md:hidden">
            @forelse ($this->items as $item)
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <a href="{{ route('admin.gallery.edit', $item) }}" wire:navigate class="block">
                        @if ($item->image_url)
                            <div class="aspect-square bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ image_src($item->image_url) }}" alt="" class="h-full w-full object-cover" loading="lazy" />
                            </div>
                        @else
                            <div class="flex aspect-square items-center justify-center bg-zinc-100 text-3xl dark:bg-zinc-800">🖼️</div>
                        @endif
                    </a>
                    <div class="p-2">
                        <div class="truncate text-sm font-medium">{{ $item->title }}</div>
                        @if ($item->subtitle)
                            <div class="truncate text-xs text-zinc-500">{{ $item->subtitle }}</div>
                        @endif
                        <div class="mt-2 flex items-center justify-between">
                            @if ($item->drop)
                                <flux:badge color="amber" size="sm">{{ __('Drop') }}</flux:badge>
                            @else
                                <span></span>
                            @endif
                            <div class="flex gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.gallery.edit', $item)" wire:navigate />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $item->id }})" />
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-6 text-center text-sm text-zinc-500">{{ __('No gallery items yet.') }}</div>
            @endforelse
        </div>
        <div class="md:hidden">{{ $this->items->links() }}</div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
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
                                -
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
                                    wire:click="confirmDelete({{ $item->id }})"
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
    </div>

    <x-admin.confirm-modal
        name="delete-gallery-item"
        :title="__('Delete this gallery item?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
