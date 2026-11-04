<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentStatusRequest;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\HospitalSetting;
use App\Models\Patient;
use App\Support\AppointmentDesk;
use App\Support\AppointmentWhatsAppNotice;
use App\Support\BillingDesk;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request, AppointmentWhatsAppNotice $whatsapp): View
    {
        $status = $request->string('status')->toString();
        $source = $request->string('source')->toString();

        if (! array_key_exists($status, Appointment::STATUSES)) {
            $status = '';
        }

        if (! array_key_exists($source, Appointment::SOURCES)) {
            $source = '';
        }

        $linkedDoctor = $request->user()?->hasRole('doctor') ? $request->user()->doctor : null;
        $doctorId = $linkedDoctor?->id ?? $request->integer('doctor');
        $selectedDoctor = $doctorId > 0
            ? Doctor::query()->select(['id', 'name'])->find($doctorId)
            : null;
        $selectedDate = $this->selectedVisitDate($request);

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department', 'visits'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
            ->when($selectedDoctor !== null, fn ($query) => $query->where('doctor_id', $selectedDoctor->id))
            ->when($selectedDate !== null, fn ($query) => $query->whereDate('appointment_date', $selectedDate->toDateString()))
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('appointment_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'))
                        ->orWhereHas('doctor', fn ($doctor) => $doctor->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $whatsappUrls = $appointments->getCollection()->mapWithKeys(
            fn (Appointment $appointment) => [$appointment->id => $whatsapp->url($appointment)],
        );

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'whatsappUrls' => $whatsappUrls,
            'doctors' => $linkedDoctor !== null
                ? collect([$linkedDoctor])->map(fn (Doctor $doctor) => (object) ['id' => $doctor->id, 'name' => $doctor->name])
                : Doctor::query()->orderBy('name')->orderBy('id')->get(['id', 'name']),
            'selectedDoctor' => $selectedDoctor,
            'selectedDate' => $selectedDate,
            'doctorLocked' => $linkedDoctor !== null,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('appointments.manage') ?? false, 403);

        $linkedDoctor = $request->user()?->hasRole('doctor') ? $request->user()->doctor : null;
        $doctors = $linkedDoctor !== null
            ? Doctor::query()->whereKey($linkedDoctor->id)->with('department')->get()
            : Doctor::query()->active()->whereNotNull('department_id')->with('department')->orderBy('name')->orderBy('id')->get();

        return view('admin.appointments.create', [
            'patients' => Patient::query()->active()->orderBy('full_name')->orderBy('id')->get(),
            'doctors' => $doctors,
            'selectedPatientId' => $request->integer('patient_id') ?: null,
            'selectedDoctorId' => $linkedDoctor?->id,
            'doctorLocked' => $linkedDoctor !== null,
        ]);
    }

    public function store(
        StoreAppointmentRequest $request,
        AppointmentDesk $desk,
        AppointmentWhatsAppNotice $whatsapp,
        BillingDesk $billing,
    ): RedirectResponse {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $payload = $request->validated();

        if ($actor->hasRole('doctor') && $actor->doctor?->id) {
            $payload['doctor_id'] = $actor->doctor->id;
        }

        $appointment = $desk->book($payload, $actor);
        $appointment->load(['patient', 'doctor', 'department']);

        if (in_array($appointment->status, ['confirmed', 'completed'], true)) {
            $billing->ensureAppointmentConsultationBill($appointment, $actor);
        }

        return $this->redirectAfterSerialNotice(
            $appointment,
            $whatsapp,
            serialJustIssued: $appointment->serial_no !== null,
            fallbackStatus: 'Visit booked.',
        );
    }

    public function show(Appointment $appointment, AppointmentWhatsAppNotice $whatsapp): View
    {
        $appointment->load(['patient', 'doctor.department', 'department', 'createdBy', 'visits']);

        $linkedBill = Bill::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', '!=', 'cancelled')
            ->latest('id')
            ->first();

        return view('admin.appointments.show', [
            'appointment' => $appointment,
            'whatsappSerialUrl' => $whatsapp->url($appointment),
            'whatsappSerialMessage' => $whatsapp->message($appointment),
            'linkedBill' => $linkedBill,
        ]);
    }

    public function update(
        AppointmentStatusRequest $request,
        Appointment $appointment,
        AppointmentDesk $desk,
        AppointmentWhatsAppNotice $whatsapp,
        BillingDesk $billing,
    ): RedirectResponse {
        $hadSerial = $appointment->serial_no !== null;

        $desk->updateVisit($appointment, $request->validated());
        $appointment->refresh()->load(['patient', 'doctor', 'department']);

        if (in_array($appointment->status, ['confirmed', 'completed'], true)) {
            $billing->ensureAppointmentConsultationBill($appointment, $request->user());
        }

        $serialJustIssued = ! $hadSerial && $appointment->serial_no !== null;

        if ($serialJustIssued) {
            return $this->redirectAfterSerialNotice(
                $appointment,
                $whatsapp,
                serialJustIssued: true,
                fallbackStatus: 'Appointment confirmed.',
            );
        }

        return back()->with('status', 'Appointment updated.');
    }

    public function printTicket(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor.department', 'department']);

        return view('admin.appointments.print-ticket', [
            'appointment' => $appointment,
            'hospital' => HospitalSetting::map(),
        ]);
    }

    private function redirectAfterSerialNotice(
        Appointment $appointment,
        AppointmentWhatsAppNotice $whatsapp,
        bool $serialJustIssued,
        string $fallbackStatus,
    ): RedirectResponse {
        $url = $whatsapp->url($appointment);
        $serial = $appointment->serialLabel();

        if ($serialJustIssued && $url !== null) {
            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with([
                    'status' => 'Serial '.$serial.' is ready. WhatsApp opens with the patient message — tap Send.',
                    'whatsapp_serial_url' => $url,
                    'open_whatsapp' => true,
                ]);
        }

        if ($serialJustIssued) {
            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with('status', 'Serial '.$serial.' issued. Add the patient phone number to send it on WhatsApp.');
        }

        return redirect()
            ->route('admin.appointments.show', $appointment)
            ->with('status', $fallbackStatus);
    }

    private function selectedVisitDate(Request $request): ?Carbon
    {
        $raw = $request->string('date')->toString();

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $raw);
        } catch (InvalidFormatException) {
            return null;
        }

        if ($date->format('Y-m-d') !== $raw) {
            return null;
        }

        return $date->startOfDay();
    }
}
