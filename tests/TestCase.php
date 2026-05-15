<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    private static bool $viteManifestPrepared = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureViteManifestForTests();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    private function ensureViteManifestForTests(): void
    {
        if (self::$viteManifestPrepared) {
            return;
        }

        $buildDir = public_path('build');
        $manifestPath = $buildDir.'/manifest.json';

        if (! is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        $requiredEntries = [
            'resources/js/storefront.js' => 'assets/storefront-test.js',
            'resources/css/storefront.css' => 'assets/storefront-test.css',
            'resources/css/app.css' => 'assets/app-test.css',
            'resources/js/app.js' => 'assets/app-test.js',
        ];

        $manifest = file_exists($manifestPath)
            ? json_decode((string) file_get_contents($manifestPath), true)
            : [];

        if (! is_array($manifest)) {
            $manifest = [];
        }

        foreach ($requiredEntries as $source => $file) {
            if (isset($manifest[$source])) {
                continue;
            }

            $manifest[$source] = [
                'file' => $file,
                'src' => $source,
                'isEntry' => true,
            ];

            $assetPath = $buildDir.'/'.$file;
            $assetDir = dirname($assetPath);

            if (! is_dir($assetDir)) {
                mkdir($assetDir, 0755, true);
            }

            if (! file_exists($assetPath)) {
                file_put_contents($assetPath, '');
            }
        }

        file_put_contents($manifestPath, json_encode($manifest, JSON_THROW_ON_ERROR));

        self::$viteManifestPrepared = true;
    }
}
