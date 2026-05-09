<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title' => 'Explore CupsEra', 'slug' => 'cupsera', 'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Indoor Ecocookery', 'slug' => 'ecocookery', 'image_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Carbon Naturally', 'slug' => 'carbon-naturally', 'image_url' => 'https://images.unsplash.com/photo-1591261730799-ee4e6c2d1e1d?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Eco FreshPicker', 'slug' => 'freshpicker', 'image_url' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=600&q=80'],
        ];

        foreach ($rows as $i => $row) {
            Category::create($row + ['sort_order' => $i]);
        }
    }
}
