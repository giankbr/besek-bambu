<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeployCheckCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        if (File::exists(public_path('hot'))) {
            File::delete(public_path('hot'));
        }

        parent::tearDown();
    }

    public function test_passes_with_production_ready_env(): void
    {
        $this->withProductionEnv();

        $this->artisan('deploy:check')
            ->expectsOutputToContain('Memeriksa kesiapan production')
            ->expectsOutputToContain('Siap production.')
            ->assertExitCode(0);
    }

    public function test_fails_when_app_debug_is_true_in_production_env(): void
    {
        $this->withProductionEnv(['APP_DEBUG' => 'true']);

        $this->artisan('deploy:check')
            ->assertExitCode(1);
    }

    public function test_fails_when_midtrans_sandbox_keys_used_in_production(): void
    {
        $this->withProductionEnv([
            'MIDTRANS_SERVER_KEY' => 'SB-Mid-server-test',
            'MIDTRANS_CLIENT_KEY' => 'SB-Mid-client-test',
        ]);

        $this->artisan('deploy:check')
            ->expectsOutputToContain('kunci Sandbox')
            ->assertExitCode(1);
    }

    public function test_strict_mode_fails_on_warnings(): void
    {
        File::put(public_path('hot'), 'http://127.0.0.1:5173');

        $this->artisan('deploy:check', ['--strict' => true])
            ->assertExitCode(1);
    }

    private function withProductionEnv(array $overrides = []): void
    {
        $defaults = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            'APP_URL' => 'https://besekbambu.com',
            'TRUSTED_PROXIES' => '10.0.0.0/8',
            'LOG_LEVEL' => 'warning',
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => 'besek_bambu',
            'DB_USERNAME' => 'app',
            'DB_PASSWORD' => 'secret',
            'SESSION_DRIVER' => 'database',
            'SESSION_ENCRYPT' => 'true',
            'SESSION_SECURE_COOKIE' => 'true',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.example.com',
            'MAIL_USERNAME' => 'mailer',
            'MAIL_PASSWORD' => 'secret',
            'MAIL_FROM_ADDRESS' => 'order@besekbambu.com',
            'MAIL_ADMIN_ADDRESS' => 'admin@besekbambu.com',
            'MIDTRANS_SERVER_KEY' => 'Mid-server-prod',
            'MIDTRANS_CLIENT_KEY' => 'Mid-client-prod',
            'MIDTRANS_IS_PRODUCTION' => 'true',
            'MIDTRANS_IS_3DS' => 'true',
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
