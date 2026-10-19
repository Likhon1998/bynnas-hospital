<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
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
            'doctors' => $featuredDoctors,
            'departments' => Department::query()->active()->orderBy('sort_order')->limit(6)->get(),
            'stats' => [
                'doctors' => Doctor::query()->active()->count(),
                'departments' => Department::query()->active()->count(),
                'patients' => Patient::query()->active()->count(),
                'services' => Service::query()->active()->count(),
            ],
        ]);
    }
}
