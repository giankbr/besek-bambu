<?php

use App\Models\Product;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Products')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy('sort_order')
            ->paginate(10);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-product')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        Product::findOrFail($this->deletingId)->delete();

        $this->deletingId = null;
        Flux::modal('delete-product')->close();
        Flux::toast(variant: 'success', text: __('Product deleted.'));
        unset($this->products);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Products') }}</flux:heading>
                <flux:subheading>{{ __('Manage your product catalog.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.products.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New Product') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by name or slug...') }}"
                class="max-w-sm"
            />
        </div>

        <flux:table :paginate="$this->products">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Price') }}</flux:table.column>
                <flux:table.column>{{ __('Stock') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $product->icon }}</span>
                                <div>
                                    <div class="font-medium">{{ $product->name }}</div>
                                    <flux:text size="sm" class="text-zinc-500">{{ $product->slug }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $product->category?->title ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ idr($product->price) }}</flux:table.cell>
                        <flux:table.cell>{{ $product->stock }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($product->is_active)
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Hidden') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    :href="route('admin.products.edit', $product)"
                                    wire:navigate
                                >
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $product->id }})"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No products found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="delete-product" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete product?') }}</flux:heading>
                <flux:subheading>{{ __('This action cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
