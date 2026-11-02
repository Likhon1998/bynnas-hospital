<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::query()
            ->published()
            ->with('blogCategory')
            ->latest('published_at')
            ->paginate(9);

        return view('web.news', compact('posts'));
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->is_published && $post->published_at !== null && $post->published_at->lte(now()), 404);

        $post->load('blogCategory');

        $related = BlogPost::query()
            ->published()
            ->with('blogCategory')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('web.news-show', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}
