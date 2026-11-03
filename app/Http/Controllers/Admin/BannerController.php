<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.banners.create', [
            'banner' => new Banner(['is_active' => true, 'placement' => 'hero', 'sort_order' => 0]),
            'placements' => Banner::placements(),
        ]);
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        Banner::query()->create($this->bannerPayload($request));

        return redirect()->route('admin.banners.index')->with('status', 'Banner created. It will appear on the public website when active.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'banner' => $banner,
            'placements' => Banner::placements(),
        ]);
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $banner->update($this->bannerPayload($request, $banner->image));

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->deleteStoredImage($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function bannerPayload(BannerRequest $request, ?string $currentImage = null): array
    {
        $data = $request->safe()->except(['image_upload']);

        if ($request->hasFile('image_upload')) {
            $path = $request->file('image_upload')->store('banners', 'public');
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

        if (! is_string($path) || ! str_contains($path, '/storage/banners/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
