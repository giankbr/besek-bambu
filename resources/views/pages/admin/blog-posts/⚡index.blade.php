<?php

use App\Livewire\Concerns\HasAdminTablePagination;
use App\Models\BlogPost;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Blog')] class extends Component {
    use HasAdminTablePagination, WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function posts()
    {
        return BlogPost::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('title', 'like', "%{$this->search}%")
                        ->orWhere('excerpt', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->paginate($this->perPage);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-blog-post')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        try {
            BlogPost::whereKey($this->deletingId)->delete();
            $this->deletingId = null;
            Flux::modal('delete-blog-post')->close();
            Flux::toast(variant: 'success', text: __('Blog post deleted.'));
            unset($this->posts);
        } catch (\Throwable $e) {
            Flux::modal('delete-blog-post')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Blog') }}</flux:heading>
                <flux:subheading>{{ __('Manage articles shown on the storefront blog.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.blog-posts.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('New article') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by title or slug…') }}"
                class="max-w-sm"
            />
        </div>

        <div class="hidden md:block">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Published') }}</flux:table.column>
                    <flux:table.column>{{ __('Order') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->posts as $post)
                        <flux:table.row :key="$post->id">
                            <flux:table.cell>
                                <div class="font-medium">
                                    <a href="{{ route('admin.blog-posts.edit', $post) }}" wire:navigate class="hover:underline">
                                        {{ $post->title }}
                                    </a>
                                </div>
                                <div class="text-xs text-zinc-500">/blog/{{ $post->slug }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($post->is_published && $post->published_at?->lte(now()))
                                    <flux:badge color="green" size="sm">{{ __('Published') }}</flux:badge>
                                @elseif ($post->is_published)
                                    <flux:badge color="amber" size="sm">{{ __('Scheduled') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $post->published_at?->format('d M Y H:i') ?? '—' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $post->sort_order }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    @if ($post->is_published)
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="arrow-top-right-on-square"
                                            :href="route('blog.show', $post)"
                                            target="_blank"
                                        />
                                    @endif
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        :href="route('admin.blog-posts.edit', $post)"
                                        wire:navigate
                                    />
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $post->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500">
                                {{ __('No blog posts yet.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="grid gap-3 md:hidden">
            @forelse ($this->posts as $post)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <a href="{{ route('admin.blog-posts.edit', $post) }}" wire:navigate class="font-medium hover:underline">
                        {{ $post->title }}
                    </a>
                    <div class="mt-1 text-xs text-zinc-500">/blog/{{ $post->slug }}</div>
                    <div class="mt-3 flex items-center justify-between">
                        @if ($post->is_published && $post->published_at?->lte(now()))
                            <flux:badge color="green" size="sm">{{ __('Published') }}</flux:badge>
                        @elseif ($post->is_published)
                            <flux:badge color="amber" size="sm">{{ __('Scheduled') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                        @endif
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.blog-posts.edit', $post)" wire:navigate />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $post->id }})" />
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No blog posts yet.') }}</p>
            @endforelse
        </div>

        <x-admin.list-pagination
            :paginator="$this->posts"
            :per-page-options="$this->perPageOptions()"
        />
    </div>

    <x-admin.confirm-modal
        name="delete-blog-post"
        :title="__('Delete this article?')"
        :description="__('This action cannot be undone.')"
        action="delete"
    />
</section>
