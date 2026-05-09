<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['quote' => 'Our sustainable bamboo utensils are perfect for daily use!', 'author_name' => 'Mira Aldine', 'author_role' => 'Home Cook', 'is_featured' => false],
            ['quote' => "Besek's glass jars are awesome for storage, and the bamboo utensils are perfect for daily use!", 'author_name' => 'Jane Cooper', 'author_role' => 'Nutritionist', 'is_featured' => false],
            ['quote' => 'Fantastic products and fast delivery. My kitchen feels so much greener!', 'author_name' => 'Darlene Robertson', 'author_role' => 'Culinary Instructor', 'is_featured' => true],
            ['quote' => "Love Besek's eco-style! Glass jars keep things fresh, and bamboo utensils are so chic.", 'author_name' => 'Jacob Jones', 'author_role' => 'Food Blogger', 'is_featured' => false],
            ['quote' => 'The sustainable bamboo utensils are perfect for daily use.', 'author_name' => 'Esther Howard', 'author_role' => 'Sous Chef', 'is_featured' => false],
        ];

        foreach ($rows as $i => $row) {
            Review::create($row + ['sort_order' => $i]);
        }
    }
}
