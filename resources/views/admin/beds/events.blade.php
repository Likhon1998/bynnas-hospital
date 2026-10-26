@extends('layouts.admin')

@section('title', 'Bed History')
@section('heading', 'Bed History')

@section('content')
    <section class="mc-card">
        <div class="mc-card__head mc-card__head--table">
            <div>
                <h2>Audit trail</h2>
                <p>Every admit, discharge, transfer, reservation, inspection, and maintenance event</p>
            </div>
            <div class="mc-table-tools">
                <a href="{{ route('admin.beds.index') }}" class="mc-btn mc-btn--dark">Back to board</a>
                <form method="GET" action="{{ route('admin.beds.events') }}" class="mc-table-tools">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Bed, patient, notes..." class="mc-input">
                    <select name="bed_id" class="mc-input mc-input--status">
                        <option value="">All beds</option>
                        @foreach ($beds as $bedOption)
                            <option value="{{ $bedOption->id }}" @selected($bedId === $bedOption->id)>{{ $bedOption->bed_code }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="mc-input mc-input--status">
                        <option value="">All events</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType }}" @selected($type === $eventType)>{{ str_replace('_', ' ', $eventType) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="mc-btn mc-btn--primary">Filter</button>
                </form>
            </div>
        </div>

        <div class="table-frame mc-table-frame">
            <table class="pro-table mc-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Bed</th>
                        <th>Event</th>
                        <th>Patient</th>
                        <th>Actor</th>
                        <th>Status change</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>
                                <span class="mc-cell-title">{{ $event->created_at?->format('M j, Y H:i') }}</span>
                                <span class="mc-cell-sub">{{ $event->created_at?->diffForHumans() }}</span>
                            </td>
                            <td class="col-primary">
                                {{ $event->bed?->bed_code ?: '—' }}
                                @if ($event->relatedBed)
                                    <span class="mc-cell-sub">↔ {{ $event->relatedBed->bed_code }}</span>
                                @endif
                            </td>
                            <td>{{ str_replace('_', ' ', $event->event_type) }}</td>
                            <td>{{ $event->patient?->full_name ?: '—' }}</td>
                            <td>{{ $event->actor?->name ?: 'System' }}</td>
                            <td>
                                @if ($event->from_status || $event->to_status)
                                    {{ $event->from_status ?: '—' }} → {{ $event->to_status ?: '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $event->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="7">No bed events match this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mc-pager">{{ $events->links() }}</div>
    </section>
@endsection
