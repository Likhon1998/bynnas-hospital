<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\FinanceApproval;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceApprovalController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('finance.view')
            || $request->user()?->can('finance.approve')
            || $request->user()?->can('billing.manage'),
            403
        );

        $approvals = FinanceApproval::query()
            ->with(['bill.patient', 'requestedBy', 'reviewedBy'])
            ->latest('id')
            ->paginate(20);

        $bills = Bill::query()
            ->with('patient')
            ->whereIn('status', ['draft', 'issued', 'paid'])
            ->latest('id')
            ->limit(40)
            ->get();

        return view('admin.owner.approvals', compact('approvals', 'bills'));
    }

    public function store(Request $request, SystemAuditor $auditor): RedirectResponse
    {
        abort_unless(
            $request->user()?->can('billing.manage')
            || $request->user()?->can('finance.approve')
            || $request->user()?->can('finance.view'),
            403
        );

        $validated = $request->validate([
            'type' => ['required', Rule::in([FinanceApproval::TYPE_DISCOUNT, FinanceApproval::TYPE_REFUND])],
            'bill_id' => ['nullable', 'integer', 'exists:bills,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $approval = FinanceApproval::query()->create([
            ...$validated,
            'status' => FinanceApproval::STATUS_PENDING,
            'requested_by' => $request->user()?->id,
        ]);

        $auditor->record('finance.request', 'Requested '.$approval->type.' approval.', $approval, [
            'amount' => $approval->amount,
            'bill_id' => $approval->bill_id,
        ], $request->user());

        return back()->with('status', 'Approval request submitted.');
    }

    public function decide(Request $request, FinanceApproval $approval, SystemAuditor $auditor): RedirectResponse
    {
        abort_unless($request->user()?->can('finance.approve'), 403);
        abort_unless($approval->isPending(), 422, 'This request was already decided.');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'notes' => ['nullable', 'string'],
        ]);

        $approval->update([
            'status' => $validated['decision'],
            'notes' => $validated['notes'] ?? $approval->notes,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        if ($validated['decision'] === 'approved' && $approval->type === FinanceApproval::TYPE_REFUND && $approval->bill) {
            $bill = $approval->bill;
            $refundAmount = min((float) $approval->amount, (float) $bill->paid_amount);
            $paidAmount = max(0, round((float) $bill->paid_amount - $refundAmount, 2));

            $bill->update([
                'paid_amount' => $paidAmount,
                'status' => $paidAmount <= 0 && $bill->status === 'paid' ? 'issued' : ($paidAmount >= (float) $bill->total ? 'paid' : 'issued'),
                'paid_at' => $paidAmount >= (float) $bill->total ? $bill->paid_at : null,
            ]);
        }

        $auditor->record(
            'finance.'.$validated['decision'],
            ucfirst($validated['decision']).' '.$approval->type.' request #'.$approval->id,
            $approval,
            ['amount' => $approval->amount],
            $request->user()
        );

        return back()->with('status', 'Request '.$validated['decision'].'.');
    }
}
