<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BoardMember;
use App\Models\Department;
use App\Models\Doctor;
use App\Support\PublicCampusStats;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(PublicCampusStats $campusStats): View
    {
        $featuredDoctors = Doctor::query()
            ->active()
            ->featured()
            ->with('department')
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        if ($featuredDoctors->isEmpty()) {
            $featuredDoctors = Doctor::query()
                ->active()
                ->with('department')
                ->orderBy('sort_order')
                ->limit(8)
                ->get();
        }

        return view('web.about', [
            'boardMembers' => BoardMember::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'doctors' => $featuredDoctors,
            'departments' => Department::query()
                ->active()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'stats' => $campusStats->snapshot(),
        ]);
    }
}
