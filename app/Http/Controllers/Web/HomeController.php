<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorTalk;
use App\Models\Gallery;
use App\Models\Notice;
use App\Models\Service;
use App\Support\PublicCampusStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, PublicCampusStats $campusStats): View
    {
        $directoryDoctors = Doctor::query()
            ->active()
            ->with('department')
            ->orderBy('name')
            ->get();

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
            'departments' => Department::query()
                ->active()
                ->withCount(['doctors as consultants_count' => fn (Builder $query) => $query->active()])
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'directoryDoctors' => $directoryDoctors,
            'notices' => Notice::query()
                ->published()
                ->orderByDesc('is_urgent')
                ->orderByDesc('published_on')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(5)
                ->get(),
            'services' => $featuredServices,
            'featuredService' => $featuredServices->first(),
            'bookableServices' => $bookableServices,
            'posts' => BlogPost::query()
                ->published()
                ->with('blogCategory')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'galleryItems' => Gallery::query()->active()->orderBy('sort_order')->orderBy('id')->limit(8)->get(),
            'doctorTalks' => DoctorTalk::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(8)
                ->get(),
            'stats' => $campusStats->snapshot(),
        ]);
    }
}
