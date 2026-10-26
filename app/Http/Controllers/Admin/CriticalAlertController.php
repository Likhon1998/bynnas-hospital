<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CriticalAlertFeed;
use Illuminate\View\View;

class CriticalAlertController extends Controller
{
    public function __invoke(CriticalAlertFeed $feed): View
    {
        $payload = $feed->all(30);

        return view('admin.operations.alerts', [
            'alerts' => $payload['alerts'],
            'counts' => $payload['counts'],
        ]);
    }
}
