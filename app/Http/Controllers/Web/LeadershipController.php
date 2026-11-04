<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BoardMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LeadershipController extends Controller
{
    public function role(string $role): View
    {
        if (! BoardMember::isValidRole($role)) {
            throw new NotFoundHttpException;
        }

        $members = BoardMember::query()
            ->active()
            ->role($role)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('web.leadership.role', [
            'role' => $role,
            'roleTitle' => BoardMember::ROLES[$role],
            'members' => $members,
        ]);
    }

    public function show(BoardMember $boardMember): RedirectResponse
    {
        if (! $boardMember->is_active) {
            throw new NotFoundHttpException;
        }

        return redirect()->route('about.leadership.role', $boardMember->role);
    }
}
