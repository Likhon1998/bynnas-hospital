<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $currentNotices = Notice::query()
            ->published()
            ->orderByDesc('is_urgent')
            ->orderByDesc('published_on')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $previousNotices = Notice::query()
            ->previous()
            ->orderByDesc('published_on')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12);

        return view('web.notices', [
            'currentNotices' => $currentNotices,
            'previousNotices' => $previousNotices,
        ]);
    }
}
