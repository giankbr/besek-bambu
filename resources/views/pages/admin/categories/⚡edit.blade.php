<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit Category')] class extends Component {
    use WithFileUploads;

    public Category $category;

    public string $title = '';
    public string $slug = '';
    public ?string $image_url = null;
    public $image;
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
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        if ($this->image) {
            if ($this->category->image_url && ! str_starts_with($this->category->image_url, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->category->image_url);
            }
            $validated['image_url'] = $this->image->store('categories', 'public');
        }

        unset($validated['image']);

        $this->category->update($validated);
        $this->image = null;
        $this->image_url = $this->category->fresh()->image_url;

        Flux::toast(variant: 'success', text: __('Category updated.'));
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Edit Category') }}</flux:heading>
            <flux:subheading>{{ $category->title }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <flux:label>{{ __('Upload new image') }}</flux:label>
                    <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm" />
                    @error('image')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror
                    @if ($image)
                        <div class="mt-2"><img src="{{ $image->temporaryUrl() }}" class="h-32 rounded-lg" /></div>
                    @elseif ($image_url)
                        <div class="mt-2"><img src="{{ image_src($image_url) }}" class="h-32 rounded-lg" /></div>
                    @endif
                </div>
                <flux:input wire:model="image_url" :label="__('…or external URL / path')" placeholder="https://..." />
            </div>

            <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.categories.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
