<?php

namespace App\View\Composers;

use App\Models\Acknowledgement;
use App\Models\Banner;
use App\Models\BoardMember;
use App\Models\Department;
use App\Models\HospitalSetting;
use App\Models\Service;
use Illuminate\View\View;

class PublicLayoutComposer
{
    public function compose(View $view): void
    {
        $hospital = HospitalSetting::map();
        $hours = json_decode((string) $hospital->get('working_hours'), true);

        $view->with([
            'hospital' => $hospital,
            'workingHours' => is_array($hours) ? $hours : [],
            'emergencyBanner' => Banner::query()->active()->where('placement', 'emergency')->orderBy('sort_order')->first(),
            'tickerAcknowledgements' => Acknowledgement::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(12)
                ->get(['id', 'message']),
            'footerDepartments' => Department::query()->active()->orderBy('sort_order')->orderBy('name')->limit(6)->get(),
            'footerServices' => Service::query()->active()->orderBy('sort_order')->orderBy('title')->limit(6)->get(),
            'navServices' => Service::query()
                ->active()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(10)
                ->get(['id', 'title', 'slug', 'is_urgent']),
            'leadershipNavRoles' => $this->leadershipNavRoles(),
        ]);
    }

    /**
     * @return array<int, array{label: string, href: string, active: bool}>
     */
    private function leadershipNavRoles(): array
    {
        $counts = BoardMember::query()
            ->active()
            ->selectRaw('role, count(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        $roles = [];

        foreach (BoardMember::ROLES as $role => $label) {
            if ((int) $counts->get($role, 0) < 1) {
                continue;
            }

            $roles[] = [
                'label' => $label,
                'href' => route('about.leadership.role', $role),
                'active' => request()->routeIs('about.leadership.role') && request()->route('role') === $role,
            ];
        }

        return $roles;
    }
}
