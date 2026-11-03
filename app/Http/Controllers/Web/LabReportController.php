<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LookupLabReportRequest;
use App\Http\Requests\Web\RecoverLabReportCodeRequest;
use App\Mail\LabReportAccessCodeMail;
use App\Models\Bill;
use App\Models\HospitalSetting;
use App\Models\LabOrder;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LabReportController extends Controller
{
    public function index(): View
    {
        return view('web.lab-reports.index', [
            'hospital' => HospitalSetting::map(),
        ]);
    }

    public function lookup(LookupLabReportRequest $request): RedirectResponse|View
    {
        $phone = $this->normalizePhone($request->validated('phone'));
        $accessCode = $request->validated('access_code');

        $bill = Bill::query()
            ->with('patient')
            ->where('report_access_code', $accessCode)
            ->where('status', '!=', 'cancelled')
            ->where('paid_amount', '>', 0)
            ->first();

        if ($bill === null || ! $this->phonesMatch($bill->patient?->phone, $phone)) {
            return back()
                ->withInput()
                ->withErrors([
                    'access_code' => 'No matching payment found. Check the phone number and access code from your money receipt.',
                ]);
        }

        $reports = $bill->publishedLabOrders();

        $request->session()->put('lab_report_unlocked_bill_ids', array_values(array_unique([
            ...$request->session()->get('lab_report_unlocked_bill_ids', []),
            $bill->id,
        ])));

        return view('web.lab-reports.results', [
            'bill' => $bill,
            'reports' => $reports,
            'hospital' => HospitalSetting::map(),
        ]);
    }

    public function download(LabOrder $labOrder): View
    {
        $labOrder->load(['patient', 'test', 'visit.doctor', 'orderedBy', 'result.verifiedBy', 'result.enteredBy']);

        abort_unless($labOrder->result?->isPublished() ?? false, 404);

        $billIds = session('lab_report_unlocked_bill_ids', []);
        $allowed = Bill::query()
            ->whereIn('id', $billIds)
            ->whereHas('billItems', fn ($items) => $items->where('item_type', 'lab')->where('item_id', $labOrder->id))
            ->exists();

        abort_unless($allowed, 403);

        return view('admin.labs.print-report', [
            'order' => $labOrder,
            'publicDownload' => true,
        ]);
    }

    public function recoverForm(): View
    {
        return view('web.lab-reports.recover', [
            'hospital' => HospitalSetting::map(),
        ]);
    }

    public function recover(RecoverLabReportCodeRequest $request): RedirectResponse|View
    {
        $phone = $this->normalizePhone($request->validated('phone'));
        $patientCode = $request->validated('patient_code');

        $patient = Patient::query()
            ->where('patient_code', $patientCode)
            ->first();

        if ($patient === null || ! $this->phonesMatch($patient->phone, $phone)) {
            return back()
                ->withInput()
                ->withErrors([
                    'patient_code' => 'We could not match that phone number and patient ID. Check your patient slip or call the hospital desk.',
                ]);
        }

        $bills = Bill::query()
            ->where('patient_id', $patient->id)
            ->whereNotNull('report_access_code')
            ->where('paid_amount', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->whereHas('billItems', fn ($items) => $items->where('item_type', 'lab'))
            ->latest('id')
            ->limit(10)
            ->get(['id', 'bill_no', 'report_access_code', 'bill_date']);

        if ($bills->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'patient_code' => 'No lab payment access codes were found for this patient yet.',
                ]);
        }

        $codes = $bills->map(fn (Bill $bill): object => (object) [
            'bill_no' => $bill->bill_no,
            'report_access_code' => $bill->report_access_code,
            'bill_date' => $bill->bill_date?->format('d M Y') ?: '—',
        ]);

        if (filled($patient->email)) {
            Mail::to($patient->email)->send(new LabReportAccessCodeMail($patient, $codes));
        }

        return view('web.lab-reports.recovered', [
            'patient' => $patient,
            'codes' => $codes,
            'emailed' => filled($patient->email),
            'hospital' => HospitalSetting::map(),
        ]);
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }

    private function phonesMatch(?string $stored, string $submitted): bool
    {
        $left = $this->normalizePhone($stored);
        $right = $this->normalizePhone($submitted);

        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        return str_ends_with($left, $right) || str_ends_with($right, $left);
    }
}
