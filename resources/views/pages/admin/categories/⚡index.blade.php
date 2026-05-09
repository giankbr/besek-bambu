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
            return;
        }

        Category::findOrFail($this->deletingId)->delete();

        $this->deletingId = null;
        Flux::modal('delete-category')->close();
        Flux::toast(variant: 'success', text: __('Category deleted.'));
        unset($this->categories);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Categories') }}</flux:heading>
                <flux:subheading>{{ __('Organize products into categories.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.categories.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New Category') }}
            </flux:button>
        </div>

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

    <flux:modal name="delete-category" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete category?') }}</flux:heading>
                <flux:subheading>{{ __('Products in this category will be unassigned, not deleted.') }}</flux:subheading>
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
