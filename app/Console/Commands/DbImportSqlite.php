<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbImportSqlite extends Command
{
    protected $signature = 'db:import-sqlite {--force : Skip confirmation}';

    protected $description = 'Import data from database/database.sqlite into the default MySQL connection';

    /** @var list<string> */
    private array $tables = [
        'users',
        'password_reset_tokens',
        'categories',
        'products',
        'product_variants',
        'product_price_tiers',
        'product_images',
        'gallery_items',
        'reviews',
        'coupons',
        'settings',
        'orders',
        'order_items',
        'contact_messages',
        'wishlist_items',
        'product_reviews',
        'cart_snapshots',
        'activity_logs',
        'media',
    ];

    public function handle(): int
    {
        if (config('database.default') === 'sqlite') {
            $this->error('Set DB_CONNECTION=mysql in .env before importing.');

            return self::FAILURE;
        }

        $sqlitePath = database_path('database.sqlite');
        if (! is_file($sqlitePath)) {
            $this->error('SQLite file not found: '.$sqlitePath);

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This replaces data in MySQL tables. Continue?')) {
            return self::SUCCESS;
        }

        config(['database.connections.sqlite.database' => $sqlitePath]);
        DB::purge('sqlite');

        $mysql = DB::connection();
        $sqlite = DB::connection('sqlite');

        $mysql->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables as $table) {
            if (! Schema::connection('sqlite')->hasTable($table) || ! Schema::hasTable($table)) {
                $this->warn("Skipping missing table: {$table}");

                continue;
            }

            $mysql->table($table)->truncate();

            $rows = $sqlite->table($table)->get();
            if ($rows->isEmpty()) {
                $this->line("{$table}: 0 rows");

                continue;
            }

            $payload = $rows->map(fn ($row) => (array) $row)->all();
            foreach (array_chunk($payload, 100) as $chunk) {
                $mysql->table($table)->insert($chunk);
            }

            $this->line("{$table}: {$rows->count()} rows");
        }

        $mysql->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('SQLite data imported into MySQL.');

        return self::SUCCESS;
    }
}
