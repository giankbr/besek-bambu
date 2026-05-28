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

    public array $selected = [];
    public string $bulkAction = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy('sort_order')
            ->paginate(10);
    }

    #[Computed]
    public function pageIds(): array
    {
        return $this->products->pluck('id')->all();
    }

    public function toggleSelectAll(bool $checked): void
    {
        if ($checked) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $this->pageIds)));
            return;
        }

        $this->selected = array_values(array_diff($this->selected, $this->pageIds));
    }

    public function confirmBulkAction(): void
    {
        if (empty($this->selected)) {
            Flux::toast(variant: 'danger', text: __('Select at least one product.'));
            return;
        }

        if ($this->bulkAction === '') {
            Flux::toast(variant: 'danger', text: __('Pick an action to apply.'));
            return;
        }

        Flux::modal('bulk-action-products')->show();
    }

    public function bulkApply(): void
    {
        try {
            $this->validate([
                'bulkAction' => ['required', 'in:activate,deactivate,delete'],
                'selected' => ['array', 'min:1'],
                'selected.*' => ['integer'],
            ]);

            $count = match ($this->bulkAction) {
                'activate' => Product::whereIn('id', $this->selected)->update(['is_active' => true]),
                'deactivate' => Product::whereIn('id', $this->selected)->update(['is_active' => false]),
                'delete' => (function () {
                    $c = Product::whereIn('id', $this->selected)->count();
                    Product::whereIn('id', $this->selected)->delete();
                    return $c;
                })(),
            };

            $this->selected = [];
            $this->bulkAction = '';

            Flux::modal('bulk-action-products')->close();
            Flux::toast(variant: 'success', text: __(':count product(s) updated.', ['count' => $count]));
            unset($this->products);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::modal('bulk-action-products')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::modal('bulk-action-products')->close();
            Flux::toast(variant: 'danger', heading: __('Failed'), text: $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-product')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: __('No product selected.'),
            );
            return;
        }

        try {
            Product::findOrFail($this->deletingId)->delete();

            $this->deletingId = null;
            Flux::modal('delete-product')->close();
            Flux::toast(variant: 'success', text: __('Product deleted.'));
            unset($this->products);
        } catch (\Throwable $e) {
            Flux::modal('delete-product')->close();
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
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Products') }}</flux:heading>
                <flux:subheading>{{ __('Manage your product catalog.') }}</flux:subheading>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.products.import')" variant="ghost" icon="arrow-up-tray" wire:navigate>
                    {{ __('Import CSV') }}
                </flux:button>
                <flux:button :href="route('admin.products.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('New Product') }}
                </flux:button>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by name or slug...') }}"
                class="max-w-sm"
            />
        </div>

        @if (count($selected) > 0)
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>
                    <span class="font-semibold">{{ count($selected) }}</span>
                    {{ __('selected') }}
                </flux:text>
                <flux:select wire:model="bulkAction" placeholder="{{ __('Choose action…') }}" class="max-w-xs">
                    <flux:select.option value="">{{ __('Choose action…') }}</flux:select.option>
                    <flux:select.option value="activate">{{ __('Activate') }}</flux:select.option>
                    <flux:select.option value="deactivate">{{ __('Deactivate') }}</flux:select.option>
                    <flux:select.option value="delete">{{ __('Delete') }}</flux:select.option>
                </flux:select>
                <flux:button
                    size="sm"
                    variant="primary"
                    icon="check"
                    wire:click="confirmBulkAction"
                >
                    {{ __('Apply') }}
                </flux:button>
                <flux:button size="sm" variant="ghost" wire:click="$set('selected', [])">
                    {{ __('Clear selection') }}
                </flux:button>
            </div>
        @endif

        {{-- Mobile card grid --}}
        <div class="grid grid-cols-2 gap-3 md:hidden">
            @forelse ($this->products as $product)
                <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-700 dark:bg-zinc-900">
                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="block">
                        <div class="aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if ($product->image_url)
                                <img src="{{ image_src($product->image_url) }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy" />
                            @else
                                <div class="flex h-full items-center justify-center text-4xl">{{ $product->icon }}</div>
                            @endif
                        </div>
                    </a>
                    <div class="p-2">
                        <div class="truncate text-sm font-medium">
                            <a href="{{ route('admin.products.edit', $product) }}" wire:navigate>{{ $product->name }}</a>
                        </div>
                        <div class="mt-0.5 text-xs text-zinc-500">{{ idr($product->price) }}</div>
                        <div class="mt-2 flex items-center justify-between gap-1">
                            <flux:checkbox wire:model.live="selected" value="{{ $product->id }}" />
                            @if ($product->is_active)
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Hidden') }}</flux:badge>
                            @endif
                            <flux:dropdown position="top" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" :href="route('admin.products.edit', $product)" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $product->id }})">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-10 text-center text-zinc-500">{{ __('No products found.') }}</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
        <flux:table :paginate="$this->products">
            <flux:table.columns>
                <flux:table.column class="w-10">
                    <flux:checkbox
                        :checked="count($selected) > 0 && count(array_diff($this->pageIds, $selected)) === 0"
                        x-on:change="$wire.toggleSelectAll($event.target.checked)"
                    />
                </flux:table.column>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Price') }}</flux:table.column>
                <flux:table.column>{{ __('Stock') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model.live="selected" value="{{ $product->id }}" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                @if ($product->image_url)
                                    <img src="{{ image_src($product->image_url) }}" alt="{{ $product->name }}" class="h-10 w-10 shrink-0 rounded-lg object-cover" />
                                @else
                                    <span class="text-2xl">{{ $product->icon }}</span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="font-medium hover:underline">{{ $product->name }}</a>
                                    <flux:text size="sm" class="text-zinc-500">{{ $product->slug }}</flux:text>
                                </div>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" :href="route('admin.products.edit', $product)" wire:navigate>
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $product->id }})">
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ idr($product->price) }}</flux:table.cell>
                        <flux:table.cell>{{ $product->stock }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($product->is_active)
                                <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Hidden') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">
                            {{ __('No products found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        </div>
    </div>

    <x-admin.confirm-modal
        name="bulk-action-products"
        :title="__('Apply action to all selected products?')"
        :confirm="__('Apply')"
        variant="primary"
        action="bulkApply"
    />

    <x-admin.confirm-modal
        name="delete-product"
        :title="__('Delete product?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
