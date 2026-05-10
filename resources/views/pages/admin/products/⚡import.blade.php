<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Import products')] class extends Component {
    use WithFileUploads;

    public $file = null;

    public bool $update_existing = true;

    public array $report = [];

    public function downloadTemplate()
    {
        $headers = [
            'name',
            'slug',
            'description',
            'icon',
            'price',
            'stock',
            'weight',
            'rating',
            'sort_order',
            'is_active',
            'category_title',
            'image_url',
            'meta_title',
            'meta_description',
        ];

        $sample = [
            'Besek Bambu Klasik',
            'besek-bambu-klasik',
            'Anyaman bambu kuat dengan tutup, ideal untuk kemasan kue.',
            '🧺',
            '45000',
            '50',
            '1000',
            '5',
            '0',
            '1',
            'Besek',
            '',
            'Besek Bambu Klasik | Eco-friendly',
            'Besek bambu kuat untuk hampers, kue, dan kemasan ramah lingkungan.',
        ];

        $csv = implode(',', $headers)."\n".implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', $v).'"', $sample))."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-template.csv"',
        ]);
    }

    public function import(): void
    {
        try {
            $this->validate([
                'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            ]);

            $handle = fopen($this->file->getRealPath(), 'r');

            if (! $handle) {
                throw new \RuntimeException(__('Could not open the uploaded file.'));
            }

            $headers = fgetcsv($handle);

            if (! $headers) {
                throw new \RuntimeException(__('CSV is empty.'));
            }

            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

            $required = ['name', 'price'];
            foreach ($required as $col) {
                if (! in_array($col, $headers, true)) {
                    throw new \RuntimeException(__('Missing required column: :col', ['col' => $col]));
                }
            }

            $report = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $record = array_combine(
                    $headers,
                    array_pad(array_slice($data, 0, count($headers)), count($headers), null),
                );

                try {
                    $name = trim((string) ($record['name'] ?? ''));
                    if ($name === '') {
                        throw new \RuntimeException(__('Name is required'));
                    }

                    $slug = trim((string) ($record['slug'] ?? ''));
                    if ($slug === '') {
                        $slug = Str::slug($name);
                    }

                    $categoryId = null;
                    $categoryTitle = trim((string) ($record['category_title'] ?? ''));
                    if ($categoryTitle !== '') {
                        $categoryId = Category::firstOrCreate(
                            ['slug' => Str::slug($categoryTitle)],
                            [
                                'title' => $categoryTitle,
                                'image_url' => '',
                                'sort_order' => 0,
                            ],
                        )->id;
                    }

                    $payload = array_filter([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $record['description'] ?? null,
                        'icon' => trim((string) ($record['icon'] ?? '🧺')) ?: '🧺',
                        'image_url' => trim((string) ($record['image_url'] ?? '')) ?: null,
                        'price' => is_numeric($record['price'] ?? null) ? (float) $record['price'] : null,
                        'stock' => is_numeric($record['stock'] ?? null) ? (int) $record['stock'] : 0,
                        'weight' => is_numeric($record['weight'] ?? null) ? (int) $record['weight'] : null,
                        'rating' => is_numeric($record['rating'] ?? null) ? (int) max(1, min(5, (int) $record['rating'])) : 5,
                        'sort_order' => is_numeric($record['sort_order'] ?? null) ? (int) $record['sort_order'] : 0,
                        'is_active' => $this->parseBool($record['is_active'] ?? '1'),
                        'category_id' => $categoryId,
                        'meta_title' => $record['meta_title'] ?? null,
                        'meta_description' => $record['meta_description'] ?? null,
                        'color_class' => 'p-1',
                    ], fn ($v) => $v !== null);

                    if ($payload['price'] === null) {
                        throw new \RuntimeException(__('Price is required'));
                    }

                    $existing = Product::where('slug', $slug)->first();

                    if ($existing) {
                        if (! $this->update_existing) {
                            $report['skipped']++;

                            continue;
                        }
                        $existing->update($payload);
                        $report['updated']++;
                    } else {
                        Product::create($payload);
                        $report['created']++;
                    }
                } catch (\Throwable $e) {
                    $report['errors'][] = __('Row :row: :msg', ['row' => $row, 'msg' => $e->getMessage()]);
                }
            }

            fclose($handle);

            $this->report = $report;
            $this->file = null;

            Flux::toast(
                variant: count($report['errors']) === 0 ? 'success' : 'warning',
                heading: __('Import done'),
                text: __(':created created · :updated updated · :skipped skipped · :errors errors', [
                    'created' => $report['created'],
                    'updated' => $report['updated'],
                    'skipped' => $report['skipped'],
                    'errors' => count($report['errors']),
                ]),
            );
        } catch (ValidationException $e) {
            Flux::toast(variant: 'danger', heading: __('Failed'), text: collect($e->validator->errors()->all())->first() ?? __('Invalid file.'));
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed'), text: $e->getMessage());
        }
    }

    private function parseBool(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'on', 'active'], true);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Import products') }}</flux:heading>
                <flux:subheading>{{ __('Upload a CSV to create or update products in bulk.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.products.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back to products') }}
            </flux:button>
        </div>

        <flux:card>
            <flux:heading size="lg">{{ __('CSV format') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                {{ __('Required columns: name, price. Optional: slug, description, icon, stock, weight, rating, sort_order, is_active, category_title, image_url, meta_title, meta_description.') }}
            </flux:text>
            <flux:text class="mt-1 text-zinc-500" size="sm">
                {{ __('Existing products are matched by slug. is_active accepts 1/0/true/false. category_title creates the category if it does not exist.') }}
            </flux:text>
            <div class="mt-4">
                <flux:button variant="filled" icon="document-arrow-down" wire:click="downloadTemplate">
                    {{ __('Download CSV template') }}
                </flux:button>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">{{ __('Upload') }}</flux:heading>
            <form wire:submit="import" class="mt-4 grid gap-4">
                <input type="file" wire:model="file" accept=".csv,text/csv" class="block w-full text-sm" />
                @error('file')<flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>@enderror

                <flux:checkbox wire:model="update_existing" :label="__('Update existing products when slug matches')" />

                <div>
                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="import,file"
                    >
                        <span wire:loading.remove wire:target="import,file">{{ __('Run import') }}</span>
                        <span wire:loading wire:target="import,file">{{ __('Importing…') }}</span>
                    </flux:button>
                </div>
            </form>
        </flux:card>

        @if (! empty($report))
            <flux:card>
                <flux:heading size="lg">{{ __('Last import report') }}</flux:heading>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/30">
                        <div class="text-xs text-zinc-500">{{ __('Created') }}</div>
                        <div class="text-2xl font-semibold">{{ $report['created'] }}</div>
                    </div>
                    <div class="rounded-lg bg-sky-50 p-3 dark:bg-sky-950/30">
                        <div class="text-xs text-zinc-500">{{ __('Updated') }}</div>
                        <div class="text-2xl font-semibold">{{ $report['updated'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                        <div class="text-xs text-zinc-500">{{ __('Skipped') }}</div>
                        <div class="text-2xl font-semibold">{{ $report['skipped'] }}</div>
                    </div>
                    <div class="rounded-lg bg-rose-50 p-3 dark:bg-rose-950/30">
                        <div class="text-xs text-zinc-500">{{ __('Errors') }}</div>
                        <div class="text-2xl font-semibold">{{ count($report['errors']) }}</div>
                    </div>
                </div>

                @if (! empty($report['errors']))
                    <flux:heading size="sm" class="mt-5">{{ __('Errors') }}</flux:heading>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-600 dark:text-rose-400">
                        @foreach ($report['errors'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </flux:card>
        @endif
    </div>
</section>
