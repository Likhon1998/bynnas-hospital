<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HealthPackage;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        $packages = HealthPackage::query()
            ->active()
            ->with(['healthPackageItems' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('web.packages', compact('packages'));
    }
}
