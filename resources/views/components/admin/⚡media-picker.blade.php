<?php

use App\Models\Media;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    /**
     * Path stored in the parent property (relative to the public disk).
     * Bound via wire:model on the parent.
     */
    #[Modelable]
    public ?string $value = null;

    public ?string $label = null;

    public bool $allowExternalUrl = true;

    public bool $open = false;

    public string $search = '';

    public string $externalUrl = '';

    public $upload = null;

    public function mount(?string $label = null, bool $allowExternalUrl = true): void
    {
        $this->label = $label;
        $this->allowExternalUrl = $allowExternalUrl;
        $this->externalUrl = $this->value && (str_starts_with($this->value, 'http://') || str_starts_with($this->value, 'https://'))
            ? $this->value
            : '';
    }

    public function openPicker(): void
    {
        $this->open = true;
    }

    public function closePicker(): void
    {
        $this->open = false;
        $this->search = '';
    }

    public function clear(): void
    {
        $this->value = null;
        $this->externalUrl = '';
    }

    public function pick(int $id): void
    {
        $media = Media::find($id);
        if (! $media) {
            return;
        }

        $this->value = $media->path;
        $this->externalUrl = '';
        $this->closePicker();
    }

    public function applyExternalUrl(): void
    {
        $url = trim($this->externalUrl);

        if ($url === '') {
            $this->value = null;
            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            Flux::toast(variant: 'danger', heading: __('Invalid URL'), text: __('Enter a full URL starting with http:// or https://'));
            return;
        }

        $this->value = $url;
    }

    public function uploadAndPick(): void
    {
        try {
            $this->validate([
                'upload' => ['required', 'image', 'max:10240'],
            ]);

            $path = $this->upload->store('media', 'public');

            $width = null;
            $height = null;
            $abs = Storage::disk('public')->path($path);
            $size = @getimagesize($abs);
            if ($size !== false) {
                [$width, $height] = $size;
            }

            $media = Media::create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->upload->getClientOriginalName(),
                'mime' => $this->upload->getMimeType(),
                'size' => $this->upload->getSize(),
                'width' => $width,
                'height' => $height,
                'uploaded_by' => auth()->id(),
            ]);

            $this->upload = null;
            $this->value = $media->path;
            $this->externalUrl = '';
            $this->closePicker();

            Flux::toast(variant: 'success', text: __('Uploaded and selected.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Upload failed'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the file.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Upload failed'), text: $e->getMessage());
        }
    }

    #[Computed]
    public function items()
    {
        return Media::query()
            ->where('mime', 'like', 'image/%')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('original_name', 'like', "%{$this->search}%")
                        ->orWhere('alt', 'like', "%{$this->search}%")
                        ->orWhere('path', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->limit(60)
            ->get();
    }

    public function preview(): ?string
    {
        return $this->value ? image_src($this->value) : null;
    }
}; ?>

<div>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div class="mt-2 flex flex-wrap items-center gap-3">
        @if ($this->preview())
            <img src="{{ $this->preview() }}" alt="" class="h-20 w-20 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700" />
        @else
            <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-zinc-300 text-zinc-400 dark:border-zinc-700">
                <flux:icon.photo class="size-8" />
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <flux:button size="sm" variant="primary" icon="folder-open" wire:click="openPicker" type="button">
                {{ __('Pick from library') }}
            </flux:button>
            @if ($value)
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clear" type="button">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>
    </div>

    @if ($allowExternalUrl)
        <div class="mt-3 flex flex-wrap items-end gap-2">
            <div class="grow">
                <flux:input
                    wire:model="externalUrl"
                    :label="__('…or paste an external URL')"
                    placeholder="https://…"
                />
            </div>
            <flux:button size="sm" variant="ghost" wire:click="applyExternalUrl" type="button">
                {{ __('Apply URL') }}
            </flux:button>
        </div>
    @endif

    @if ($value)
        <flux:text size="sm" class="mt-2 break-all text-zinc-500">{{ $value }}</flux:text>
    @endif

    @if ($open)
        <flux:modal name="media-picker-{{ $this->getId() }}" :show="true" x-on:close="$wire.closePicker()" class="md:w-4xl">
            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Pick from media library') }}</flux:heading>
                    <flux:subheading>{{ __('Click an image to use it, or upload a new one below.') }}</flux:subheading>
                </div>

                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="{{ __('Search…') }}"
                />

                <div class="grid max-h-[60vh] grid-cols-3 gap-3 overflow-y-auto sm:grid-cols-4 lg:grid-cols-6">
                    @forelse ($this->items as $media)
                        <button
                            type="button"
                            wire:click="pick({{ $media->id }})"
                            wire:key="picker-{{ $media->id }}"
                            class="group relative aspect-square overflow-hidden rounded-lg border border-zinc-200 transition-all hover:border-emerald-500 hover:ring-2 hover:ring-emerald-500/30 dark:border-zinc-700"
                            title="{{ $media->original_name }}"
                        >
                            <img src="{{ $media->url() }}" alt="{{ $media->alt ?: $media->original_name }}" class="h-full w-full object-cover" loading="lazy" />
                            <span class="absolute inset-x-0 bottom-0 truncate bg-black/60 px-1 py-0.5 text-[10px] text-white opacity-0 group-hover:opacity-100">{{ $media->original_name }}</span>
                        </button>
                    @empty
                        <div class="col-span-full py-10 text-center text-zinc-500">
                            {{ __('No images found. Upload one below or search a different keyword.') }}
                        </div>
                    @endforelse
                </div>

                <flux:separator />

                <div class="flex flex-col gap-3">
                    <flux:label>{{ __('Or upload new') }}</flux:label>
                    <input type="file" wire:model="upload" accept="image/*" class="block w-full text-sm" />
                    @error('upload')<flux:text class="text-sm text-red-500">{{ $message }}</flux:text>@enderror
                    <div class="flex items-center gap-2">
                        <flux:button size="sm" variant="primary" icon="arrow-up-tray" wire:click="uploadAndPick" :disabled="! $upload" type="button">
                            {{ __('Upload & select') }}
                        </flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="closePicker" type="button">
                            {{ __('Close') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
