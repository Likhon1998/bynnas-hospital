@extends('layouts.admin')

@section('title', 'Queue history')
@section('heading', 'Queue history')

@section('content')
    <div class="mc-desk">
        <section class="mc-card mc-card--queue reveal">
            <div class="mc-card__head mc-card__head--table">
                <div>
                    <h2>{{ $deskHeading }} history</h2>
                    <p>Completed tasks remain available after they leave the active queue</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="mc-filter-btn">Back to desk</a>
            </div>

            <div class="table-frame mc-table-frame">
                <table class="pro-table mc-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Queue</th>
                            <th>Detail</th>
                            <th class="col-when">Completed</th>
                            <th class="col-open">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="col-primary">{{ $item->title }}</td>
                                <td>{{ $item->kindLabel() }}</td>
                                <td>{{ $item->detail ?: '—' }}</td>
                                <td class="col-when">{{ $item->completed_at?->format('d M Y g:i A') ?: '—' }}</td>
                                <td class="col-open">
                                    <x-admin.row-menu>
                                        <a href="{{ $item->href }}">Open</a>
                                    </x-admin.row-menu>
                                </td>
                            </tr>
                        @empty
                            <tr class="is-empty"><td colspan="5">No completed work yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
