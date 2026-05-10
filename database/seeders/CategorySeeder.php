<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title' => '7×7', 'slug' => '7x7', 'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80'],
            ['title' => '10×10', 'slug' => '10x10', 'image_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80'],
            ['title' => '12×12', 'slug' => '12x12', 'image_url' => 'https://images.unsplash.com/photo-1591261730799-ee4e6c2d1e1d?auto=format&fit=crop&w=600&q=80'],
            ['title' => '14×14', 'slug' => '14x14', 'image_url' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=600&q=80'],
            ['title' => '16×16', 'slug' => '16x16', 'image_url' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=600&q=80'],
            ['title' => '20×20', 'slug' => '20x20', 'image_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80'],
        ];

        foreach ($rows as $i => $row) {
            Category::updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['sort_order' => $i],
            );
        }
    }
}
