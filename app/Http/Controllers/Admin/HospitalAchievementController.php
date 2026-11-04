<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HospitalAchievementRequest;
use App\Models\HospitalAchievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HospitalAchievementController extends Controller
{
    public function index(): View
    {
        $achievements = HospitalAchievement::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(20);

        return view('admin.hospital-achievements.index', compact('achievements'));
    }

    public function create(): View
    {
        return view('admin.hospital-achievements.create', [
            'achievement' => new HospitalAchievement(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(HospitalAchievementRequest $request): RedirectResponse
    {
        HospitalAchievement::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.hospital-achievements.index')->with('status', 'Achievement added. Active items appear on the public About page.');
    }

    public function edit(HospitalAchievement $hospitalAchievement): View
    {
        return view('admin.hospital-achievements.edit', [
            'achievement' => $hospitalAchievement,
        ]);
    }

    public function update(HospitalAchievementRequest $request, HospitalAchievement $hospitalAchievement): RedirectResponse
    {
        $hospitalAchievement->update($request->safe()->except(['_token']));

        return redirect()->route('admin.hospital-achievements.index')->with('status', 'Achievement updated.');
    }

    public function destroy(HospitalAchievement $hospitalAchievement): RedirectResponse
    {
        $hospitalAchievement->delete();

        return redirect()->route('admin.hospital-achievements.index')->with('status', 'Achievement removed.');
    }
}
