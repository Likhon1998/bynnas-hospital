<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasRole('super_admin')
            || $request->user()?->can('audit.view'),
            403
        );

        $logs = SystemAuditLog::query()
            ->with('user')
            ->latest('id')
            ->paginate(30);

        return view('admin.system.audit-logs', compact('logs'));
    }
}
