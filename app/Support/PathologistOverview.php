<?php

namespace App\Support;

use App\Models\LabOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PathologistOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     pendingVerification: Collection<int, LabOrder>,
     *     abnormal: Collection<int, LabOrder>,
     *     urgent: Collection<int, LabOrder>,
     *     approved: Collection<int, LabOrder>,
     *     todayReports: Collection<int, LabOrder>,
     *     imagingRequests: Collection<int, LabOrder>
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || (! $user->hasRole('pathologist') && ! $user->hasRole('radiologist'))) {
            return [
                'enabled' => false,
                'kpis' => [],
                'pendingVerification' => Collection::make(),
                'abnormal' => Collection::make(),
                'urgent' => Collection::make(),
                'approved' => Collection::make(),
                'todayReports' => Collection::make(),
                'imagingRequests' => Collection::make(),
            ];
        }

        $pendingCount = LabOrder::query()->where('status', 'pending_verification')->count();

        $abnormalCount = LabOrder::query()
            ->where('status', 'pending_verification')
            ->where(function (Builder $query): void {
                $query->where('is_critical', true)
                    ->orWhereHas('result', fn (Builder $result) => $result->where('is_abnormal', true)->orWhere('is_critical', true));
            })
            ->count();

        $urgentCount = LabOrder::query()
            ->where('status', 'pending_verification')
            ->where(function (Builder $query): void {
                $query->where('is_critical', true)
                    ->orWhereHas('result', fn (Builder $result) => $result->where('is_critical', true));
            })
            ->count();

        $approvedCount = LabOrder::query()
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        $todayReportsCount = LabOrder::query()
            ->where('status', 'completed')
            ->whereHas('result', fn (Builder $result) => $result->whereDate('published_at', today())->orWhereDate('verified_at', today()))
            ->count();

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => 'Pending Report Verification',
                    'value' => number_format($pendingCount),
                    'meta' => 'Awaiting your approval',
                    'href' => route('admin.labs.index', ['status' => 'pending_verification']),
                    'icon' => 'clipboard-check',
                ],
                [
                    'label' => 'Abnormal Results',
                    'value' => number_format($abnormalCount),
                    'meta' => 'Out of range or flagged',
                    'href' => route('admin.labs.index', ['status' => 'pending_verification', 'flag' => 'abnormal']),
                    'icon' => 'thermometer',
                ],
                [
                    'label' => 'Urgent Reports',
                    'value' => number_format($urgentCount),
                    'meta' => 'Critical flags pending',
                    'href' => route('admin.labs.index', ['status' => 'pending_verification', 'flag' => 'urgent']),
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Approved Reports',
                    'value' => number_format($approvedCount),
                    'meta' => 'Verified today',
                    'href' => route('admin.labs.index', ['status' => 'completed']),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => "Today's Laboratory Reports",
                    'value' => number_format($todayReportsCount),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.labs.index', ['status' => 'completed']),
                    'icon' => 'flask',
                ],
            ],
            'pendingVerification' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'pending_verification')
                ->latest('submitted_for_verification_at')
                ->limit(8)
                ->get(),
            'abnormal' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'pending_verification')
                ->where(function (Builder $query): void {
                    $query->where('is_critical', true)
                        ->orWhereHas('result', fn (Builder $result) => $result->where('is_abnormal', true)->orWhere('is_critical', true));
                })
                ->latest('submitted_for_verification_at')
                ->limit(8)
                ->get(),
            'urgent' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'pending_verification')
                ->where(function (Builder $query): void {
                    $query->where('is_critical', true)
                        ->orWhereHas('result', fn (Builder $result) => $result->where('is_critical', true));
                })
                ->latest('submitted_for_verification_at')
                ->limit(8)
                ->get(),
            'approved' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->latest('completed_at')
                ->limit(8)
                ->get(),
            'todayReports' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'completed')
                ->whereHas('result', function (Builder $result): void {
                    $result->whereDate('published_at', today())
                        ->orWhereDate('verified_at', today());
                })
                ->latest('completed_at')
                ->limit(8)
                ->get(),
            'imagingRequests' => LabOrder::query()
                ->with(['patient', 'test', 'result', 'visit.doctor'])
                ->whereHas('test', fn (Builder $test) => $test->whereIn('category', ['radiology', 'imaging']))
                ->whereNotIn('status', ['cancelled'])
                ->where(function (Builder $query): void {
                    $query->whereIn('status', ['ordered', 'collected', 'processing', 'pending_verification'])
                        ->orWhere(fn (Builder $done) => $done->where('status', 'completed')->whereDate('completed_at', today()));
                })
                ->latest('id')
                ->limit(8)
                ->get(),
        ];
    }
}
