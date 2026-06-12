<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const INDEX_PER_PAGE = 9;

    public function index(): View
    {
        $posts = BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate(self::INDEX_PER_PAGE)
            ->withQueryString();

        return view('pages.blog.index', [
            'posts' => $posts,
        ]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless(
            $post->is_published
            && $post->published_at
            && $post->published_at->lte(now()),
            404,
        );

        $related = BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKeyNot($post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}
