<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with(['blogCategory', 'author'])
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where('title', 'like', '%'.$term.'%');
            })
            ->latest('published_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog-posts.create', [
            'post' => new BlogPost,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        BlogPost::query()->create([
            ...$request->safe()->except(['_token']),
            'user_id' => $request->user()->id,
            'published_at' => $request->boolean('is_published')
                ? ($request->date('published_at') ?? now())
                : $request->date('published_at'),
        ]);

        return redirect()->route('admin.blog-posts.index')->with('status', 'Article created.');
    }

    public function edit(BlogPost $blogPost): View
    {
        return view('admin.blog-posts.edit', [
            'post' => $blogPost,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(BlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $blogPost->update([
            ...$request->safe()->except(['_token']),
            'published_at' => $request->boolean('is_published')
                ? ($blogPost->published_at ?? $request->date('published_at') ?? now())
                : $request->date('published_at'),
        ]);

        return redirect()->route('admin.blog-posts.index')->with('status', 'Article updated.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('status', 'Article deleted.');
    }
}
