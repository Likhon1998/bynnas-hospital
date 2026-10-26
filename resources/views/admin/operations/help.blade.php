@extends('layouts.admin')

@section('title', 'Help & Supports')
@section('heading', 'Help & Supports')

@section('content')
    <section class="mc-card">
        <div class="mc-card__head">
            <div>
                <h2>{{ $hospitalName }} desk help</h2>
                <p>How to use the operations board</p>
            </div>
        </div>
        <div class="space-y-4 px-5 pb-5 text-sm leading-6 text-[#4b5563]">
            <p><strong class="text-[#111827]">Bed & Wards</strong> — track bed status (available, occupied, dirty, cleaning), filter by ward type, and update turnaround from the action menu.</p>
            <p><strong class="text-[#111827]">Emergency Flow</strong> — today’s urgent visits plus free beds that can take an admission.</p>
            <p><strong class="text-[#111827]">Critical Alerts</strong> — dirty/cleaning beds, pending visits, unpaid bills, and unread website messages.</p>
            <p><strong class="text-[#111827]">Capacity Forecasts</strong> — 7-day occupancy outlook from current beds and booked appointments.</p>
            <p><strong class="text-[#111827]">Operational Reports</strong> — ward, visit, patient, doctor, and billing totals for the week.</p>
            @if ($phone || $emergency || $email)
                <div class="rounded-xl bg-[#f3f4f6] p-4">
                    <p class="font-semibold text-[#111827]">Need the on-call desk?</p>
                    @if ($phone)<p class="mt-2">OPD {{ $phone }}</p>@endif
                    @if ($emergency)<p>Emergency {{ $emergency }}</p>@endif
                    @if ($email)<p>{{ $email }}</p>@endif
                </div>
            @endif
        </div>
    </section>
@endsection
