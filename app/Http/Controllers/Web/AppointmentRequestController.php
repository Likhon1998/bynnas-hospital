<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAppointmentRequest;
use App\Models\Doctor;
use App\Models\Service;
use App\Support\AppointmentDesk;
use Illuminate\Http\RedirectResponse;

class AppointmentRequestController extends Controller
{
    public function store(StoreAppointmentRequest $request, AppointmentDesk $desk): RedirectResponse
    {
        $service = Service::query()
            ->active()
            ->where('slug', $request->validated('service'))
            ->first();

        if (! $service) {
            return back()
                ->withInput()
                ->withErrors(['service' => 'That service is not available right now.']);
        }

        if (! Doctor::query()->active()->whereNotNull('department_id')->exists()) {
            return back()
                ->withInput()
                ->with('error', 'The desk cannot take website requests until a doctor is on the roster.');
        }

        $appointment = $desk->requestFromWebsite($request->validated(), $service);

        return redirect()
            ->to(route('home').'#appointment')
            ->with('status', 'Request received. The desk will call '.$appointment->patient?->phone.' to confirm.');
    }
}
