<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewers = [
            ['name' => 'Andini', 'email' => 'andini@example.com'],
            ['name' => 'Budi', 'email' => 'budi@example.com'],
            ['name' => 'Citra', 'email' => 'citra@example.com'],
        ];

        $users = collect($reviewers)->map(fn ($r) => User::firstOrCreate(
            ['email' => $r['email']],
            ['name' => $r['name'], 'password' => bcrypt('password')],
        ));

        $samples = [
            ['rating' => 5, 'title' => 'Beautiful craftsmanship', 'body' => 'Even better in person. The weave is tight and the finish feels natural.'],
            ['rating' => 5, 'title' => 'Worth every rupiah', 'body' => 'Sturdy, lightweight, and perfect for our weekend market trips.'],
            ['rating' => 4, 'title' => 'Lovely piece', 'body' => 'A small kink in one of the corners but the overall quality is great.'],
            ['rating' => 5, 'title' => 'Will buy again', 'body' => 'Bought one as a gift, ended up keeping a second for ourselves.'],
        ];

        Product::take(5)->get()->each(function (Product $product) use ($users, $samples) {
            foreach ($users as $i => $user) {
                $sample = $samples[($product->id + $i) % count($samples)];

                ProductReview::firstOrCreate(
                    ['product_id' => $product->id, 'user_id' => $user->id, 'order_id' => null],
                    array_merge($sample, ['is_approved' => true]),
                );
            }
        });
    }
}
