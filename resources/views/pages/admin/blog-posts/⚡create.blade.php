<?php

use App\Models\BlogPost;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New article')] class extends Component {
    public string $title = '';
    public string $slug = '';
    public ?string $excerpt = null;
    public string $body = '';
    public ?string $author_name = null;
    public ?string $published_at = null;
    public bool $is_published = false;
    public int $sort_order = 0;
    public ?string $meta_title = null;
    public ?string $meta_description = null;
    public ?string $og_image = null;

    public function mount(): void
    {
        $this->author_name = store_name();
    }

    public function updatedTitle(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    public function generateSeo(): void
    {
        $seo = generate_blog_seo_meta($this->title, $this->excerpt, $this->body);
        $this->meta_title = $seo['meta_title'];
        $this->meta_description = $seo['meta_description'];
    }

    public function save(): void
    {
        try {
            $validated = $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', Rule::unique('blog_posts', 'slug')],
                'excerpt' => ['nullable', 'string', 'max:500'],
                'body' => ['required', 'string'],
                'author_name' => ['nullable', 'string', 'max:255'],
                'published_at' => ['nullable', 'date'],
                'is_published' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
                'meta_title' => ['nullable', 'string', 'max:160'],
                'meta_description' => ['nullable', 'string', 'max:320'],
                'og_image' => ['nullable', 'string', 'max:2048'],
            ]);

            if ($validated['is_published'] && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            BlogPost::create($validated);

            Flux::toast(variant: 'success', text: __('Blog post created.'));
            $this->redirectRoute('admin.blog-posts.index', navigate: true);
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
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <flux:heading size="xl">{{ __('New article') }}</flux:heading>
            <flux:subheading>{{ __('Write a new blog post for the storefront.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model.live.debounce.500ms="title" :label="__('Title')" required />
                <flux:input wire:model="slug" :label="__('Slug')" required description="{{ __('URL: /blog/slug') }}" />
            </div>

            <flux:textarea wire:model="excerpt" :label="__('Excerpt')" rows="2" maxlength="500" :description="__('Short summary for cards and SEO fallback.').' '.($excerpt ? strlen($excerpt) : 0).' / 500'" />

            <flux:textarea wire:model="body" :label="__('Body')" rows="14" required :description="__('HTML is supported (h2, p, ul, ol, a, strong, em).')" />

            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="author_name" :label="__('Author name')" />
                <flux:input wire:model="published_at" :label="__('Publish date & time')" type="datetime-local" />
            </div>

            <div class="flex flex-wrap items-end gap-x-6 gap-y-4">
                <div class="w-full max-w-[10rem]">
                    <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />
                </div>
                <flux:checkbox wire:model="is_published" :label="__('Published (visible on storefront)')" />
            </div>

            <flux:separator />

            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ __('SEO') }}</flux:heading>
                    <flux:subheading>{{ __('How this article appears in search engines and social previews.') }}</flux:subheading>
                </div>
                <flux:button size="sm" variant="ghost" icon="sparkles" wire:click="generateSeo" type="button">
                    {{ __('Generate') }}
                </flux:button>
            </div>

            <flux:input
                wire:model.live="meta_title"
                :label="__('Meta title')"
                maxlength="160"
                :description="($meta_title ? strlen($meta_title) : 0).' / 160'"
            />

            <flux:textarea
                wire:model.live="meta_description"
                :label="__('Meta description')"
                rows="3"
                maxlength="320"
                :description="($meta_description ? strlen($meta_description) : 0).' / 320. '.__('Recommended 120–160 characters.')"
            />

            <livewire:admin.media-picker wire:model="og_image" :label="__('Open Graph image')" key="blog-create-og" />

            @if ($meta_title || $meta_description)
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:text size="sm" class="mb-2 font-medium text-zinc-600 dark:text-zinc-300">{{ __('Search preview') }}</flux:text>
                    <p class="text-base text-[#1a0dab] dark:text-blue-400">{{ $meta_title ?: $title }}</p>
                    <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-400">{{ url('/blog/'.($slug ?: 'artikel')) }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $meta_description ?: __('Meta description will fall back to excerpt or body.') }}</p>
                </div>
            @endif

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create article') }}</flux:button>
                <flux:button :href="route('admin.blog-posts.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>
