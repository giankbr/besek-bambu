<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@besek.test',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

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
