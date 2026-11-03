<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CashierOverview;
use App\Support\DashboardOverview;
use App\Support\DoctorOverview;
use App\Support\HospitalAdminOverview;
use App\Support\LabOverview;
use App\Support\OwnerDirectorOverview;
use App\Support\PathologistOverview;
use App\Support\ReceptionistOverview;
use App\Support\SuperAdminOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardOverview $overview,
        SuperAdminOverview $superAdminOverview,
        OwnerDirectorOverview $ownerDirectorOverview,
        HospitalAdminOverview $hospitalAdminOverview,
        ReceptionistOverview $receptionistOverview,
        DoctorOverview $doctorOverview,
        LabOverview $labOverview,
        PathologistOverview $pathologistOverview,
        CashierOverview $cashierOverview,
    ): View {
        return view('admin.dashboard', array_merge(
            $overview->for($request->user()),
            [
                'superAdmin' => $superAdminOverview->for($request->user()),
                'ownerDirector' => $ownerDirectorOverview->for($request->user()),
                'hospitalAdmin' => $hospitalAdminOverview->for($request->user()),
                'receptionist' => $receptionistOverview->for($request->user()),
                'doctorDesk' => $doctorOverview->for($request->user()),
                'labDesk' => $labOverview->for($request->user()),
                'pathologistDesk' => $pathologistOverview->for($request->user()),
                'cashierDesk' => $cashierOverview->for($request->user()),
            ],
        ));
    }
}
