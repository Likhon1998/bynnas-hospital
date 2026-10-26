<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HospitalSetting;
use Illuminate\View\View;

class HelpSupportController extends Controller
{
    public function __invoke(): View
    {
        $settings = HospitalSetting::query()->pluck('value', 'key');

        return view('admin.operations.help', [
            'hospitalName' => $settings->get('hospital_name') ?: config('app.name'),
            'phone' => $settings->get('phone'),
            'emergency' => $settings->get('emergency_phone'),
            'email' => $settings->get('email'),
        ]);
    }
}
