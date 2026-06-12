<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Database\Seeders\BlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_show_displays_english_content_when_locale_is_en(): void
    {
        BlogPost::create([
            'title' => 'Judul Indonesia',
            'title_en' => 'English Title',
            'slug' => 'artikel-bilingual',
            'excerpt' => 'Ringkasan ID',
            'excerpt_en' => 'English excerpt',
            'body' => '<p>Konten Indonesia</p>',
            'body_en' => '<p>English body content</p>',
            'meta_title_en' => 'English SEO Title',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 0,
        ]);

        $this->withSession(['locale' => 'en'])
            ->get('/blog/artikel-bilingual?lang=en')
            ->assertOk()
            ->assertSee('English Title', false)
            ->assertSee('English body content', false)
            ->assertSee('English SEO Title', false)
            ->assertDontSee('Judul Indonesia', false);
    }

    public function test_blog_show_falls_back_to_indonesian_when_english_missing(): void
    {
        BlogPost::create([
            'title' => 'Hanya Indonesia',
            'slug' => 'hanya-indonesia',
            'body' => '<p>Isi Indonesia</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 0,
        ]);

        $this->withSession(['locale' => 'en'])
            ->get('/blog/hanya-indonesia?lang=en')
            ->assertOk()
            ->assertSee('Hanya Indonesia', false)
            ->assertSee('Isi Indonesia', false);
    }

    public function test_blog_seeder_populates_english_fields_for_all_posts(): void
    {
        $this->seed(BlogPostSeeder::class);

        $this->assertSame(100, BlogPost::query()->whereNotNull('title_en')->where('title_en', '!=', '')->count());
        $this->assertSame(100, BlogPost::query()->whereNotNull('body_en')->where('body_en', '!=', '')->count());

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'cara-merawat-besek-bambu-awet',
            'title_en' => 'How to Care for Bamboo Baskets So They Last',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'besek-bambu-15x15-hantaran-pernikahan',
        ]);
    }
}
