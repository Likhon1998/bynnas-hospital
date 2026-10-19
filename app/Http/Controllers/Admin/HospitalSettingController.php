<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HospitalSettingRequest;
use App\Models\HospitalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HospitalSettingController extends Controller
{
    public function edit(): View
    {
        $settings = HospitalSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(HospitalSettingRequest $request): RedirectResponse
    {
        $allowedKeys = HospitalSetting::query()->pluck('key');

        foreach ($request->validated('settings') as $key => $value) {
            if (! $allowedKeys->contains($key)) {
                continue;
            }

            HospitalSetting::query()->where('key', $key)->update([
                'value' => $value,
            ]);
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Hospital settings saved.');
    }
}
