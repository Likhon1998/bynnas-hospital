<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BedEventController extends Controller
{
    public function index(Request $request): View
    {
        $bedId = $request->integer('bed_id') ?: null;
        $type = $request->string('type')->toString();
        $search = $request->string('q')->toString();

        $events = BedEvent::query()
            ->with(['bed.ward', 'patient', 'actor', 'relatedBed'])
            ->when($bedId, fn ($query) => $query->where('bed_id', $bedId))
            ->when($type !== '', fn ($query) => $query->where('event_type', $type))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('notes', 'like', '%'.$search.'%')
                        ->orWhere('event_type', 'like', '%'.$search.'%')
                        ->orWhereHas('bed', fn ($bed) => $bed->where('bed_code', 'like', '%'.$search.'%'))
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.beds.events', [
            'events' => $events,
            'beds' => Bed::query()->orderBy('bed_code')->get(['id', 'bed_code']),
            'bedId' => $bedId,
            'type' => $type,
            'search' => $search,
            'eventTypes' => [
                BedEvent::TYPE_ADMITTED,
                BedEvent::TYPE_DISCHARGED,
                BedEvent::TYPE_CLEANING_STARTED,
                BedEvent::TYPE_INSPECTION_PASSED,
                BedEvent::TYPE_INSPECTION_FAILED,
                BedEvent::TYPE_RESERVED,
                BedEvent::TYPE_RESERVATION_CANCELLED,
                BedEvent::TYPE_RESERVATION_EXPIRED,
                BedEvent::TYPE_TRANSFERRED_OUT,
                BedEvent::TYPE_TRANSFERRED_IN,
                BedEvent::TYPE_MAINTENANCE_STARTED,
                BedEvent::TYPE_MAINTENANCE_ENDED,
            ],
        ]);
    }
}
