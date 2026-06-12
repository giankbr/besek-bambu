<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogPostsAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_view_blog_posts_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.blog-posts.index'))
            ->assertOk()
            ->assertSee('New article', false);
    }

    public function test_admin_can_create_blog_post(): void
    {
        $this->actingAs($this->admin());

        Livewire::test('pages::admin.blog-posts.create')
            ->set('title', 'Tips Packing Hantaran')
            ->set('slug', 'tips-packing-hantaran')
            ->set('excerpt', 'Ringkasan artikel packing.')
            ->set('body', '<p>Isi artikel besek bambu.</p>')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.blog-posts.index'));

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'tips-packing-hantaran',
            'is_published' => true,
        ]);

        $this->get(route('blog.show', 'tips-packing-hantaran'))
            ->assertOk()
            ->assertSee('Tips Packing Hantaran', false);
    }

    public function test_admin_can_update_blog_post_seo_fields(): void
    {
        $post = BlogPost::create([
            'title' => 'Draft Lama',
            'slug' => 'draft-lama',
            'body' => '<p>Konten lama.</p>',
            'is_published' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin());

        Livewire::test('pages::admin.blog-posts.edit', ['post' => $post])
            ->set('title', 'Judul Baru')
            ->set('meta_title', 'SEO Title Khusus')
            ->set('meta_description', 'Deskripsi SEO khusus untuk artikel besek.')
            ->set('is_published', true)
            ->set('published_at', now()->subHour()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        $post->refresh();

        $this->assertSame('Judul Baru', $post->title);
        $this->assertSame('SEO Title Khusus', $post->meta_title);
        $this->assertTrue($post->is_published);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('SEO Title Khusus', false);
    }

    public function test_generate_blog_seo_meta_uses_excerpt(): void
    {
        $seo = generate_blog_seo_meta(
            'Panduan Besek',
            'Panduan lengkap memilih ukuran besek untuk seserahan pernikahan dengan berbagai tips praktis dari pengrajin lokal.',
        );

        $this->assertStringContainsString('Panduan Besek', $seo['meta_title']);
        $this->assertStringContainsString('seserahan', $seo['meta_description']);
    }
}
