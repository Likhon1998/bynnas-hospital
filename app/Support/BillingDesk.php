<?php

namespace App\Support;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingDesk
{
    /**
     * Ensure a draft/issued consultation bill exists for a finished OPD appointment.
     */
    public function ensureAppointmentConsultationBill(Appointment $appointment, ?User $actor = null): ?Bill
    {
        $appointment->loadMissing(['patient', 'doctor', 'department']);

        if ($appointment->patient_id === null || $appointment->status === 'cancelled') {
            return null;
        }

        $existing = Bill::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', '!=', 'cancelled')
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $fee = round((float) ($appointment->doctor?->consultation_fee ?? 0), 2);

        if ($fee <= 0) {
            $fee = 0;
        }

        $doctorName = $appointment->doctor?->name ?: 'Doctor';
        $department = $appointment->department?->name;
        $description = 'Consultation — '.$doctorName.($department ? ' ('.$department.')' : '');

        return Bill::createFromReception([
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'admission_id' => null,
            'issued_by' => $actor?->id,
            'bill_date' => $appointment->appointment_date?->toDateString() ?? now()->toDateString(),
            'status' => 'draft',
            'discount' => 0,
            'notes' => 'Auto-prepared when the appointment was completed. Cashier can add labs/pharmacy then issue and collect.',
        ], [[
            'item_type' => 'consultation',
            'item_id' => $appointment->doctor_id,
            'description' => $description,
            'quantity' => 1,
            'unit_price' => $fee,
        ]]);
    }

    /**
     * Create or update today's inpatient draft bill: bed charge + any extra day services.
     *
     * @param  list<array{description: string, quantity?: int|string, unit_price: float|int|string, item_type?: string}>  $extraItems
     */
    public function postAdmissionDayCharges(
        Admission $admission,
        string $serviceDate,
        array $extraItems,
        User $actor,
        bool $includeBedCharge = true,
    ): Bill {
        if (! $admission->isOpen()) {
            throw ValidationException::withMessages([
                'service_date' => 'This admission is closed. Daily charges cannot be posted.',
            ]);
        }

        $date = Carbon::parse($serviceDate)->toDateString();
        $admission->loadMissing(['patient', 'ward', 'bed', 'attendingDoctor']);

        return DB::transaction(function () use ($admission, $date, $extraItems, $actor, $includeBedCharge): Bill {
            $bill = Bill::query()
                ->where('admission_id', $admission->id)
                ->whereDate('bill_date', $date)
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->first();

            if ($bill && ! $bill->isEditable()) {
                throw ValidationException::withMessages([
                    'service_date' => 'A bill for this day already exists and is no longer a draft. Open it from billing, or choose another date.',
                ]);
            }

            $items = [];

            if ($includeBedCharge) {
                $rate = $this->dailyBedRate($admission->ward);
                $wardName = $admission->ward?->name ?: 'Ward';
                $bedCode = $admission->bed?->bed_code ?: 'bed';

                $items[] = [
                    'item_type' => 'bed',
                    'item_id' => $admission->bed_id,
                    'description' => 'Bed charge · '.$wardName.' · '.$bedCode.' · '.$date,
                    'quantity' => 1,
                    'unit_price' => $rate,
                ];
            }

            foreach ($extraItems as $item) {
                $description = trim((string) ($item['description'] ?? ''));

                if ($description === '') {
                    continue;
                }

                $items[] = [
                    'item_type' => $item['item_type'] ?? 'custom',
                    'item_id' => null,
                    'description' => $description,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'unit_price' => round((float) ($item['unit_price'] ?? 0), 2),
                ];
            }

            if ($items === []) {
                throw ValidationException::withMessages([
                    'items' => 'Add the bed charge or at least one service for this day.',
                ]);
            }

            $notes = 'Inpatient day charges for admission '.$admission->admission_no.' on '.$date.'.';

            if ($bill) {
                $bill->update([
                    'notes' => $notes,
                    'issued_by' => $bill->issued_by ?: $actor->id,
                ]);
                $bill->syncItems($items);

                return $bill->refresh()->load('billItems');
            }

            return Bill::createFromReception([
                'patient_id' => $admission->patient_id,
                'appointment_id' => $admission->appointment_id,
                'admission_id' => $admission->id,
                'issued_by' => $actor->id,
                'bill_date' => $date,
                'status' => 'draft',
                'discount' => 0,
                'notes' => $notes,
            ], $items);
        });
    }

    public function dailyBedRate(?Ward $ward): float
    {
        if ($ward !== null && $ward->daily_bed_rate !== null && (float) $ward->daily_bed_rate > 0) {
            return round((float) $ward->daily_bed_rate, 2);
        }

        return match ($ward?->type) {
            'icu' => 5000.0,
            'nicu' => 4000.0,
            'isolation' => 3000.0,
            default => 1500.0,
        };
    }
}
