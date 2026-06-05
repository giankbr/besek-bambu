<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('production')) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@besek.test',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        }

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            GalleryItemSeeder::class,
            ProductReviewSeeder::class,
            CouponSeeder::class,
        ]);
    }
}
