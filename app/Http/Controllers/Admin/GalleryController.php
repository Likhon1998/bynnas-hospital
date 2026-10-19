<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $items = Gallery::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(18);

        return view('admin.galleries.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.galleries.create', ['item' => new Gallery(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(GalleryRequest $request): RedirectResponse
    {
        Gallery::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item created.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', ['item' => $gallery]);
    }

    public function update(GalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $gallery->update($request->safe()->except(['_token']));

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item updated.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item deleted.');
    }
}
