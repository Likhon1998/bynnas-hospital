<?php

namespace App\View\Composers;

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
            'footerDepartments' => Department::query()->active()->orderBy('sort_order')->limit(6)->get(),
            'footerServices' => Service::query()->active()->orderBy('sort_order')->limit(6)->get(),
        ]);
    }
}
