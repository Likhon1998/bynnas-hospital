<?php

namespace App\Support;

use App\Models\LabOrder;
use App\Models\User;
use Illuminate\Support\Collection;

class LabOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     todayOrders: Collection<int, LabOrder>,
     *     pendingSamples: Collection<int, LabOrder>,
     *     collected: Collection<int, LabOrder>,
     *     inProgress: Collection<int, LabOrder>,
     *     pendingResults: Collection<int, LabOrder>,
     *     completed: Collection<int, LabOrder>
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('lab_technician')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'todayOrders' => Collection::make(),
                'pendingSamples' => Collection::make(),
                'collected' => Collection::make(),
                'inProgress' => Collection::make(),
                'pendingResults' => Collection::make(),
                'completed' => Collection::make(),
            ];
        }

        $todayOrdersCount = LabOrder::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        $pendingSamplesCount = LabOrder::query()->where('status', 'ordered')->count();
        $collectedCount = LabOrder::query()->where('status', 'collected')->count();
        $inProgressCount = LabOrder::query()->where('status', 'processing')->count();
        $pendingResultsCount = LabOrder::query()->where('status', 'pending_verification')->count();
        $completedCount = LabOrder::query()
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => "Today's Lab Orders",
                    'value' => number_format($todayOrdersCount),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.labs.index'),
                    'icon' => 'flask',
                ],
                [
                    'label' => 'Pending Samples',
                    'value' => number_format($pendingSamplesCount),
                    'meta' => 'Awaiting collection',
                    'href' => route('admin.labs.index', ['status' => 'ordered']),
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Collected Samples',
                    'value' => number_format($collectedCount),
                    'meta' => 'Ready to process',
                    'href' => route('admin.labs.index', ['status' => 'collected']),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => 'Tests in Progress',
                    'value' => number_format($inProgressCount),
                    'meta' => 'Running in the lab',
                    'href' => route('admin.labs.index', ['status' => 'processing']),
                    'icon' => 'thermometer',
                ],
                [
                    'label' => 'Pending Results',
                    'value' => number_format($pendingResultsCount),
                    'meta' => 'Awaiting pathologist verification',
                    'href' => route('admin.labs.index', ['status' => 'pending_verification']),
                    'icon' => 'clipboard-check',
                ],
                [
                    'label' => 'Completed Tests',
                    'value' => number_format($completedCount),
                    'meta' => 'Verified today',
                    'href' => route('admin.labs.index', ['status' => 'completed']),
                    'icon' => 'users',
                ],
            ],
            'todayOrders' => LabOrder::query()
                ->with(['patient', 'test'])
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->latest('id')
                ->limit(8)
                ->get(),
            'pendingSamples' => LabOrder::query()
                ->with(['patient', 'test'])
                ->where('status', 'ordered')
                ->oldest('id')
                ->limit(8)
                ->get(),
            'collected' => LabOrder::query()
                ->with(['patient', 'test'])
                ->where('status', 'collected')
                ->latest('collected_at')
                ->limit(8)
                ->get(),
            'inProgress' => LabOrder::query()
                ->with(['patient', 'test'])
                ->where('status', 'processing')
                ->latest('processing_at')
                ->limit(8)
                ->get(),
            'pendingResults' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'pending_verification')
                ->latest('submitted_for_verification_at')
                ->limit(8)
                ->get(),
            'completed' => LabOrder::query()
                ->with(['patient', 'test', 'result'])
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->latest('completed_at')
                ->limit(8)
                ->get(),
        ];
    }
}
