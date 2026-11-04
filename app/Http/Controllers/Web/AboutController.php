<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BoardMember;
use App\Models\HospitalAchievement;
use App\Support\PublicCampusStats;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(PublicCampusStats $campusStats): View
    {
        $boardMembers = BoardMember::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $rolePages = $boardMembers
            ->groupBy('role')
            ->filter(fn ($group, $role) => BoardMember::isValidRole((string) $role))
            ->map(fn ($group, $role) => [
                'label' => BoardMember::ROLES[$role],
                'count' => $group->count(),
            ])
            ->sortBy(fn (array $item, string $role) => array_search($role, array_keys(BoardMember::ROLES), true));

        return view('web.about', [
            'boardMembers' => $boardMembers,
            'rolePages' => $rolePages,
            'achievements' => HospitalAchievement::query()
                ->active()
                ->orderBy('sort_order')
                ->orderByDesc('year')
                ->orderBy('id')
                ->get(),
            'stats' => $campusStats->snapshot(),
        ]);
    }
}
