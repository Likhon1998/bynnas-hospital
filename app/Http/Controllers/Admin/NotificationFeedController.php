<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CriticalAlertFeed;
use App\Support\WorkInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    public function index(Request $request, CriticalAlertFeed $feed): JsonResponse
    {
        $user = $request->user();
        $payload = $feed->for($user, 8);

        return response()->json([
            'total' => $payload['total'],
            'counts' => $payload['counts'],
            'ids' => $payload['ids'],
            'href' => $user?->can('operations.manage')
                ? route('admin.alerts')
                : ($user?->can('beds.view') ? route('admin.beds.index') : route('admin.dashboard')),
            'alerts' => $payload['alerts']->values()->all(),
        ]);
    }

    public function markSeen(Request $request, CriticalAlertFeed $feed, WorkInbox $inbox): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        /** @var array{ids?: list<string>, all?: bool} $validated */
        $validated = $request->validate([
            'ids' => ['required_without:all', 'array', 'max:500'],
            'ids.*' => ['required', 'string', 'max:120'],
            'all' => ['sometimes', 'boolean'],
        ]);

        $inbox->markRead(
            $user,
            $validated['ids'] ?? [],
            (bool) ($validated['all'] ?? false),
        );

        $payload = $feed->for($user, 8);

        return response()->json([
            'ok' => true,
            'total' => $payload['total'],
            'counts' => $payload['counts'],
            'ids' => $payload['ids'],
            'alerts' => $payload['alerts']->values()->all(),
        ]);
    }
}
