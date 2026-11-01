<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcknowledgeWorkItemRequest;
use App\Http\Requests\Admin\EscalateWorkItemRequest;
use App\Models\WorkItem;
use App\Support\WorkInbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkItemController extends Controller
{
    public function history(Request $request, WorkInbox $inbox): View
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $inbox->syncFor($user);

        return view('admin.work.history', [
            'items' => $inbox->historyFor($user, 50),
            'deskHeading' => $inbox->deskHeading($user),
        ]);
    }

    public function acknowledge(AcknowledgeWorkItemRequest $request, WorkItem $workItem, WorkInbox $inbox): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        abort_unless($inbox->isVisibleTo($actor, $workItem), 403);
        abort_unless($workItem->canAcknowledge(), 403);

        $inbox->acknowledge($workItem, $actor);

        return back()->with('status', 'Queue item acknowledged.');
    }

    public function escalate(EscalateWorkItemRequest $request, WorkItem $workItem, WorkInbox $inbox): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        abort_unless($inbox->isVisibleTo($actor, $workItem), 403);
        abort_unless($workItem->canEscalate(), 403);

        $inbox->escalate($workItem, $actor);

        return back()->with('status', 'Critical item escalated to administration.');
    }
}
