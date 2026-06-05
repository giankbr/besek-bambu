<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCheckCommand extends Command
{
    protected $signature = 'deploy:check {--strict : Anggap peringatan sebagai gagal}';

    protected $description = 'Cek kesiapan production: env, Midtrans, mail, keamanan, dan asset build.';

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    /** @var list<string> */
    private array $passed = [];

    public function handle(): int
    {
        $this->info('Memeriksa kesiapan production…');
        $this->newLine();

        $this->checkApp();
        $this->checkSecurity();
        $this->checkDatabase();
        $this->checkSession();
        $this->checkMail();
        $this->checkMidtrans();
        $this->checkAssets();

        $this->renderResults();

        if ($this->errors !== []) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $this->warnings !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkApp(): void
    {
        $this->section('Aplikasi');

        $this->requireEquals('APP_ENV', 'production', env('APP_ENV'));
        $this->requireFalse('APP_DEBUG', $this->envBool('APP_DEBUG', true));
        $this->requireFilled('APP_KEY', env('APP_KEY'));

        $url = rtrim((string) env('APP_URL', ''), '/');

        if ($url === '') {
            $this->addError('APP_URL', 'wajib diisi');
        } elseif (! str_starts_with($url, 'https://')) {
            $this->addWarning('APP_URL', 'gunakan HTTPS di production');
        } elseif ($this->looksLikeDevHost($url)) {
            $this->addWarning('APP_URL', 'masih menunjuk ke host dev (localhost / ngrok)');
        } else {
            $this->markOk('APP_URL', $url);
        }

        $logLevel = strtolower((string) env('LOG_LEVEL', 'debug'));

        if (in_array($logLevel, ['debug', 'trace'], true)) {
            $this->addWarning('LOG_LEVEL', "sebaiknya warning atau error (sekarang: {$logLevel})");
        } else {
            $this->markOk('LOG_LEVEL', $logLevel);
        }
    }

    private function checkSecurity(): void
    {
        $this->section('Keamanan & proxy');

        $proxies = trim((string) env('TRUSTED_PROXIES', '*'));

        if ($proxies === '' || $proxies === '*') {
            $this->addWarning('TRUSTED_PROXIES', 'jangan "*" di production — pin CIDR load balancer');
        } else {
            $this->markOk('TRUSTED_PROXIES', 'sudah dipin');
        }
    }

    private function checkDatabase(): void
    {
        $this->section('Database');

        $driver = (string) env('DB_CONNECTION', '');

        if ($driver === 'sqlite') {
            $this->addWarning('DB_CONNECTION', 'sqlite tidak disarankan di production');
        } else {
            $this->markOk('DB_CONNECTION', $driver !== '' ? $driver : '(default)');
        }

        $this->requireFilled('DB_DATABASE', env('DB_DATABASE'));
        $this->requireFilled('DB_USERNAME', env('DB_USERNAME'));
        $this->requireFilled('DB_PASSWORD', env('DB_PASSWORD'), allowEmptyPassword: false);
    }

    private function checkSession(): void
    {
        $this->section('Sesi');

        $driver = (string) env('SESSION_DRIVER', 'file');

        if ($driver === 'file') {
            $this->addWarning('SESSION_DRIVER', 'database atau redis lebih cocok untuk multi-server');
        } else {
            $this->markOk('SESSION_DRIVER', $driver);
        }

        $this->requireTrue('SESSION_ENCRYPT', $this->envBool('SESSION_ENCRYPT', false));
        $this->requireTrue('SESSION_SECURE_COOKIE', $this->envBool('SESSION_SECURE_COOKIE', false));
    }

    private function checkMail(): void
    {
        $this->section('Email');

        $mailer = (string) env('MAIL_MAILER', 'log');

        if ($mailer === 'log' || $mailer === 'array') {
            $this->addError('MAIL_MAILER', "gunakan smtp di production (sekarang: {$mailer})");
        } else {
            $this->markOk('MAIL_MAILER', $mailer);
        }

        $from = (string) env('MAIL_FROM_ADDRESS', '');

        if ($from === '' || str_contains($from, 'example.com')) {
            $this->addError('MAIL_FROM_ADDRESS', 'isi alamat domain toko yang valid');
        } else {
            $this->markOk('MAIL_FROM_ADDRESS', $from);
        }

        $admin = (string) env('MAIL_ADMIN_ADDRESS', '');

        if ($admin === '' || str_contains($admin, 'example.com')) {
            $this->addWarning('MAIL_ADMIN_ADDRESS', 'isi alamat admin untuk notifikasi pesanan');
        } else {
            $this->markOk('MAIL_ADMIN_ADDRESS', $admin);
        }

        if ($mailer === 'smtp') {
            $this->requireFilled('MAIL_HOST', env('MAIL_HOST'));
            $this->requireFilled('MAIL_USERNAME', env('MAIL_USERNAME'));
            $this->requireFilled('MAIL_PASSWORD', env('MAIL_PASSWORD'));
        }
    }

    private function checkMidtrans(): void
    {
        $this->section('Midtrans');

        $serverKey = (string) env('MIDTRANS_SERVER_KEY', '');
        $clientKey = (string) env('MIDTRANS_CLIENT_KEY', '');
        $isProduction = $this->envBool('MIDTRANS_IS_PRODUCTION', false);

        $this->requireFilled('MIDTRANS_SERVER_KEY', $serverKey);
        $this->requireFilled('MIDTRANS_CLIENT_KEY', $clientKey);
        $this->requireTrue('MIDTRANS_IS_PRODUCTION', $isProduction);

        if ($isProduction && $this->looksLikeSandboxKey($serverKey, $clientKey)) {
            $this->addError('MIDTRANS_*_KEY', 'kunci Sandbox (SB-Mid-) tidak cocok dengan MIDTRANS_IS_PRODUCTION=true');
        } elseif ($serverKey !== '' && $clientKey !== '') {
            $this->markOk('MIDTRANS_*_KEY', 'terisi');
        }

        if (! $this->envBool('MIDTRANS_IS_3DS', true)) {
            $this->addWarning('MIDTRANS_IS_3DS', 'sebaiknya true untuk kartu kredit');
        } else {
            $this->markOk('MIDTRANS_IS_3DS', 'aktif');
        }

        $appUrl = rtrim((string) env('APP_URL', ''), '/');

        if ($appUrl !== '' && ! $this->looksLikeDevHost($appUrl)) {
            $this->markOk('Webhook URL', $appUrl.'/payment/notification');
            $this->line('  <comment>→ daftarkan URL di atas di Midtrans Dashboard → Settings → Configuration</comment>');
        } else {
            $this->addWarning('Webhook URL', 'set APP_URL HTTPS dulu, lalu daftar {APP_URL}/payment/notification di Midtrans');
        }
    }

    private function checkAssets(): void
    {
        $this->section('Asset frontend');

        $hotFile = public_path('hot');

        if (is_file($hotFile)) {
            $this->addWarning('public/hot', 'file Vite dev masih ada — jalankan npm run build && rm -f public/hot');
        } else {
            $this->markOk('public/hot', 'tidak ada (asset production)');
        }

        $manifest = public_path('build/manifest.json');

        if (! is_file($manifest)) {
            $this->addWarning('public/build/manifest.json', 'belum ada — jalankan npm run build');
        } else {
            $this->markOk('public/build/manifest.json', 'ada');
        }
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>{$title}</>");
    }

    private function requireFilled(string $key, mixed $value, bool $allowEmptyPassword = true): void
    {
        if ($key === 'DB_PASSWORD' && $allowEmptyPassword) {
            // Password boleh kosong di beberapa setup, tapi production biasanya ada.
        }

        if (! filled($value)) {
            $this->addError($key, 'wajib diisi');

            return;
        }

        if ($key === 'APP_KEY' || str_contains($key, 'PASSWORD') || str_contains($key, 'KEY')) {
            $this->markOk($key, 'terisi');

            return;
        }

        $this->markOk($key, (string) $value);
    }

    private function requireEquals(string $key, string $expected, mixed $actual): void
    {
        if ((string) $actual !== $expected) {
            $this->addError($key, "harus {$expected} (sekarang: ".($actual ?: '(kosong)').')');

            return;
        }

        $this->markOk($key, (string) $actual);
    }

    private function requireTrue(string $key, bool $value): void
    {
        if (! $value) {
            $this->addError($key, 'harus true');

            return;
        }

        $this->markOk($key, 'true');
    }

    private function requireFalse(string $key, bool $value): void
    {
        if ($value) {
            $this->addError($key, 'harus false');

            return;
        }

        $this->markOk($key, 'false');
    }

    private function markOk(string $key, string $detail): void
    {
        $this->passed[] = "{$key}: {$detail}";
        $this->line("  <fg=green>✓</> {$key} <fg=gray>— {$detail}</>");
    }

    private function addWarning(string $key, string $message): void
    {
        $this->warnings[] = "{$key}: {$message}";
        $this->line("  <fg=yellow>!</> {$key} <fg=gray>— {$message}</>");
    }

    private function addError(string $key, string $message): void
    {
        $this->errors[] = "{$key}: {$message}";
        $this->line("  <fg=red>✗</> {$key} <fg=gray>— {$message}</>");
    }

    private function renderResults(): void
    {
        $this->newLine();
        $this->line(str_repeat('─', 52));

        $passCount = count($this->passed);
        $warnCount = count($this->warnings);
        $errorCount = count($this->errors);

        $this->line("Lulus: {$passCount}  Peringatan: {$warnCount}  Gagal: {$errorCount}");

        if ($errorCount > 0) {
            $this->newLine();
            $this->error('Belum siap production. Perbaiki item gagal di atas.');

            return;
        }

        if ($warnCount > 0) {
            $this->newLine();
            $this->warn('Lulus dengan peringatan. Jalankan ulang dengan --strict setelah diperbaiki.');

            return;
        }

        $this->newLine();
        $this->info('Siap production.');
    }

    private function envBool(string $key, bool $default): bool
    {
        $value = env($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    private function looksLikeDevHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === ''
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || str_contains($host, 'ngrok')
            || str_ends_with($host, '.local');
    }

    private function looksLikeSandboxKey(string $serverKey, string $clientKey): bool
    {
        return str_starts_with($serverKey, 'SB-Mid-')
            || str_starts_with($clientKey, 'SB-Mid-');
    }
}
