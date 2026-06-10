<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class FixProductSlugsCommand extends Command
{
    protected $signature = 'products:fix-slugs {--dry-run : Preview changes without saving}';

    protected $description = 'Normalize product names and slugs, preserving old slugs for redirects';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        Product::query()
            ->orderBy('id')
            ->each(function (Product $product) use ($dryRun, &$updated) {
                $name = trim((string) $product->name);
                $currentSlug = trim((string) $product->slug);
                $targetSlug = Product::normalizeSlug($name);

                if ($targetSlug === '') {
                    $this->warn("Skipping product #{$product->id}: unable to derive slug.");

                    return;
                }

                $legacy = is_array($product->legacy_slugs) ? $product->legacy_slugs : [];
                $changes = [];

                if ($name !== $product->name) {
                    $changes[] = 'name trimmed';
                }

                if ($currentSlug !== $targetSlug) {
                    if ($currentSlug !== '' && ! in_array($currentSlug, $legacy, true)) {
                        $legacy[] = $currentSlug;
                    }
                    $changes[] = "{$currentSlug} -> {$targetSlug}";
                }

                if ($changes === [] && $legacy === ($product->legacy_slugs ?? [])) {
                    return;
                }

                $this->line("#{$product->id} {$product->name}: ".implode(', ', $changes));

                if ($dryRun) {
                    return;
                }

                $product->forceFill([
                    'name' => $name,
                    'slug' => $targetSlug,
                    'legacy_slugs' => $legacy !== [] ? array_values(array_unique($legacy)) : null,
                ])->save();

                $updated++;
            });

        $this->info($dryRun
            ? 'Dry run complete.'
            : "Updated {$updated} product(s).");

        return self::SUCCESS;
    }
}
