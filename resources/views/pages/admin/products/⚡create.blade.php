<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('New Product')] class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public string $icon = '';
    public ?string $image_url = null;
    public $image;
    public string $price = '0';
    public int $stock = 0;
    public ?int $weight = null;
    public int $rating = 5;
    public string $color_class = 'p-1';
    public ?int $category_id = null;
    public bool $is_active = true;
    public int $sort_order = 0;
    public ?string $meta_title = null;
    public ?string $meta_description = null;
    public ?string $og_image = null;
    public $og_image_upload = null;

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
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
                'description' => ['nullable', 'string'],
                'icon' => ['required', 'string', 'max:8'],
                'image_url' => ['nullable', 'string', 'max:2048'],
                'image' => ['nullable', 'image', 'max:4096'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'weight' => ['nullable', 'integer', 'min:0', 'max:1000000'],
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
                $validated['image_url'] = $this->image->store('products', 'public');
            }

            if ($this->og_image_upload) {
                $validated['og_image'] = $this->og_image_upload->store('products/og', 'public');
            }

            unset($validated['image'], $validated['og_image_upload']);

            Product::create($validated);

            Flux::toast(variant: 'success', text: __('Product created.'));
            $this->redirectRoute('admin.products.index', navigate: true);
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to create'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to create'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('New Product') }}</flux:heading>
            <flux:subheading>{{ __('Add a new product to the catalog.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model.blur="name" :label="__('Name')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required description="{{ __('URL-friendly identifier.') }}" />
            </div>

            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />

            <div class="grid gap-5 md:grid-cols-4">
                <flux:input wire:model="icon" :label="__('Icon (emoji)')" required maxlength="8" />
                <flux:input wire:model="price" :label="__('Price (IDR)')" type="number" step="1" min="0" required />
                <flux:input wire:model="stock" :label="__('Stock')" type="number" min="0" required />
                <flux:input
                    wire:model="weight"
                    :label="__('Weight (gram)')"
                    type="number"
                    min="0"
                    step="1"
                    placeholder="1000"
                    description="{{ __('Required when using RajaOngkir.') }}"
                />
            </div>

            <livewire:admin.media-picker
                wire:model="image_url"
                :label="__('Primary image')"
                key="product-create-image"
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
                <flux:subheading>{{ __('Optional. Defaults to product name and description if blank.') }}</flux:subheading>
            </div>

            <flux:input
                wire:model="meta_title"
                :label="__('Meta title')"
                maxlength="160"
                :description="($meta_title ? strlen($meta_title) : 0).' / 160'"
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
                key="product-create-og"
            />
            <flux:text size="sm" class="text-zinc-500">{{ __('Recommended size 1200×630.') }}</flux:text>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button :href="route('admin.products.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
