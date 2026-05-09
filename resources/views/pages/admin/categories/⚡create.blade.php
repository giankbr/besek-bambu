<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New Category')] class extends Component {
    public string $title = '';
    public string $slug = '';
    public ?string $image_url = null;
    public int $sort_order = 0;

    public function updatedTitle(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')],
            'image_url' => ['required', 'url', 'max:2048'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        Category::create($validated);

        Flux::toast(variant: 'success', text: __('Category created.'));
        $this->redirectRoute('admin.categories.index', navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('New Category') }}</flux:heading>
            <flux:subheading>{{ __('Add a new category.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid max-w-2xl gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model.blur="title" :label="__('Title')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required />
            </div>

            <flux:input wire:model="image_url" :label="__('Image URL')" type="url" placeholder="https://..." required />

            <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button :href="route('admin.categories.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
