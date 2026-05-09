<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Reusable drinkware in a greener finish', 'icon' => '🥤', 'price' => 18.00, 'rating' => 5, 'color_class' => 'p-1'],
            ['name' => 'Non-stick cookware for sustainable cooking', 'icon' => '🍲', 'price' => 76.30, 'rating' => 5, 'color_class' => 'p-2'],
            ['name' => 'Kettle & Teaware eco-friendly meals', 'icon' => '🫖', 'price' => 104.00, 'rating' => 5, 'color_class' => 'p-3'],
            ['name' => 'Bamboo Made (Round) Utensils Set', 'icon' => '🥄', 'price' => 28.27, 'rating' => 4, 'color_class' => 'p-4'],
        ];

        foreach ($rows as $i => $row) {
            Product::create($row + ['sort_order' => $i]);
        }
    }
}
