<?php

use App\Models\GalleryItem;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit gallery item')] class extends Component {
    public GalleryItem $item;

    public string $title = '';
    public ?string $subtitle = null;
    public string $image_url = '';
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
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image_url' => ['required', 'url', 'max:2048'],
            'color_class' => ['required', Rule::in(['g-1', 'g-2', 'g-3', 'g-4'])],
            'drop' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $this->item->update($validated);

        Flux::toast(variant: 'success', text: __('Gallery item updated.'));
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

        <form wire:submit="save" class="grid max-w-2xl gap-5">
            <flux:input wire:model="title" :label="__('Title')" required />
            <flux:input wire:model="subtitle" :label="__('Subtitle')" />
            <flux:input wire:model="image_url" :label="__('Image URL')" type="url" required />

            @if ($image_url)
                <div>
                    <img src="{{ $image_url }}" alt="" class="h-40 w-full max-w-sm rounded-lg object-cover" />
                </div>
            @endif

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
