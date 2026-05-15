<?php

use App\Models\Media;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Media library')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'type', except: '')]
    public string $typeFilter = '';

    public array $uploads = [];

    public ?int $editingId = null;

    public string $editingAlt = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }

    #[Computed]
    public function items()
    {
        return Media::query()
            ->with('uploader:id,name')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('original_name', 'like', "%{$this->search}%")
                      ->orWhere('alt', 'like', "%{$this->search}%")
                      ->orWhere('path', 'like', "%{$this->search}%");
                });
            })
            ->when($this->typeFilter === 'image', fn ($q) => $q->where('mime', 'like', 'image/%'))
            ->when($this->typeFilter === 'other', fn ($q) => $q->where(function ($w) {
                $w->whereNull('mime')->orWhere('mime', 'not like', 'image/%');
            }))
            ->latest()
            ->paginate(24);
    }

    public function uploadFiles(): void
    {
        try {
            $this->validate([
                'uploads' => ['required', 'array', 'min:1'],
                'uploads.*' => [
                    'file',
                    'max:10240',
                    'mimes:jpg,jpeg,png,webp,gif,pdf',
                ],
            ], [
                'uploads.required' => __('Pick at least one file to upload.'),
            ]);

            foreach ($this->uploads as $file) {
                $path = $file->store('media', 'public');

                $width = null;
                $height = null;
                if (str_starts_with((string) $file->getMimeType(), 'image/')) {
                    $abs = Storage::disk('public')->path($path);
                    $size = @getimagesize($abs);
                    if ($size !== false) {
                        [$width, $height] = $size;
                    }
                }

                Media::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                    'uploaded_by' => auth()->id(),
                ]);
            }

            $count = count($this->uploads);
            $this->uploads = [];

            Flux::toast(variant: 'success', text: __(':count file(s) uploaded.', ['count' => $count]));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to upload'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the files.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to upload'), text: $e->getMessage());
        }
    }

    public function startEdit(int $id): void
    {
        $media = Media::find($id);
        if (! $media) {
            return;
        }
        $this->editingId = $id;
        $this->editingAlt = (string) $media->alt;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingAlt = '';
    }

    public function saveAlt(): void
    {
        try {
            $this->validate([
                'editingAlt' => ['nullable', 'string', 'max:255'],
            ]);

            $media = Media::findOrFail($this->editingId);
            $media->update(['alt' => $this->editingAlt]);

            $this->cancelEdit();

            Flux::toast(variant: 'success', text: __('Alt text saved.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $media = Media::findOrFail($id);

            if (Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }

            $media->delete();

            if ($this->editingId === $id) {
                $this->cancelEdit();
            }

            Flux::toast(variant: 'success', text: __('Media deleted.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to delete'), text: $e->getMessage());
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Media library') }}</flux:heading>
            <flux:subheading>{{ __('Centralised storage for product, gallery, and other site images.') }}</flux:subheading>
        </div>

        <flux:card>
            <flux:heading size="lg">{{ __('Upload') }}</flux:heading>
            <form wire:submit="uploadFiles" class="mt-4 grid gap-3">
                <input type="file" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" class="block w-full text-sm" />
                @error('uploads')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror
                @error('uploads.*')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror

                @if (! empty($uploads))
                    <flux:text size="sm" class="text-zinc-500">
                        {{ count($uploads) }} {{ __('file(s) ready') }}
                    </flux:text>
                @endif

                <div>
                    <flux:button type="submit" variant="primary" icon="arrow-up-tray">{{ __('Upload') }}</flux:button>
                </div>
            </form>
        </flux:card>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by filename or alt…') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="typeFilter" class="max-w-xs">
                <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                <flux:select.option value="image">{{ __('Images only') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other files') }}</flux:select.option>
            </flux:select>
        </div>

        @if ($this->items->isEmpty())
            <flux:card>
                <flux:text class="py-10 text-center text-zinc-500">{{ __('No media uploaded yet.') }}</flux:text>
            </flux:card>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                @foreach ($this->items as $media)
                    <flux:card class="flex flex-col gap-2" :key="$media->id">
                        <div class="relative aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            @if ($media->isImage())
                                <img src="{{ $media->url() }}" alt="{{ $media->alt ?: $media->original_name }}" class="h-full w-full object-cover" loading="lazy" />
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <flux:icon.document class="size-12 text-zinc-400" />
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-medium" title="{{ $media->original_name }}">
                                {{ $media->original_name ?: basename($media->path) }}
                            </div>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ $media->humanSize() }}{{ $media->width ? ' · '.$media->width.'×'.$media->height : '' }}
                            </flux:text>
                        </div>

                        @if ($editingId === $media->id)
                            <form wire:submit="saveAlt" class="flex flex-col gap-2">
                                <flux:input wire:model="editingAlt" placeholder="{{ __('Alt text') }}" />
                                <div class="flex gap-1">
                                    <flux:button size="xs" variant="primary" type="submit">{{ __('Save') }}</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="cancelEdit" type="button">{{ __('Cancel') }}</flux:button>
                                </div>
                            </form>
                        @else
                            @if ($media->alt)
                                <flux:text size="sm" class="truncate text-zinc-500" title="{{ $media->alt }}">
                                    {{ __('Alt') }}: {{ $media->alt }}
                                </flux:text>
                            @endif

                            <div class="flex flex-wrap gap-1">
                                <span x-data="{ copied: false }">
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="clipboard"
                                        x-on:click="navigator.clipboard.writeText('{{ $media->url() }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                        type="button"
                                    >
                                        <span x-text="copied ? '{{ __('Copied') }}' : '{{ __('Copy URL') }}'"></span>
                                    </flux:button>
                                </span>
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    icon="pencil-square"
                                    wire:click="startEdit({{ $media->id }})"
                                />
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="delete({{ $media->id }})"
                                    wire:confirm="{{ __('Delete this file? It will also be removed from disk.') }}"
                                />
                            </div>
                        @endif
                    </flux:card>
                @endforeach
            </div>

            <div>{{ $this->items->links() }}</div>
        @endif
    </div>
</section>
