@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($stats as $stat)
            <article class="rounded-3xl bg-white p-5 shadow-[0_18px_40px_-28px_rgba(7,20,31,0.45)] ring-1 ring-sand-100">
                <p class="text-sm text-ink-600">{{ $stat['label'] }}</p>
                <p class="mt-2 font-display text-3xl text-navy-900">{{ number_format($stat['value']) }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl text-navy-900">Upcoming appointments</h2>
                @can('appointments.view')
                    <a href="{{ route('admin.appointments.index') }}" class="text-sm font-semibold text-teal-700">View all</a>
                @endcan
            </div>
            @if ($upcomingAppointments->isEmpty())
                <p class="mt-6 text-sm text-ink-600">No upcoming appointments.</p>
            @else
                <ul class="mt-5 divide-y divide-sand-100">
                    @foreach ($upcomingAppointments as $appointment)
                        <li class="flex items-start justify-between gap-3 py-3">
                            <div>
                                <p class="font-medium text-navy-900">{{ $appointment->patient?->full_name }}</p>
                                <p class="text-sm text-ink-600">{{ $appointment->doctor?->name }} · {{ $appointment->department?->name }}</p>
                            </div>
                            <p class="shrink-0 text-sm text-ink-400">{{ $appointment->appointment_date?->format('d M') }} {{ substr((string) $appointment->start_time, 0, 5) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl text-navy-900">Latest messages</h2>
                @can('content.manage')
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-semibold text-teal-700">Inbox</a>
                @endcan
            </div>
            @if ($recentMessages->isEmpty())
                <p class="mt-6 text-sm text-ink-600">No contact messages yet.</p>
            @else
                <ul class="mt-5 divide-y divide-sand-100">
                    @foreach ($recentMessages as $message)
                        <li class="py-3">
                            <p class="font-medium text-navy-900">{{ $message->name }}</p>
                            <p class="text-sm text-ink-600">{{ $message->subject ?: 'Website enquiry' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    @can('billing.manage')
        <section class="mt-8 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-display text-xl text-navy-900">Patient billing</h2>
                <a href="{{ route('admin.bills.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Generate bill</a>
            </div>
            @if ($recentBills->isEmpty())
                <p class="mt-6 text-sm text-ink-600">No bills yet. Reception can generate a patient bill from here.</p>
            @else
                <ul class="mt-5 divide-y divide-sand-100">
                    @foreach ($recentBills as $bill)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <p class="font-medium text-navy-900">{{ $bill->bill_no }} · {{ $bill->patient?->full_name }}</p>
                                <p class="text-sm text-ink-600">{{ number_format((float) $bill->total, 2) }} · {{ str_replace('_', ' ', $bill->status) }}</p>
                            </div>
                            <a href="{{ route('admin.bills.show', $bill) }}" class="text-sm font-semibold text-teal-700">Open</a>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p class="mt-4 text-sm">
                <a href="{{ route('admin.bills.index') }}" class="font-semibold text-teal-700">View all bills</a>
            </p>
        </section>
    @endcan
@endsection
