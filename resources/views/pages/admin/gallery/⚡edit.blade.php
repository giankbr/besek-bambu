<?php

use App\Models\GalleryItem;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit gallery item')] class extends Component {
    use WithFileUploads;

    public GalleryItem $item;

    public string $title = '';
    public ?string $subtitle = null;
    public string $image_url = '';
    public $image;
    public string $color_class = 'g-1';
    public bool $drop = false;
    public int $sort_order = 0;

    public function mount(GalleryItem $item): void
    {
        $this->item = $item;
        $this->title = $item->title;
        $this->subtitle = $item->subtitle;
        $this->image_url = $item->image_url;
        $this->color_class = $item->color_class;
        $this->drop = $item->drop;
        $this->sort_order = $item->sort_order;
    }

    public function save(): void
    {
        try {
            $validated = $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'image_url' => ['nullable', 'string', 'max:2048'],
                'image' => ['nullable', 'image', 'max:4096'],
                'color_class' => ['required', Rule::in(['g-1', 'g-2', 'g-3', 'g-4'])],
                'drop' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            if ($this->image) {
                if ($this->item->image_url && ! str_starts_with($this->item->image_url, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($this->item->image_url);
                }
                $validated['image_url'] = $this->image->store('gallery', 'public');
            }

            unset($validated['image']);

            $this->item->update($validated);
            $this->image = null;
            $this->image_url = $this->item->fresh()->image_url;

            Flux::toast(variant: 'success', text: __('Gallery item updated.'));
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
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $item->title }}</flux:heading>
                <flux:subheading>{{ __('Update gallery item.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.gallery.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back to gallery') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <flux:input wire:model="title" :label="__('Title')" required />
            <flux:input wire:model="subtitle" :label="__('Subtitle')" />
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

            <div class="grid gap-5 md:grid-cols-3">
                <flux:select wire:model="color_class" :label="__('Color theme')">
                    <flux:select.option value="g-1">{{ __('Olive') }}</flux:select.option>
                    <flux:select.option value="g-2">{{ __('Cream') }}</flux:select.option>
                    <flux:select.option value="g-3">{{ __('Forest') }}</flux:select.option>
                    <flux:select.option value="g-4">{{ __('Sand') }}</flux:select.option>
                </flux:select>

                <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

                <flux:checkbox wire:model="drop" :label="__('Drop image (taller)')" />
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
            </div>
        </form>
    </div>
</section>
