<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New Product')] class extends Component {
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public string $icon = '';
    public ?string $image_url = null;
    public string $price = '0';
    public int $stock = 0;
    public int $rating = 5;
    public string $color_class = 'p-1';
    public ?int $category_id = null;
    public bool $is_active = true;
    public int $sort_order = 0;

    public function updatedName(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('title')->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'string', 'max:8'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'color_class' => ['required', Rule::in(['p-1', 'p-2', 'p-3', 'p-4'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        Product::create($validated);

        Flux::toast(variant: 'success', text: __('Product created.'));
        $this->redirectRoute('admin.products.index', navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('New Product') }}</flux:heading>
            <flux:subheading>{{ __('Add a new product to the catalog.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid max-w-3xl gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model.blur="name" :label="__('Name')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required description="{{ __('URL-friendly identifier.') }}" />
            </div>

            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />

            <div class="grid gap-5 md:grid-cols-3">
                <flux:input wire:model="icon" :label="__('Icon (emoji)')" required maxlength="8" />
                <flux:input wire:model="price" :label="__('Price (IDR)')" type="number" step="1" min="0" required />
                <flux:input wire:model="stock" :label="__('Stock')" type="number" min="0" required />
            </div>

            <flux:input wire:model="image_url" :label="__('Image URL')" type="url" placeholder="https://..." />

            <div class="grid gap-5 md:grid-cols-3">
                <flux:select wire:model="category_id" :label="__('Category')" placeholder="{{ __('— None —') }}">
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->title }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="color_class" :label="__('Color theme')">
                    <flux:select.option value="p-1">{{ __('Green soft') }}</flux:select.option>
                    <flux:select.option value="p-2">{{ __('Cream') }}</flux:select.option>
                    <flux:select.option value="p-3">{{ __('Green blue') }}</flux:select.option>
                    <flux:select.option value="p-4">{{ __('Yellow') }}</flux:select.option>
                </flux:select>

                <flux:input wire:model="rating" :label="__('Rating')" type="number" min="1" max="5" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />
                <flux:checkbox wire:model="is_active" :label="__('Active (visible on storefront)')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button :href="route('admin.products.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
