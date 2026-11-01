<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\View\View;

class EmergencyController extends Controller
{
    public function __invoke(): View
    {
        return view('web.emergency', [
            'service' => Service::query()->where('slug', 'emergency-care')->first(),
        ]);
    }
}
