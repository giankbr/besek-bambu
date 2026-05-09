<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Category')] class extends Component {
    public Category $category;

    public string $title = '';
    public string $slug = '';
    public ?string $image_url = null;
    public int $sort_order = 0;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->title = $category->title;
        $this->slug = $category->slug;
        $this->image_url = $category->image_url;
        $this->sort_order = $category->sort_order;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->category->id)],
            'image_url' => ['required', 'url', 'max:2048'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $this->category->update($validated);

        Flux::toast(variant: 'success', text: __('Category updated.'));
        $this->redirectRoute('admin.categories.index', navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Edit Category') }}</flux:heading>
            <flux:subheading>{{ $category->title }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid max-w-2xl gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required />
            </div>

            <flux:input wire:model="image_url" :label="__('Image URL')" type="url" required />

            <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.categories.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
