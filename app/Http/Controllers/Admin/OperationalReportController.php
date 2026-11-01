<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OperationalReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationalReportController extends Controller
{
    public function __invoke(Request $request, OperationalReport $report): View
    {
        $period = $request->string('period')->toString();

        if (! in_array($period, ['today', 'week', '7d'], true)) {
            $period = 'week';
        }

        return view('admin.operations.reports', $report->forPeriod($period));
    }
}
