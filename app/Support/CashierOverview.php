<?php

namespace App\Support;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\FinanceApproval;
use App\Models\User;
use Illuminate\Support\Collection;

class CashierOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     todayBills: Collection<int, Bill>,
     *     todayCollections: Collection<int, BillPayment>,
     *     pendingPayments: Collection<int, Bill>,
     *     outstandingDues: Collection<int, Bill>,
     *     refundRequests: Collection<int, FinanceApproval>
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('cashier')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'todayBills' => Collection::make(),
                'todayCollections' => Collection::make(),
                'pendingPayments' => Collection::make(),
                'outstandingDues' => Collection::make(),
                'refundRequests' => Collection::make(),
            ];
        }

        $todayBillsCount = Bill::query()
            ->whereDate('bill_date', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        $todayCollectionsTotal = (float) BillPayment::query()
            ->whereDate('collected_at', today())
            ->sum('amount');

        $pendingPaymentsCount = Bill::query()
            ->where('status', 'issued')
            ->whereColumn('paid_amount', '<', 'total')
            ->count();

        $outstandingTotal = (float) Bill::query()
            ->whereIn('status', ['draft', 'issued'])
            ->selectRaw('coalesce(sum(total - paid_amount), 0) as due')
            ->value('due');

        $refundRequestsCount = FinanceApproval::query()
            ->where('type', FinanceApproval::TYPE_REFUND)
            ->where('status', FinanceApproval::STATUS_PENDING)
            ->count();

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => "Today's Bills",
                    'value' => number_format($todayBillsCount),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.bills.index'),
                    'icon' => 'receipt',
                ],
                [
                    'label' => "Today's Collections",
                    'value' => '৳'.number_format($todayCollectionsTotal, 0),
                    'meta' => 'Cash / Card / Mobile',
                    'href' => route('admin.bills.collections'),
                    'icon' => 'pill',
                ],
                [
                    'label' => 'Pending Payments',
                    'value' => number_format($pendingPaymentsCount),
                    'meta' => 'Issued bills with balance due',
                    'href' => route('admin.bills.index', ['status' => 'issued']),
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Outstanding Dues',
                    'value' => '৳'.number_format($outstandingTotal, 0),
                    'meta' => 'Unpaid draft + issued balance',
                    'href' => route('admin.bills.index', ['status' => 'issued']),
                    'icon' => 'thermometer',
                ],
                [
                    'label' => 'Refund Requests',
                    'value' => number_format($refundRequestsCount),
                    'meta' => 'Awaiting director approval',
                    'href' => route('admin.owner.approvals.index'),
                    'icon' => 'clipboard-check',
                ],
            ],
            'todayBills' => Bill::query()
                ->with('patient')
                ->whereDate('bill_date', today())
                ->where('status', '!=', 'cancelled')
                ->latest('id')
                ->limit(8)
                ->get(),
            'todayCollections' => BillPayment::query()
                ->with(['bill.patient', 'collectedBy'])
                ->whereDate('collected_at', today())
                ->latest('id')
                ->limit(8)
                ->get(),
            'pendingPayments' => Bill::query()
                ->with('patient')
                ->where('status', 'issued')
                ->whereColumn('paid_amount', '<', 'total')
                ->orderBy('bill_date')
                ->limit(8)
                ->get(),
            'outstandingDues' => Bill::query()
                ->with('patient')
                ->whereIn('status', ['draft', 'issued'])
                ->whereColumn('paid_amount', '<', 'total')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
            'refundRequests' => FinanceApproval::query()
                ->with(['bill.patient', 'requestedBy'])
                ->where('type', FinanceApproval::TYPE_REFUND)
                ->where('status', FinanceApproval::STATUS_PENDING)
                ->latest('id')
                ->limit(8)
                ->get(),
        ];
    }
}
