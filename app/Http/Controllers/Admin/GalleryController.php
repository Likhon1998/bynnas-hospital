<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
        Gallery::query()->create($this->galleryPayload($request));

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item created.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', ['item' => $gallery]);
    }

    public function update(GalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $gallery->update($this->galleryPayload($request, $gallery->image));

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item updated.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->deleteStoredImage($gallery->image);
        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('status', 'Gallery item deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function galleryPayload(GalleryRequest $request, ?string $currentImage = null): array
    {
        $data = $request->safe()->except(['image_upload']);

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('galleries', 'public');
            $data['image'] = '/storage/'.$path;
            $this->deleteStoredImage($currentImage);
        }

        return $data;
    }

    private function deleteStoredImage(?string $image): void
    {
        if (! filled($image)) {
            return;
        }

        $path = parse_url($image, PHP_URL_PATH) ?: $image;

        if (! is_string($path) || ! str_contains($path, '/storage/galleries/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
