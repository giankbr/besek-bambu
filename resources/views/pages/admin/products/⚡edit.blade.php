<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public ?string $meta_title = null;
    public ?string $meta_description = null;
    public ?string $og_image = null;
    public $og_image_upload = null;

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
        $this->meta_title = $product->meta_title;
        $this->meta_description = $product->meta_description;
        $this->og_image = $product->og_image;
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('title')->get();
    }

    public function save(): void
    {
        try {
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
                'meta_title' => ['nullable', 'string', 'max:160'],
                'meta_description' => ['nullable', 'string', 'max:320'],
                'og_image' => ['nullable', 'string', 'max:2048'],
                'og_image_upload' => ['nullable', 'image', 'max:4096'],
            ]);

            if ($this->image) {
                if ($this->product->image_url && ! str_starts_with($this->product->image_url, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($this->product->image_url);
                }
                $validated['image_url'] = $this->image->store('products', 'public');
            }

            if ($this->og_image_upload) {
                if ($this->product->og_image && ! str_starts_with($this->product->og_image, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($this->product->og_image);
                }
                $validated['og_image'] = $this->og_image_upload->store('products/og', 'public');
            }

            unset($validated['image'], $validated['og_image_upload']);

            $this->product->update($validated);
            $this->image = null;
            $this->og_image_upload = null;
            $this->image_url = $this->product->fresh()->image_url;
            $this->og_image = $this->product->fresh()->og_image;

            Flux::toast(variant: 'success', text: __('Product updated.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: $e->getMessage(),
            );
        }
    }

    public function uploadExtraImages(): void
    {
        try {
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
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to upload'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the files.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to upload'),
                text: $e->getMessage(),
            );
        }
    }

    public function setPrimary(int $imageId): void
    {
        try {
            $this->product->images()->update(['is_primary' => false]);
            $this->product->images()->where('id', $imageId)->update(['is_primary' => true]);
            Flux::toast(variant: 'success', text: __('Primary image updated.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function deleteImage(int $imageId): void
    {
        try {
            $image = \App\Models\ProductImage::find($imageId);
            if (! $image || $image->product_id !== $this->product->id) {
                Flux::toast(
                    variant: 'danger',
                    heading: __('Failed to remove'),
                    text: __('Image not found.'),
                );
                return;
            }

            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
            $image->delete();
            Flux::toast(variant: 'success', text: __('Image removed.'));
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to remove'),
                text: $e->getMessage(),
            );
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

            <livewire:admin.media-picker
                wire:model="image_url"
                :label="__('Primary image')"
                :key="'product-image-'.$product->id"
            />
            @if ($image)
                <div class="mt-1"><img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg" alt="" /></div>
            @endif
            <div>
                <flux:label>{{ __('…or upload directly') }}</flux:label>
                <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm" />
                @error('image')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror
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

            <flux:separator />

            <div>
                <flux:heading size="lg">{{ __('SEO') }}</flux:heading>
                <flux:subheading>{{ __('Customise how this product appears in search engines and social previews.') }}</flux:subheading>
            </div>

            <flux:input
                wire:model="meta_title"
                :label="__('Meta title')"
                maxlength="160"
                :description="($meta_title ? strlen($meta_title) : 0).' / 160. '.__('Leave blank to use the product name.')"
            />

            <flux:textarea
                wire:model="meta_description"
                :label="__('Meta description')"
                rows="3"
                maxlength="320"
                :description="($meta_description ? strlen($meta_description) : 0).' / 320. '.__('Recommended 120–160 characters.')"
            />

            <livewire:admin.media-picker
                wire:model="og_image"
                :label="__('Open Graph image')"
                :key="'product-og-'.$product->id"
            />
            <flux:text size="sm" class="text-zinc-500">{{ __('Recommended size 1200×630. Falls back to product image if blank.') }}</flux:text>

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
