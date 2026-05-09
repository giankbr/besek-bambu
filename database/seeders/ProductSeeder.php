<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cupsera = Category::where('slug', 'cupsera')->value('id');
        $ecocookery = Category::where('slug', 'ecocookery')->value('id');
        $carbon = Category::where('slug', 'carbon-naturally')->value('id');

        $rows = [
            [
                'name' => 'Reusable drinkware in a greener finish',
                'icon' => '🥤',
                'price' => 18.00,
                'rating' => 5,
                'color_class' => 'p-1',
                'description' => 'Botol minum reusable dengan finishing matte hijau, food-grade dan bebas BPA.',
                'stock' => 120,
                'category_id' => $cupsera,
            ],
            [
                'name' => 'Non-stick cookware for sustainable cooking',
                'icon' => '🍲',
                'price' => 76.30,
                'rating' => 5,
                'color_class' => 'p-2',
                'description' => 'Panci anti-lengket dengan lapisan keramik bebas PFOA, hemat energi.',
                'stock' => 35,
                'category_id' => $ecocookery,
            ],
            [
                'name' => 'Kettle & Teaware eco-friendly meals',
                'icon' => '🫖',
                'price' => 104.00,
                'rating' => 5,
                'color_class' => 'p-3',
                'description' => 'Set kettle dan teaware keramik dengan glaze ramah lingkungan.',
                'stock' => 18,
                'category_id' => $ecocookery,
            ],
            [
                'name' => 'Bamboo Made (Round) Utensils Set',
                'icon' => '🥄',
                'price' => 28.27,
                'rating' => 4,
                'color_class' => 'p-4',
                'description' => 'Set sendok-spatula bambu organik dipoles minyak alami.',
                'stock' => 200,
                'category_id' => $carbon,
            ],
        ];

        foreach ($rows as $i => $row) {
            Product::create($row + [
                'slug' => Str::slug($row['name']),
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
