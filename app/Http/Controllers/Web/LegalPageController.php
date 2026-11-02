<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HospitalSetting;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return $this->page('Privacy Policy', 'privacy_content');
    }

    public function terms(): View
    {
        return $this->page('Terms of Use', 'terms_content');
    }

    private function page(string $title, string $key): View
    {
        $hospital = HospitalSetting::map();
        $body = trim((string) $hospital->get($key));
        $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';

        abort_unless(filled($body), 404);

        return view('web.legal', [
            'title' => $title,
            'note' => $title.' for '.$hospitalName.'.',
            'body' => $body,
        ]);
    }
}
