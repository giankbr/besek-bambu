<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit Product')] class extends Component {
    use WithFileUploads;

    public Product $product;

    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public string $icon = '';
    public ?string $image_url = null;
    public $image;
    public $extraImages = [];
    public string $price = '0';
    public int $stock = 0;
    public int $rating = 5;
    public string $color_class = 'p-1';
    public ?int $category_id = null;
    public bool $is_active = true;
    public int $sort_order = 0;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = $product->description;
        $this->icon = $product->icon;
        $this->image_url = $product->image_url;
        $this->price = (string) $product->price;
        $this->stock = $product->stock;
        $this->rating = $product->rating;
        $this->color_class = $product->color_class;
        $this->category_id = $product->category_id;
        $this->is_active = $product->is_active;
        $this->sort_order = $product->sort_order;
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
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->product->id)],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'string', 'max:8'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'color_class' => ['required', Rule::in(['p-1', 'p-2', 'p-3', 'p-4'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        if ($this->image) {
            if ($this->product->image_url && ! str_starts_with($this->product->image_url, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->product->image_url);
            }
            $validated['image_url'] = $this->image->store('products', 'public');
        }

        unset($validated['image']);

        $this->product->update($validated);
        $this->image = null;
        $this->image_url = $this->product->fresh()->image_url;

        Flux::toast(variant: 'success', text: __('Product updated.'));
    }

    public function uploadExtraImages(): void
    {
        $this->validate([
            'extraImages.*' => ['image', 'max:4096'],
        ]);

        $maxSort = $this->product->images()->max('sort_order') ?? 0;
        $hasPrimary = $this->product->images()->where('is_primary', true)->exists();

        foreach ($this->extraImages as $i => $file) {
            $path = $file->store('products', 'public');
            $this->product->images()->create([
                'path' => $path,
                'sort_order' => $maxSort + $i + 1,
                'is_primary' => ! $hasPrimary && $i === 0,
            ]);
            $hasPrimary = true;
        }

        $this->extraImages = [];
        $this->dispatch('images-updated');

        Flux::toast(variant: 'success', text: __('Images uploaded.'));
    }

    public function setPrimary(int $imageId): void
    {
        $this->product->images()->update(['is_primary' => false]);
        $this->product->images()->where('id', $imageId)->update(['is_primary' => true]);
        Flux::toast(variant: 'success', text: __('Primary image updated.'));
    }

    public function deleteImage(int $imageId): void
    {
        $image = \App\Models\ProductImage::find($imageId);
        if ($image && $image->product_id === $this->product->id) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
            $image->delete();
            Flux::toast(variant: 'success', text: __('Image removed.'));
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Edit Product') }}</flux:heading>
            <flux:subheading>{{ $product->name }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" :label="__('Name')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required />
            </div>

            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />

            <div class="grid gap-5 md:grid-cols-3">
                <flux:input wire:model="icon" :label="__('Icon (emoji)')" required maxlength="8" />
                <flux:input wire:model="price" :label="__('Price (IDR)')" type="number" step="1" min="0" required />
                <flux:input wire:model="stock" :label="__('Stock')" type="number" min="0" required />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <flux:label>{{ __('Upload image') }}</flux:label>
                    <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm" />
                    @error('image')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror
                    @if ($image)
                        <div class="mt-2"><img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg" /></div>
                    @elseif ($image_url)
                        <div class="mt-2"><img src="{{ image_src($image_url) }}" class="h-24 rounded-lg" /></div>
                    @endif
                </div>
                <flux:input wire:model="image_url" :label="__('…or external URL / path')" placeholder="https://..." />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:select wire:model="category_id" :label="__('Category')" placeholder="{{ __('— None —') }}">
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->title }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="rating" :label="__('Rating')" type="number" min="1" max="5" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />
                <flux:checkbox wire:model="is_active" :label="__('Active (visible on storefront)')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.products.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <flux:separator class="my-2" />

        <div class="w-full">
            <flux:heading size="lg">{{ __('Image gallery') }}</flux:heading>
            <flux:subheading>{{ __('Upload additional images to show on the product page.') }}</flux:subheading>

            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($product->images as $img)
                    <div class="relative">
                        <img src="{{ image_src($img->path) }}" class="h-28 w-28 rounded-lg object-cover {{ $img->is_primary ? 'ring-2 ring-emerald-500' : '' }}" />
                        <div class="mt-1 flex gap-1">
                            @if (! $img->is_primary)
                                <flux:button size="xs" variant="ghost" wire:click="setPrimary({{ $img->id }})">{{ __('Set primary') }}</flux:button>
                            @else
                                <flux:badge color="green" size="sm">{{ __('Primary') }}</flux:badge>
                            @endif
                            <flux:button size="xs" variant="ghost" icon="trash" wire:click="deleteImage({{ $img->id }})" wire:confirm="{{ __('Remove this image?') }}" />
                        </div>
                    </div>
                @endforeach
                @if ($product->images->isEmpty())
                    <flux:text class="text-zinc-500">{{ __('No additional images yet.') }}</flux:text>
                @endif
            </div>

            <form wire:submit="uploadExtraImages" class="mt-5 grid gap-3">
                <flux:label>{{ __('Add more images (multiple allowed)') }}</flux:label>
                <input type="file" wire:model="extraImages" multiple accept="image/*" class="block w-full text-sm" />
                @error('extraImages.*')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror
                <div>
                    <flux:button type="submit" variant="primary">{{ __('Upload images') }}</flux:button>
                </div>
            </form>
        </div>
    </div>
</section>
