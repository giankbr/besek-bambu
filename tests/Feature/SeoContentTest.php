<?php

namespace Tests\Feature;

use App\Models\BlogPost;
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
