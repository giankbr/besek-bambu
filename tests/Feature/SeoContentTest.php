<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Database\Seeders\BlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_wholesale_page_targets_grosir_keywords(): void
    {
        $response = $this->get('/grosir');

        $response->assertOk();
        $response->assertSee('Grosir &amp; Custom Besek Bambu', false);
        $response->assertSee('grosir mulai 25', false);
        $response->assertSee('custom logo', false);
    }

    public function test_wholesale_redirects_from_english_path(): void
    {
        $this->get('/wholesale')->assertRedirect('/grosir');
    }

    public function test_blog_post_seeder_creates_hundred_articles(): void
    {
        $this->seed(BlogPostSeeder::class);

        $this->assertSame(100, BlogPost::query()->where('is_published', true)->count());
        $this->assertDatabaseHas('blog_posts', ['slug' => 'cara-merawat-besek-bambu-awet']);
        $this->assertDatabaseHas('blog_posts', ['slug' => 'besek-bambu-corporate-gifting']);
        $this->assertDatabaseHas('blog_posts', ['slug' => 'besek-bambu-15x15-hantaran-pernikahan']);
        $this->assertDatabaseHas('blog_posts', ['slug' => 'besek-harga-grosir-per-lusin']);
        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'cara-merawat-besek-bambu-awet',
            'title_en' => 'How to Care for Bamboo Baskets So They Last',
        ]);
    }

    public function test_blog_index_paginates_published_posts(): void
    {
        $this->seed(BlogPostSeeder::class);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('/blog?page=2', false);

        $this->get('/blog?page=2')
            ->assertOk()
            ->assertSee('aria-current="page"', false);
    }

    public function test_blog_index_lists_published_posts(): void
    {
        BlogPost::create([
            'title' => 'Artikel Test',
            'slug' => 'artikel-test',
            'excerpt' => 'Ringkasan artikel.',
            'body' => '<p>Konten artikel.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 0,
        ]);

        BlogPost::create([
            'title' => 'Draft',
            'slug' => 'draft',
            'excerpt' => 'Hidden',
            'body' => '<p>Hidden</p>',
            'is_published' => false,
            'published_at' => now()->subDay(),
            'sort_order' => 1,
        ]);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('Artikel Test', false);
        $response->assertDontSee('>Draft<', false);
    }

    public function test_blog_post_page_outputs_article_schema(): void
    {
        $post = BlogPost::create([
            'title' => 'Panduan Besek',
            'slug' => 'panduan-besek',
            'excerpt' => 'Panduan praktis.',
            'body' => '<p>Isi panduan besek bambu.</p>',
            'meta_description' => 'Meta khusus artikel besek.',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 0,
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertOk();
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('Meta khusus artikel besek.', false);
        $response->assertSee('property="og:type" content="article"', false);
        $response->assertSee('property="article:published_time"', false);
        $response->assertSee('blog-share', false);
        $response->assertSee('wa.me', false);
        $response->assertSee('facebook.com/sharer', false);
    }

    public function test_social_share_urls_encode_title_and_url(): void
    {
        $urls = social_share_urls('Ide Hantaran', 'https://besekbambu.com/blog/test');

        $this->assertStringContainsString('wa.me', $urls['whatsapp']);
        $this->assertStringContainsString('Ide%20Hantaran', $urls['whatsapp']);
        $this->assertStringContainsString('facebook.com/sharer', $urls['facebook']);
        $this->assertStringContainsString('twitter.com/intent/tweet', $urls['twitter']);
    }

    public function test_shop_page_uses_enhanced_meta_title(): void
    {
        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertSee('Produk Besek Bambu 7×7–20×20', false);
        $response->assertSee('Jelajahi katalog besek bambu handmade berbagai ukuran', false);
    }

    public function test_generate_product_seo_meta_includes_keywords(): void
    {
        $seo = generate_product_seo_meta('Besek Natural 15×15', 'Anyaman rapi.', '15×15');

        $this->assertStringContainsString('Besek Natural 15×15', $seo['meta_title']);
        $this->assertStringContainsString('15×15', $seo['meta_title']);
        $this->assertStringContainsString('hantaran', $seo['meta_description']);
        $this->assertStringContainsString('hampers', $seo['meta_description']);
    }
}
