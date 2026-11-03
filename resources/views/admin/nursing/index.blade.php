@extends('layouts.admin')
@section('title', 'Nursing')
@section('heading', 'Nursing workspace')
@section('content')
    <p class="form-lede">Assigned inpatients by ward. Open a ward for due tasks, observations, and shift handover.</p>

    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--blue">
            <p class="mc-kpi__label">Admitted</p>
            <p class="mc-kpi__value">{{ $counts['patients'] }}</p>
            <p class="mc-kpi__meta">On the wards</p>
        </article>
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Open tasks</p>
            <p class="mc-kpi__value">{{ $counts['due'] }}</p>
            <p class="mc-kpi__meta">Still to do</p>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <p class="mc-kpi__label">Overdue</p>
            <p class="mc-kpi__value">{{ $counts['overdue'] }}</p>
            <p class="mc-kpi__meta">Need attention</p>
        </article>
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <div class="table-toolbar">
            <p class="table-count">{{ $wards->count() }} {{ \Illuminate\Support\Str::plural('ward', $wards->count()) }}</p>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Ward</th>
                        <th>Patients</th>
                        <th>Open tasks</th>
                        <th>Overdue</th>
                        <th>Last handover</th>
                        <th class="col-pair-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($wards as $ward)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $ward->name }}</span>
                                <span class="cell-sub">{{ $ward->typeLabel() }} · {{ $ward->code }}</span>
                            </td>
                            <td>{{ $ward->occupied_beds_count }} / {{ $ward->beds_count }}</td>
                            <td>{{ $ward->open_tasks_count }}</td>
                            <td>{{ $ward->overdue_tasks_count }}</td>
                            <td>
                                @if ($ward->latestHandover)
                                    {{ $ward->latestHandover->handed_over_at?->format('d M H:i') }}
                                    <span class="cell-sub">{{ $ward->latestHandover->shiftLabel() }} → {{ $ward->latestHandover->incomingShiftLabel() }}</span>
                                @else
                                    No handover yet
                                @endif
                            </td>
                            <td class="col-pair-actions">
                                <div class="table-actions">
                                    <a href="{{ route('admin.nursing.wards.show', $ward) }}" class="table-btn table-btn--edit">Open ward</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No active wards.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
