<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelLabOrderRequest;
use App\Http\Requests\Admin\CollectLabOrderRequest;
use App\Http\Requests\Admin\ProcessLabOrderRequest;
use App\Http\Requests\Admin\StoreLabOrderRequest;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Visit;
use App\Support\LabDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! array_key_exists($status, LabOrder::STATUSES)) {
            $status = '';
        }

        $orders = LabOrder::query()
            ->with(['patient', 'test', 'visit', 'result'])
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status),
                fn ($query) => $query->open(),
            )
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('order_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'))
                        ->orWhereHas('test', fn ($test) => $test->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByRaw("case status when 'processing' then 1 when 'collected' then 2 when 'ordered' then 3 else 4 end")
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.labs.index', [
            'orders' => $orders,
            'counts' => [
                'ordered' => LabOrder::query()->where('status', 'ordered')->count(),
                'collected' => LabOrder::query()->where('status', 'collected')->count(),
                'processing' => LabOrder::query()->where('status', 'processing')->count(),
                'critical' => LabOrder::query()->critical()->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('lab.order') ?? false, 403);

        $visit = $request->filled('visit_id')
            ? Visit::query()->with(['patient', 'doctor'])->find($request->integer('visit_id'))
            : null;

        return view('admin.labs.create', [
            'visit' => $visit,
            'visits' => Visit::query()
                ->with('patient')
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('arrived_at')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
            'tests' => LabTest::query()->active()->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    public function store(StoreLabOrderRequest $request, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $order = $desk->order($request->safe()->only([
            'visit_id',
            'lab_test_id',
            'clinical_notes',
        ]), $actor);

        return redirect()
            ->route('admin.labs.show', $order)
            ->with('status', 'Lab order '.$order->order_no.' placed.');
    }

    public function show(LabOrder $labOrder): View
    {
        $labOrder->load([
            'patient',
            'visit.doctor',
            'appointment',
            'test',
            'orderedBy',
            'collectedBy',
            'processingBy',
            'cancelledBy',
            'result.enteredBy',
            'result.verifiedBy',
            'events.actor',
        ]);

        return view('admin.labs.show', [
            'order' => $labOrder,
        ]);
    }

    public function collect(CollectLabOrderRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->collect($labOrder, $actor);

        return back()->with('status', 'Sample collected.');
    }

    public function process(ProcessLabOrderRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->startProcessing($labOrder, $actor);

        return back()->with('status', 'Sample is in process.');
    }

    public function cancel(CancelLabOrderRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->cancel($labOrder, $request->validated('cancellation_reason'), $actor);

        return back()->with('status', 'Lab order cancelled.');
    }
}
