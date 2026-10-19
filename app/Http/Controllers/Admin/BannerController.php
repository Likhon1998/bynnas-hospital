<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
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
        return view('admin.banners.create', ['banner' => new Banner(['is_active' => true, 'placement' => 'hero', 'sort_order' => 0])]);
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        Banner::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $banner->update($request->safe()->except(['_token']));

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }
}
