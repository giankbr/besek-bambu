<?php

namespace App\Console\Commands\Media;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncMediaCommand extends Command
{
    protected $signature = 'media:sync
                            {--disk=public : Filesystem disk to scan}
                            {--prefix=* : Optional path prefixes to limit the scan (defaults to common upload folders)}
                            {--dry-run : Only report what would be added, do not insert rows}
                            {--prune : Remove media rows that no longer point to an existing file on disk}';

    protected $description = 'Index files already stored on disk into the media library table.';

    public function handle(): int
    {
        $disk = $this->option('disk');
        $prefixes = $this->option('prefix');

        if ($prefixes === []) {
            $prefixes = ['products', 'categories', 'gallery', 'settings', 'media'];
        }

        $dryRun = (bool) $this->option('dry-run');
        $prune = (bool) $this->option('prune');

        $this->line(sprintf('Scanning disk "%s" prefixes: %s', $disk, implode(', ', $prefixes)));

        $fs = Storage::disk($disk);
        $imported = 0;
        $skipped = 0;

        foreach ($prefixes as $prefix) {
            $files = $fs->allFiles($prefix);

            if ($files === []) {
                $this->line(sprintf('  · %s — no files', $prefix));

                continue;
            }

            $this->line(sprintf('  · %s — %d file(s)', $prefix, count($files)));

            foreach ($files as $path) {
                if (Media::where('disk', $disk)->where('path', $path)->exists()) {
                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->info('    + would import: '.$path);
                    $imported++;

                    continue;
                }

                $mime = $fs->mimeType($path) ?: null;
                $size = $fs->size($path) ?: null;
                $width = null;
                $height = null;

                if ($mime && str_starts_with($mime, 'image/')) {
                    $abs = $fs->path($path);
                    $info = @getimagesize($abs);
                    if ($info !== false) {
                        [$width, $height] = $info;
                    }
                }

                Media::create([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => basename($path),
                    'mime' => $mime,
                    'size' => $size,
                    'width' => $width,
                    'height' => $height,
                ]);

                $imported++;
            }
        }

        $this->newLine();
        $this->info(sprintf('Imported: %d, skipped (already indexed): %d', $imported, $skipped));

        if ($prune) {
            $this->line('Pruning rows pointing to missing files…');
            $pruned = 0;

            Media::query()->where('disk', $disk)->chunkById(200, function ($rows) use ($fs, $dryRun, &$pruned) {
                foreach ($rows as $media) {
                    if ($fs->exists($media->path)) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->warn('  - would prune: '.$media->path);
                    } else {
                        $media->delete();
                    }

                    $pruned++;
                }
            });

            $this->info(sprintf('Pruned: %d', $pruned));
        }

        return self::SUCCESS;
    }
}
