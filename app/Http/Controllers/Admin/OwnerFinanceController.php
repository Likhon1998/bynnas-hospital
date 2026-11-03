<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\HospitalExpense;
use App\Support\OwnerDirectorOverview;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerFinanceController extends Controller
{
    public function index(Request $request, OwnerDirectorOverview $overview): View
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $owner = $overview->for($request->user());

        $expenses = HospitalExpense::query()
            ->with('recordedBy')
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15);

        $outstandingBills = Bill::query()
            ->with('patient')
            ->whereIn('status', ['draft', 'issued'])
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return view('admin.owner.finance', [
            'owner' => $owner,
            'expenses' => $expenses,
            'outstandingBills' => $outstandingBills,
        ]);
    }

    public function storeExpense(Request $request, SystemAuditor $auditor): RedirectResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $expense = HospitalExpense::query()->create([
            ...$validated,
            'recorded_by' => $request->user()?->id,
        ]);

        $auditor->record('finance.expense', 'Recorded expense '.$expense->description, $expense, [
            'amount' => $expense->amount,
            'category' => $expense->category,
        ], $request->user());

        return back()->with('status', 'Expense recorded.');
    }
}
