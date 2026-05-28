<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Categories')] class extends Component {
    public ?int $deletingId = null;

    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->orderBy('sort_order')->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-category')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: __('No category selected.'),
            );
            return;
        }

        try {
            Category::findOrFail($this->deletingId)->delete();

            $this->deletingId = null;
            Flux::modal('delete-category')->close();
            Flux::toast(variant: 'success', text: __('Category deleted.'));
            unset($this->categories);
        } catch (\Throwable $e) {
            Flux::modal('delete-category')->close();
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
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Categories') }}</flux:heading>
                <flux:subheading>{{ __('Organize products into categories.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.categories.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New Category') }}
            </flux:button>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-2 md:hidden">
            @forelse ($this->categories as $category)
                <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-medium">{{ $category->title }}</div>
                            <div class="text-xs text-zinc-500">{{ $category->slug }}</div>
                        </div>
                        <flux:badge color="zinc" size="sm">{{ $category->products_count }} {{ __('products') }}</flux:badge>
                    </div>
                    <div class="mt-2 flex justify-end gap-1">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.categories.edit', $category)" wire:navigate>{{ __('Edit') }}</flux:button>
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $category->id }})">{{ __('Delete') }}</flux:button>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No categories yet.') }}</p>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Slug') }}</flux:table.column>
                <flux:table.column>{{ __('Products') }}</flux:table.column>
                <flux:table.column>{{ __('Sort') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell>{{ $category->title }}</flux:table.cell>
                        <flux:table.cell>{{ $category->slug }}</flux:table.cell>
                        <flux:table.cell>{{ $category->products_count }}</flux:table.cell>
                        <flux:table.cell>{{ $category->sort_order }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center justify-end gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil-square"
                                    :href="route('admin.categories.edit', $category)"
                                    wire:navigate
                                >
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $category->id }})"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">
                            {{ __('No categories yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
        </div>
    </div>

    <x-admin.confirm-modal
        name="delete-category"
        :title="__('Delete category?')"
        :description="__('Products in this category will be unassigned, not deleted.')"
        action="delete"
    />
</section>
