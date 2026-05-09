<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GalleryItemSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title' => 'SizzlePro Non-', 'subtitle' => 'Stick Pan', 'image_url' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=600&q=80', 'color_class' => 'g-1', 'drop' => false],
            ['title' => 'Grain Slice', 'subtitle' => 'Board Duo', 'image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80', 'color_class' => 'g-2', 'drop' => true],
            ['title' => 'Bamboo', 'subtitle' => 'Utensil Set', 'image_url' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80', 'color_class' => 'g-3', 'drop' => false],
            ['title' => 'Glow Pot', 'subtitle' => 'Ceramic', 'image_url' => 'https://images.unsplash.com/photo-1591261730799-ee4e6c2d1e1d?auto=format&fit=crop&w=600&q=80', 'color_class' => 'g-4', 'drop' => true],
            ['title' => 'StoneSip', 'subtitle' => 'Ceramic Cup', 'image_url' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=600&q=80', 'color_class' => 'g-5', 'drop' => false],
        ];

        foreach ($rows as $i => $row) {
            GalleryItem::create($row + ['sort_order' => $i]);
        }
    }
}
