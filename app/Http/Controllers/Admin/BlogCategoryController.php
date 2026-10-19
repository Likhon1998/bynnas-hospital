<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        $categories = BlogCategory::query()
            ->withCount('blogPosts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.blog-categories.create', ['category' => new BlogCategory(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(BlogCategoryRequest $request): RedirectResponse
    {
        BlogCategory::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.blog-categories.index')->with('status', 'Category created.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blog-categories.edit', ['category' => $blogCategory]);
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update($request->safe()->except(['_token']));

        return redirect()->route('admin.blog-categories.index')->with('status', 'Category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        try {
            $blogCategory->delete();
        } catch (QueryException) {
            return back()->with('error', 'This category cannot be deleted while posts are linked to it.');
        }

        return redirect()->route('admin.blog-categories.index')->with('status', 'Category deleted.');
    }
}
