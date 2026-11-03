<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\HealthPackage;
use App\Models\Service;
use App\Models\Testimonial;
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

        $showcaseDoctors = Doctor::query()
            ->active()
            ->with('department')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $searchedName = trim((string) $request->query('q', ''));
        $specialty = trim((string) $request->query('specialty', ''));
        $isFinderSearch = $searchedName !== '' || $specialty !== '';

        $finderQuery = Doctor::query()->active()->with('department');

        if ($searchedName !== '') {
            $finderQuery->where('name', 'like', '%'.$searchedName.'%');
        }

        if ($specialty !== '') {
            $finderQuery->where(function (Builder $query) use ($specialty): void {
                $query->where('specialization', 'like', '%'.$specialty.'%')
                    ->orWhereHas('department', function (Builder $departmentQuery) use ($specialty): void {
                        $departmentQuery->where('name', $specialty);
                    });
            });
        }

        $finderDoctors = $isFinderSearch
            ? $finderQuery->orderBy('sort_order')->orderBy('name')->get()
            : $showcaseDoctors->take(4)->values();

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
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'doctors' => $showcaseDoctors,
            'finderDoctors' => $finderDoctors,
            'directoryDoctors' => $directoryDoctors,
            'finderDirectory' => $directoryDoctors->map(function (Doctor $doctor) use ($showcaseDoctors): array {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialty' => $doctor->department?->name ?: '',
                    'specialization' => $doctor->specialization ?: $doctor->department?->name ?: 'Consultant',
                    'photo' => $doctor->photo,
                    'initials' => $doctor->initials(),
                    'preview' => $showcaseDoctors->take(4)->contains('id', $doctor->id),
                    'url' => filled($doctor->slug) ? route('doctors.show', $doctor) : route('home').'#appointment',
                ];
            })->values(),
            'isFinderSearch' => $isFinderSearch,
            'finderSearchLabel' => $specialty !== '' ? $specialty : ($searchedName !== '' ? $searchedName : null),
            'services' => $featuredServices,
            'featuredService' => $featuredServices->first(),
            'bookableServices' => $bookableServices,
            'packages' => HealthPackage::query()
                ->active()
                ->with(['healthPackageItems' => fn ($query) => $query->orderBy('sort_order')])
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(3)
                ->get(),
            'testimonials' => Testimonial::query()->active()->orderBy('sort_order')->limit(6)->get(),
            'posts' => BlogPost::query()
                ->published()
                ->with('blogCategory')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'faqs' => Faq::query()->active()->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'galleryItems' => Gallery::query()->active()->orderBy('sort_order')->orderBy('id')->limit(3)->get(),
            'stats' => $campusStats->snapshot(),
        ]);
    }
}
