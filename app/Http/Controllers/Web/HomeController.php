<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HealthPackage;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredDoctors = Doctor::query()
            ->active()
            ->featured()
            ->with('department')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        if ($featuredDoctors->isEmpty()) {
            $featuredDoctors = Doctor::query()
                ->active()
                ->with('department')
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        }

        $featuredServices = Service::query()
            ->active()
            ->with('department')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(7)
            ->get();

        $bookableServices = Service::query()
            ->active()
            ->orderBy('title')
            ->get();

        return view('web.home', [
            'heroBanner' => Banner::query()->active()->where('placement', 'hero')->orderBy('sort_order')->first(),
            'emergencyBanner' => Banner::query()->active()->where('placement', 'emergency')->orderBy('sort_order')->first(),
            'departments' => Department::query()->active()->orderBy('sort_order')->limit(6)->get(),
            'doctors' => $featuredDoctors,
            'services' => $featuredServices,
            'bookableServices' => $bookableServices,
            'packages' => HealthPackage::query()
                ->active()
                ->with(['healthPackageItems' => fn ($query) => $query->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
            'testimonials' => Testimonial::query()->active()->orderBy('sort_order')->limit(6)->get(),
            'posts' => BlogPost::query()
                ->published()
                ->with('blogCategory')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'stats' => [
                'doctors' => Doctor::query()->active()->count(),
                'departments' => Department::query()->active()->count(),
                'patients' => Patient::query()->active()->count(),
                'services' => Service::query()->active()->count(),
            ],
        ]);
    }
}
