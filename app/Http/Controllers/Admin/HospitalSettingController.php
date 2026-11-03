<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HospitalSettingRequest;
use App\Models\HospitalSetting;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HospitalSettingController extends Controller
{
    public function edit(): View
    {
        HospitalSetting::ensureDefaults();

        $settings = HospitalSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.settings.edit', [
            'settings' => $settings,
            'logo' => HospitalSetting::query()->where('key', 'logo')->value('value'),
            'hospitalName' => HospitalSetting::query()->where('key', 'hospital_name')->value('value'),
        ]);
    }

    public function update(HospitalSettingRequest $request, SystemAuditor $auditor): RedirectResponse
    {
        HospitalSetting::ensureDefaults();

        $allowedKeys = HospitalSetting::query()->pluck('key');
        $changed = [];
        $values = $request->validated('settings');

        if ($request->hasFile('logo_upload')) {
            $path = $request->file('logo_upload')->store('branding', 'public');
            $currentLogo = HospitalSetting::query()->where('key', 'logo')->value('value');
            $values['logo'] = '/storage/'.$path;
            $this->deleteStoredAsset($currentLogo);
        }

        foreach ($values as $key => $value) {
            if (! $allowedKeys->contains($key)) {
                continue;
            }

            HospitalSetting::query()->where('key', $key)->update([
                'value' => $value,
            ]);
            $changed[] = $key;
        }

        $auditor->record('settings.update', 'Updated hospital settings.', null, [
            'keys' => $changed,
        ], $request->user());

        return redirect()->route('admin.settings.edit')->with('status', 'Hospital branding and settings saved. The public website will use them immediately.');
    }

    private function deleteStoredAsset(?string $url): void
    {
        if (! filled($url)) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (! is_string($path) || ! str_contains($path, '/storage/branding/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
