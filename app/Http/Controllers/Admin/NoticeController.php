<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoticeRequest;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $notices = Notice::query()
            ->orderByDesc('published_on')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15);

        return view('admin.notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('admin.notices.create', [
            'notice' => new Notice([
                'published_on' => now()->toDateString(),
                'is_urgent' => false,
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(NoticeRequest $request): RedirectResponse
    {
        Notice::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.notices.index')->with('status', 'Notice created.');
    }

    public function edit(Notice $notice): View
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(NoticeRequest $request, Notice $notice): RedirectResponse
    {
        $notice->update($request->safe()->except(['_token']));

        return redirect()->route('admin.notices.index')->with('status', 'Notice updated.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')->with('status', 'Notice deleted.');
    }
}
