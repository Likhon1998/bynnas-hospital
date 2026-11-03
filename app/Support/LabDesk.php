<?php

namespace App\Support;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\LabOrder;
use App\Models\LabOrderEvent;
use App\Models\LabResult;
use App\Models\LabTest;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LabDesk
{
    /**
     * @param  array{visit_id: int, lab_test_id: int, clinical_notes?: string|null}  $attributes
     */
    public function order(array $attributes, User $actor): LabOrder
    {
        $visit = Visit::query()->with('patient')->find($attributes['visit_id']);
        $test = LabTest::query()->find($attributes['lab_test_id']);

        if ($visit === null || $visit->status === 'cancelled') {
            throw ValidationException::withMessages([
                'visit_id' => 'Choose an active clinical encounter.',
            ]);
        }

        if ($test === null || ! $test->is_active) {
            throw ValidationException::withMessages([
                'lab_test_id' => 'Choose an active laboratory test.',
            ]);
        }

        return DB::transaction(function () use ($attributes, $actor, $visit, $test): LabOrder {
            $order = LabOrder::query()->create([
                'order_no' => LabOrder::nextNumber(),
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'appointment_id' => $visit->appointment_id,
                'lab_test_id' => $test->id,
                'ordered_by' => $actor->id,
                'clinical_notes' => $attributes['clinical_notes'] ?? null,
                'status' => 'ordered',
            ]);

            $this->chargeOnce($order, $test, $actor);

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_ORDERED,
                null,
                'ordered',
                'Ordered '.$test->name.'.',
                ['lab_test_id' => $test->id, 'price' => (string) $test->price],
            );

            $this->recordVisitEvent(
                $visit,
                $actor,
                VisitEvent::TYPE_LAB_ORDERED,
                $visit->status,
                $visit->status,
                'Lab ordered: '.$test->name.' ('.$order->order_no.').',
                ['lab_order_id' => $order->id],
            );

            return $order->refresh();
        });
    }

    /**
     * @param  array{sample_id?: string|null, barcode?: string|null, collected_at?: string|null}  $attributes
     */
    public function collect(LabOrder $order, array $attributes, User $actor): LabOrder
    {
        if (! $order->canCollect()) {
            throw ValidationException::withMessages([
                'status' => 'Only an ordered test can be collected.',
            ]);
        }

        return DB::transaction(function () use ($order, $attributes, $actor): LabOrder {
            $from = $order->status;
            $sampleId = filled($attributes['sample_id'] ?? null)
                ? (string) $attributes['sample_id']
                : LabOrder::nextSampleId();
            $barcode = filled($attributes['barcode'] ?? null)
                ? (string) $attributes['barcode']
                : $sampleId;
            $collectedAt = filled($attributes['collected_at'] ?? null)
                ? $attributes['collected_at']
                : now();

            $order->update([
                'status' => 'collected',
                'sample_id' => $sampleId,
                'barcode' => $barcode,
                'collected_by' => $actor->id,
                'collected_at' => $collectedAt,
            ]);

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_COLLECTED,
                $from,
                'collected',
                'Sample collected · '.$sampleId.'.',
                ['sample_id' => $sampleId, 'barcode' => $barcode],
            );

            return $order->refresh();
        });
    }

    public function startProcessing(LabOrder $order, User $actor): LabOrder
    {
        if (! $order->canProcess()) {
            throw ValidationException::withMessages([
                'status' => 'Collect the sample before processing.',
            ]);
        }

        return DB::transaction(function () use ($order, $actor): LabOrder {
            $from = $order->status;

            $order->update([
                'status' => 'processing',
                'processing_by' => $actor->id,
                'processing_at' => now(),
            ]);

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_PROCESSING,
                $from,
                'processing',
                'Sample is in process.',
            );

            return $order->refresh();
        });
    }

    /**
     * @param  array{value: string, unit?: string|null, reference_range?: string|null, notes?: string|null, findings?: string|null, is_critical?: bool, is_abnormal?: bool}  $attributes
     */
    public function recordPreliminary(LabOrder $order, array $attributes, User $actor): LabResult
    {
        if (! $order->canRecordResult()) {
            throw ValidationException::withMessages([
                'status' => 'Collect the sample before recording a result.',
            ]);
        }

        if ($order->result) {
            if ($order->result->isVerified()) {
                throw ValidationException::withMessages([
                    'value' => 'You cannot modify a verified final report.',
                ]);
            }

            throw ValidationException::withMessages([
                'value' => 'A preliminary result is already awaiting pathologist verification.',
            ]);
        }

        $order->loadMissing('test', 'visit');
        $test = $order->test;
        $numeric = $this->numericValue($attributes['value']);
        $isCritical = (bool) ($attributes['is_critical'] ?? false)
            || ($test?->isCriticalValue($numeric) ?? false);
        $isAbnormal = (bool) ($attributes['is_abnormal'] ?? false) || $isCritical;

        return DB::transaction(function () use ($order, $attributes, $actor, $test, $numeric, $isCritical, $isAbnormal): LabResult {
            if ($order->status === 'collected') {
                $order->update([
                    'status' => 'processing',
                    'processing_by' => $actor->id,
                    'processing_at' => $order->processing_at ?? now(),
                ]);
            }

            $result = LabResult::query()->create([
                'lab_order_id' => $order->id,
                'patient_id' => $order->patient_id,
                'visit_id' => $order->visit_id,
                'value' => $attributes['value'],
                'numeric_value' => $numeric,
                'unit' => $attributes['unit'] ?? $test?->unit,
                'reference_range' => $attributes['reference_range'] ?? $test?->reference_range,
                'is_critical' => $isCritical,
                'is_abnormal' => $isAbnormal,
                'notes' => $attributes['notes'] ?? null,
                'findings' => $attributes['findings'] ?? null,
                'entered_by' => $actor->id,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            $from = $order->status;

            $order->update([
                'status' => 'pending_verification',
                'is_critical' => $isCritical,
                'submitted_for_verification_at' => now(),
                'submitted_for_verification_by' => $actor->id,
            ]);

            $summary = ($test?->name ?: 'Test').' '.$result->displayValue()
                .($isCritical ? ' (critical)' : '');

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_RESULT_SUBMITTED,
                $from,
                'pending_verification',
                'Preliminary result submitted: '.$summary.'.',
                [
                    'lab_result_id' => $result->id,
                    'is_critical' => $isCritical,
                ],
            );

            return $result->refresh();
        });
    }

    /**
     * @param  array{value?: string|null, unit?: string|null, reference_range?: string|null, notes?: string|null, findings?: string|null, is_critical?: bool, is_abnormal?: bool, signature_name?: string|null, signature_title?: string|null}  $attributes
     */
    public function verifyResult(LabOrder $order, array $attributes, User $actor): LabResult
    {
        $order->loadMissing(['result', 'test', 'visit']);

        if (! $order->canVerify()) {
            throw ValidationException::withMessages([
                'status' => 'Only a preliminary result awaiting verification can be approved.',
            ]);
        }

        $result = $order->result;

        if ($result === null || $result->isVerified()) {
            throw ValidationException::withMessages([
                'value' => 'This order already has a verified result.',
            ]);
        }

        return DB::transaction(function () use ($order, $attributes, $actor, $result): LabResult {
            $value = $attributes['value'] ?? $result->value;
            $numeric = $this->numericValue((string) $value);
            $test = $order->test;
            $isCritical = (bool) ($attributes['is_critical'] ?? $result->is_critical)
                || ($test?->isCriticalValue($numeric) ?? false);
            $isAbnormal = (bool) ($attributes['is_abnormal'] ?? $result->is_abnormal) || $isCritical;
            $signatureName = filled($attributes['signature_name'] ?? null)
                ? (string) $attributes['signature_name']
                : $actor->name;
            $signatureTitle = filled($attributes['signature_title'] ?? null)
                ? (string) $attributes['signature_title']
                : ($actor->hasRole('pathologist') ? 'Pathologist' : 'Authorized verifier');

            $result->update([
                'value' => $value,
                'numeric_value' => $numeric,
                'unit' => $attributes['unit'] ?? $result->unit,
                'reference_range' => $attributes['reference_range'] ?? $result->reference_range,
                'notes' => array_key_exists('notes', $attributes) ? $attributes['notes'] : $result->notes,
                'findings' => array_key_exists('findings', $attributes) ? $attributes['findings'] : $result->findings,
                'is_critical' => $isCritical,
                'is_abnormal' => $isAbnormal,
                'verified_by' => $actor->id,
                'verified_at' => now(),
                'signature_name' => $signatureName,
                'signature_title' => $signatureTitle,
                'published_at' => now(),
            ]);

            $from = $order->status;
            $summary = ($test?->name ?: 'Test').' '.$result->fresh()->displayValue()
                .($isCritical ? ' (critical)' : '')
                .($isAbnormal ? ' (abnormal)' : '');

            $order->update([
                'status' => 'completed',
                'is_critical' => $isCritical,
                'completed_at' => now(),
            ]);

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_COMPLETED,
                $from,
                'completed',
                'Final report approved and published: '.$summary.'.',
                [
                    'lab_result_id' => $result->id,
                    'is_critical' => $isCritical,
                    'is_abnormal' => $isAbnormal,
                    'signature_name' => $signatureName,
                ],
            );

            if ($order->visit) {
                $this->recordVisitEvent(
                    $order->visit,
                    $actor,
                    VisitEvent::TYPE_LAB_RESULTED,
                    $order->visit->status,
                    $order->visit->status,
                    'Lab result published: '.$summary.' · '.$order->order_no.'.',
                    [
                        'lab_order_id' => $order->id,
                        'lab_result_id' => $result->id,
                        'is_critical' => $isCritical,
                    ],
                );
            }

            return $result->refresh();
        });
    }

    public function returnForCorrection(LabOrder $order, string $reason, User $actor): LabOrder
    {
        $order->loadMissing(['result', 'test']);

        if (! $order->canReturnForCorrection()) {
            throw ValidationException::withMessages([
                'status' => 'Only a preliminary result awaiting verification can be returned.',
            ]);
        }

        if ($order->result?->isVerified()) {
            throw ValidationException::withMessages([
                'status' => 'A verified final report cannot be returned for correction.',
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $actor): LabOrder {
            $from = $order->status;
            $result = $order->result;

            if ($result !== null) {
                $result->delete();
            }

            $order->update([
                'status' => 'processing',
                'submitted_for_verification_at' => null,
                'submitted_for_verification_by' => null,
                'processing_by' => $order->processing_by ?? $actor->id,
                'processing_at' => $order->processing_at ?? now(),
            ]);

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_RETURNED,
                $from,
                'processing',
                $reason,
            );

            return $order->refresh();
        });
    }

    public function assertCanModifyResult(LabOrder $order, User $actor): void
    {
        $order->loadMissing('result');

        if ($order->result?->isVerified() && (int) $order->result->verified_by !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'value' => 'You cannot modify another verifier\'s final report.',
            ]);
        }
    }

    public function cancel(LabOrder $order, string $reason, User $actor): LabOrder
    {
        if (! $order->canCancel()) {
            throw ValidationException::withMessages([
                'status' => 'A completed result cannot be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $actor): LabOrder {
            $from = $order->status;

            $order->update([
                'status' => 'cancelled',
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            if ($from === 'ordered') {
                $this->removeDraftCharge($order);
            }

            $this->recordEvent(
                $order,
                $actor,
                LabOrderEvent::TYPE_CANCELLED,
                $from,
                'cancelled',
                $reason,
            );

            return $order->refresh();
        });
    }

    private function chargeOnce(LabOrder $order, LabTest $test, User $actor): void
    {
        $existing = BillItem::query()
            ->where('item_type', 'lab')
            ->where('item_id', $order->id)
            ->whereHas('bill', fn ($query) => $query->where('status', '!=', 'cancelled'))
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return;
        }

        $bill = Bill::query()
            ->where('patient_id', $order->patient_id)
            ->where('status', 'draft')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($bill === null) {
            $bill = Bill::query()->create([
                'bill_no' => Bill::nextBillNumber(),
                'patient_id' => $order->patient_id,
                'appointment_id' => $order->appointment_id,
                'issued_by' => $actor->id,
                'bill_date' => now()->toDateString(),
                'status' => 'draft',
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'notes' => 'Laboratory charges',
            ]);
        }

        $bill->appendUniqueLine(
            itemType: 'lab',
            itemId: $order->id,
            description: 'Lab · '.$test->name.' · '.$order->order_no,
            quantity: 1,
            unitPrice: (float) $test->price,
        );
    }

    private function removeDraftCharge(LabOrder $order): void
    {
        $item = BillItem::query()
            ->where('item_type', 'lab')
            ->where('item_id', $order->id)
            ->whereHas('bill', fn ($query) => $query->where('status', 'draft'))
            ->first();

        if ($item === null) {
            return;
        }

        $bill = $item->bill;
        $item->delete();

        if ($bill) {
            $bill->recalculateTotals();

            if (! $bill->billItems()->exists()) {
                $bill->delete();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordEvent(
        LabOrder $order,
        ?User $actor,
        string $type,
        ?string $from,
        ?string $to,
        string $notes,
        array $meta = [],
    ): void {
        LabOrderEvent::query()->create([
            'lab_order_id' => $order->id,
            'patient_id' => $order->patient_id,
            'visit_id' => $order->visit_id,
            'actor_user_id' => $actor?->id,
            'event_type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordVisitEvent(
        Visit $visit,
        User $actor,
        string $type,
        ?string $from,
        ?string $to,
        string $notes,
        array $meta = [],
    ): void {
        VisitEvent::query()->create([
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'actor_user_id' => $actor->id,
            'event_type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    private function numericValue(string $value): ?float
    {
        $normalized = str_replace(',', '', trim($value));

        if (! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
