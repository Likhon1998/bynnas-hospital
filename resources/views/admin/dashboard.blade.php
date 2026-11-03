@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', $deskHeading)

@section('content')
    @php
        $todayLabel = now()->format('l, j M Y');
    @endphp

    <div class="mc-desk">
        <section class="mc-desk__hero reveal">
            <div class="mc-desk__status">
                <span class="mc-desk__live" aria-hidden="true"></span>
                <div class="mc-desk__copy">
                    <p class="mc-desk__kicker">Live desk</p>
                    <p class="mc-desk__when">{{ $todayLabel }}</p>
                </div>
                <p class="mc-desk__stat">{{ $deskSummary }}</p>
            </div>
            @if ($quickActions !== [])
                <nav class="mc-desk__actions" aria-label="Desk actions">
                    <span class="mc-desk__actions-label">Desk actions</span>
                    <div class="mc-actions__grid">
                        @foreach ($quickActions as $action)
                            <a
                                href="{{ $action['href'] }}"
                                class="mc-action mc-action--{{ $action['tone'] }}"
                            >
                                <x-admin.nav-icon :name="$action['icon'] ?? 'cross'" class="h-3.5 w-3.5" />
                                <span>{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </nav>
            @endif
        </section>

        @if (($superAdmin['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($superAdmin['kpis']) }}">
                @foreach ($superAdmin['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="pulse" class="h-4 w-4" />
                                <span>System Activity</span>
                            </h2>
                            <p>Audit trail and desk movement</p>
                        </div>
                        <a href="{{ route('admin.system.audit-logs.index') }}">Audit logs</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($superAdmin['activity'] as $row)
                            <li>
                                <a href="{{ $row->href ?: '#' }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-{{ $row->kind === 'audit' ? 'blue' : 'green' }}"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $row->title }}</strong>
                                        <span>{{ $row->detail }}</span>
                                        <em>{{ $row->when }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No system activity recorded yet.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="pill" class="h-4 w-4" />
                                <span>Recent Transactions</span>
                            </h2>
                            <p>Latest paid bills</p>
                        </div>
                        <a href="{{ route('admin.bills.index') }}">Billing</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($superAdmin['transactions'] as $bill)
                            <li>
                                <a href="{{ route('admin.bills.show', $bill) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $bill->bill_no }}</strong>
                                        <span>{{ $bill->patient?->full_name ?: 'Patient' }} · ৳{{ number_format((float) $bill->paid_amount, 2) }}</span>
                                        <em>{{ $bill->paid_at?->diffForHumans() }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No paid bills yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        @endif

        @if (($ownerDirector['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($ownerDirector['kpis']) }}">
                @foreach ($ownerDirector['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="stethoscope" class="h-4 w-4" />
                                <span>Doctor Performance</span>
                            </h2>
                            <p>Appointments and revenue this month</p>
                        </div>
                        <a href="{{ route('admin.owner.finance.index') }}">Finance</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($ownerDirector['doctorPerformance'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>{{ $row->appointments }} appointments · ৳{{ number_format($row->revenue, 2) }}</span>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No doctor activity this month.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                                <span>Department-wise Revenue</span>
                            </h2>
                            <p>Paid bill revenue this month</p>
                        </div>
                        <a href="{{ route('admin.owner.finance.index') }}">Details</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($ownerDirector['departmentRevenue'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-green"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>৳{{ number_format($row->revenue, 2) }}</span>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No department revenue this month.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="users" class="h-4 w-4" />
                                <span>Staff Attendance</span>
                            </h2>
                            <p>Signed in today vs roster</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($ownerDirector['staffAttendance'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-{{ $row->present ? 'green' : 'amber' }}"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>{{ $row->present ? 'Present today' : 'Not signed in today' }}</span>
                                    <em>{{ $row->last_login ?: 'Never' }}</em>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No staff accounts to monitor.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="flask" class="h-4 w-4" />
                                <span>Pending Laboratory Reports</span>
                            </h2>
                            <p>Open lab orders</p>
                        </div>
                        <a href="{{ route('admin.labs.index') }}">Lab desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($ownerDirector['pendingLabs'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Lab order' }}</strong>
                                        <span>{{ $order->patient?->full_name ?: 'Patient' }} · {{ $order->status }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending laboratory reports.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="bed" class="h-4 w-4" />
                                <span>Pending Discharges</span>
                            </h2>
                            <p>Active and authorized admissions</p>
                        </div>
                        <a href="{{ route('admin.admissions.index') }}">Admissions</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($ownerDirector['pendingDischarges'] as $admission)
                            <li>
                                <a href="{{ route('admin.admissions.show', $admission) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-{{ $admission->status === 'discharge_authorized' ? 'green' : 'blue' }}"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $admission->admission_no }}</strong>
                                        <span>{{ $admission->patient?->full_name ?: 'Patient' }} · {{ str_replace('_', ' ', $admission->status) }}</span>
                                        <em>{{ $admission->ward?->name }} {{ $admission->bed?->bed_code }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending discharges.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="receipt" class="h-4 w-4" />
                                <span>Profit / Loss</span>
                            </h2>
                            <p>This month</p>
                        </div>
                        <a href="{{ route('admin.owner.approvals.index') }}">Approvals</a>
                    </div>
                    <ul class="mc-activity">
                        <li class="mc-activity__item" style="cursor: default;">
                            <span class="mc-activity__dot is-green"></span>
                            <span class="mc-activity__body">
                                <strong>Income</strong>
                                <span>৳{{ number_format($ownerDirector['profitLoss']['income'], 2) }}</span>
                            </span>
                        </li>
                        <li class="mc-activity__item" style="cursor: default;">
                            <span class="mc-activity__dot is-amber"></span>
                            <span class="mc-activity__body">
                                <strong>Expenses</strong>
                                <span>৳{{ number_format($ownerDirector['profitLoss']['expenses'], 2) }}</span>
                            </span>
                        </li>
                        <li class="mc-activity__item" style="cursor: default;">
                            <span class="mc-activity__dot is-blue"></span>
                            <span class="mc-activity__body">
                                <strong>Net</strong>
                                <span>৳{{ number_format($ownerDirector['profitLoss']['profit'], 2) }}</span>
                            </span>
                        </li>
                    </ul>
                </section>
            </div>
        @endif

        @if (($hospitalAdmin['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($hospitalAdmin['kpis']) }}">
                @foreach ($hospitalAdmin['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                                <span>Department-wise Activity</span>
                            </h2>
                            <p>Today's appointments and active admissions</p>
                        </div>
                        <a href="{{ route('admin.departments.index') }}">Departments</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($hospitalAdmin['departmentActivity'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>{{ $row->appointments }} appointments · {{ $row->admissions }} admissions</span>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No department activity recorded today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="users" class="h-4 w-4" />
                                <span>Staff Activity</span>
                            </h2>
                            <p>Who signed in and when</p>
                        </div>
                        <a href="{{ route('admin.doctors.index') }}">Doctors / schedules</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($hospitalAdmin['staffActivity'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-{{ $row->present ? 'green' : 'amber' }}"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>{{ $row->present ? 'Active today' : 'Not signed in today' }}</span>
                                    <em>{{ $row->last_login ?: 'Never' }}</em>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No staff activity yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="flask" class="h-4 w-4" />
                                <span>Pending Laboratory Reports</span>
                            </h2>
                            <p>Open lab orders</p>
                        </div>
                        <a href="{{ route('admin.labs.index') }}">Lab desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($hospitalAdmin['pendingLabs'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Lab order' }}</strong>
                                        <span>{{ $order->patient?->full_name ?: 'Patient' }} · {{ $order->status }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending laboratory reports.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="bed" class="h-4 w-4" />
                                <span>Pending Discharges</span>
                            </h2>
                            <p>Active inpatient stays</p>
                        </div>
                        <a href="{{ route('admin.admissions.index') }}">Admissions</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($hospitalAdmin['pendingDischarges'] as $admission)
                            <li>
                                <a href="{{ route('admin.admissions.show', $admission) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-{{ $admission->status === 'discharge_authorized' ? 'green' : 'blue' }}"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $admission->admission_no }}</strong>
                                        <span>{{ $admission->patient?->full_name ?: 'Patient' }} · {{ str_replace('_', ' ', $admission->status) }}</span>
                                        <em>{{ $admission->ward?->name }} {{ $admission->bed?->bed_code }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending discharges.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="pill" class="h-4 w-4" />
                                <span>Pharmacy &amp; services</span>
                            </h2>
                            <p>Monitor pharmacy and hospital services</p>
                        </div>
                        <a href="{{ route('admin.services.index') }}">Services</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($hospitalAdmin['pharmacy'] as $service)
                            <li>
                                <a href="{{ route('admin.services.edit', $service) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $service->title }}</strong>
                                        <span>{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pharmacy services configured.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                                <span>Director approvals</span>
                            </h2>
                            <p>Refunds and high-value discounts need Owner approval</p>
                        </div>
                        <a href="{{ route('admin.owner.approvals.index') }}">Request approval</a>
                    </div>
                    <ul class="mc-activity">
                        <li class="mc-activity__item" style="cursor: default;">
                            <span class="mc-activity__dot is-amber"></span>
                            <span class="mc-activity__body">
                                <strong>Finance guard</strong>
                                <span>Discounts of ৳500+ or 10%+ require Director approval before saving.</span>
                            </span>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports') }}" class="mc-activity__item">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>Operational reports</strong>
                                    <span>Review daily hospital activity and resolve issues from the ops desk.</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
        @endif

        @if (($receptionist['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($receptionist['kpis']) }}">
                @foreach ($receptionist['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="stethoscope" class="h-4 w-4" />
                                <span>Doctor Schedule</span>
                            </h2>
                            <p>Clinic sessions and booked visits today</p>
                        </div>
                        <a href="{{ route('admin.appointments.create') }}">Book visit</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($receptionist['doctorSchedules'] as $row)
                            <li class="mc-activity__item" style="cursor: default;">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>{{ $row->name }}</strong>
                                    <span>{{ $row->department }} · {{ $row->slots }}</span>
                                    <em>{{ $row->appointments }} booked</em>
                                </span>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No doctor schedules for today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="users" class="h-4 w-4" />
                                <span>Waiting Patients</span>
                            </h2>
                            <p>Checked in and waiting</p>
                        </div>
                        <a href="{{ route('admin.visits.index') }}">Visit desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($receptionist['waitingPatients'] as $visit)
                            <li>
                                <a href="{{ route('admin.visits.show', $visit) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $visit->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $visit->visit_no }} · {{ $visit->doctor?->name ?: 'Unassigned' }}</span>
                                        <em>{{ $visit->arrived_at?->diffForHumans() }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No waiting patients.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-plus" class="h-4 w-4" />
                                <span>Pending Serial Numbers</span>
                            </h2>
                            <p>Confirm website requests to issue serials</p>
                        </div>
                        <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Pending visits</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($receptionist['pendingSerials'] as $appointment)
                            <li>
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $appointment->appointment_no }}</strong>
                                        <span>{{ $appointment->patient?->full_name }} · {{ $appointment->doctor?->name }}</span>
                                        <em>{{ $appointment->status }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">All today's serials are assigned.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="search" class="h-4 w-4" />
                                <span>Patient Search</span>
                            </h2>
                            <p>Find by name, phone, or Patient ID</p>
                        </div>
                        <a href="{{ $receptionist['searchHref'] }}">Open registry</a>
                    </div>
                    <div class="mc-card__body">
                        <form method="GET" action="{{ route('admin.patients.index') }}" class="form-grid">
                            <div class="form-span-2">
                                <x-form.input name="q" label="Search patients" :value="request('q')" placeholder="PAT-… / name / phone" />
                            </div>
                            <div class="form-span-2 flex flex-wrap gap-2">
                                <button type="submit" class="admin-action">Search</button>
                                <a href="{{ route('admin.patients.create') }}" class="admin-action">Register patient</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        @endif

        @if (($cashierDesk['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($cashierDesk['kpis']) }}">
                @foreach ($cashierDesk['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'receipt'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="receipt" class="h-4 w-4" />
                                <span>Today's Bills</span>
                            </h2>
                            <p>Bills dated today</p>
                        </div>
                        <a href="{{ route('admin.bills.index') }}">All bills</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($cashierDesk['todayBills'] as $bill)
                            <li>
                                <a href="{{ route('admin.bills.show', $bill) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $bill->bill_no }}</strong>
                                        <span>{{ $bill->patient?->full_name }} · ৳{{ number_format((float) $bill->total, 0) }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No bills dated today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="pill" class="h-4 w-4" />
                                <span>Today's Collections</span>
                            </h2>
                            <p>Payments taken today</p>
                        </div>
                        <a href="{{ route('admin.bills.collections') }}">Report</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($cashierDesk['todayCollections'] as $payment)
                            <li>
                                <a href="{{ route('admin.bills.show', $payment->bill) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>৳{{ number_format((float) $payment->amount, 0) }} · {{ $payment->methodLabel() }}</strong>
                                        <span>{{ $payment->bill?->patient?->full_name }} · {{ $payment->bill?->bill_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No collections yet today.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clock" class="h-4 w-4" />
                                <span>Pending Payments</span>
                            </h2>
                            <p>Issued bills with balance due</p>
                        </div>
                        <a href="{{ route('admin.bills.index', ['status' => 'issued']) }}">Issued</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($cashierDesk['pendingPayments'] as $bill)
                            <li>
                                <a href="{{ route('admin.bills.show', $bill) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $bill->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $bill->bill_no }} · due ৳{{ number_format($bill->balanceDue(), 0) }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending payments.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="thermometer" class="h-4 w-4" />
                                <span>Outstanding Dues</span>
                            </h2>
                            <p>Largest open balances</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($cashierDesk['outstandingDues'] as $bill)
                            <li>
                                <a href="{{ route('admin.bills.show', $bill) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>৳{{ number_format($bill->balanceDue(), 0) }}</strong>
                                        <span>{{ $bill->patient?->full_name }} · {{ $bill->bill_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No outstanding dues.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                                <span>Refund Requests</span>
                            </h2>
                            <p>Awaiting director approval</p>
                        </div>
                        <a href="{{ route('admin.owner.approvals.index') }}">Approvals</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($cashierDesk['refundRequests'] as $approval)
                            <li>
                                <a href="{{ route('admin.owner.approvals.index') }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>৳{{ number_format((float) $approval->amount, 0) }}</strong>
                                        <span>{{ $approval->bill?->bill_no ?: 'No bill' }} · {{ $approval->reason }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending refund requests.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                                <span>Quick billing</span>
                            </h2>
                            <p>OPD, lab, bed, and service charges</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        <li>
                            <a href="{{ route('admin.bills.create') }}" class="mc-activity__item">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>Create bill</strong>
                                    <span>OPD consultation, lab, pharmacy, bed, or admission charges.</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bills.collections') }}" class="mc-activity__item">
                                <span class="mc-activity__dot is-green"></span>
                                <span class="mc-activity__body">
                                    <strong>Daily collection report</strong>
                                    <span>Cash, Card, bKash, and Nagad totals.</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
        @endif

        @if (($pathologistDesk['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($pathologistDesk['kpis']) }}">
                @foreach ($pathologistDesk['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'flask'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                                <span>Pending Report Verification</span>
                            </h2>
                            <p>Preliminary results awaiting approval</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'pending_verification']) }}">Queue</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['pendingVerification'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Report' }}</strong>
                                        <span>{{ $order->patient?->full_name }} · {{ $order->result?->displayValue() }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No reports awaiting verification.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="thermometer" class="h-4 w-4" />
                                <span>Abnormal Results</span>
                            </h2>
                            <p>Out of range or flagged</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'pending_verification', 'flag' => 'abnormal']) }}">Abnormal</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['abnormal'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Result' }}</strong>
                                        <span>{{ $order->result?->displayValue() }} · {{ $order->patient?->full_name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No abnormal results pending.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clock" class="h-4 w-4" />
                                <span>Urgent Reports</span>
                            </h2>
                            <p>Critical flags pending approval</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'pending_verification', 'flag' => 'urgent']) }}">Urgent</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['urgent'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Urgent' }}</strong>
                                        <span>{{ $order->order_no }} · {{ $order->patient?->full_name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No urgent reports pending.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard" class="h-4 w-4" />
                                <span>Approved Reports</span>
                            </h2>
                            <p>Verified and published today</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'completed']) }}">Approved</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['approved'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.report', $order) }}" class="mc-activity__item" target="_blank">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Report' }}</strong>
                                        <span>{{ $order->result?->signature_name ?: 'Signed' }} · {{ $order->patient?->full_name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No approved reports today.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="flask" class="h-4 w-4" />
                                <span>Today's Laboratory Reports</span>
                            </h2>
                            <p>Published laboratory and imaging reports</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['todayReports'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.report', $order) }}" class="mc-activity__item" target="_blank">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->order_no }}</strong>
                                        <span>{{ $order->test?->name }} · {{ $order->result?->displayValue() }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No laboratory reports published today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                                <span>Imaging requests</span>
                            </h2>
                            <p>Radiology and imaging studies</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['flag' => 'imaging']) }}">Imaging</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($pathologistDesk['imagingRequests'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Study' }}</strong>
                                        <span>{{ $order->patient?->full_name }} · {{ $order->statusLabel() }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No open imaging requests.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        @endif

        @if (($labDesk['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($labDesk['kpis']) }}">
                @foreach ($labDesk['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'flask'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="flask" class="h-4 w-4" />
                                <span>Today's Lab Orders</span>
                            </h2>
                            <p>Orders placed today</p>
                        </div>
                        <a href="{{ route('admin.labs.index') }}">Lab desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['todayOrders'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Order' }}</strong>
                                        <span>{{ $order->patient?->full_name }} · {{ $order->order_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No lab orders today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clock" class="h-4 w-4" />
                                <span>Pending Samples</span>
                            </h2>
                            <p>Awaiting collection</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'ordered']) }}">Ordered</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['pendingSamples'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $order->test?->name }} · {{ $order->order_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending samples.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard" class="h-4 w-4" />
                                <span>Collected Samples</span>
                            </h2>
                            <p>Ready for processing</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'collected']) }}">Collected</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['collected'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->sample_id ?: $order->order_no }}</strong>
                                        <span>{{ $order->patient?->full_name }} · {{ $order->test?->name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No collected samples waiting.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="thermometer" class="h-4 w-4" />
                                <span>Tests in Progress</span>
                            </h2>
                            <p>Currently processing</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'processing']) }}">Processing</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['inProgress'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Test' }}</strong>
                                        <span>{{ $order->sample_id ?: $order->order_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No tests in progress.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                                <span>Pending Results</span>
                            </h2>
                            <p>Awaiting pathologist verification</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'pending_verification']) }}">Pending</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['pendingResults'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Result' }}</strong>
                                        <span>{{ $order->result?->displayValue() }} · {{ $order->patient?->full_name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No results awaiting verification.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="users" class="h-4 w-4" />
                                <span>Completed Tests</span>
                            </h2>
                            <p>Verified today</p>
                        </div>
                        <a href="{{ route('admin.labs.index', ['status' => 'completed']) }}">Completed</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($labDesk['completed'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Test' }}</strong>
                                        <span>{{ $order->result?->displayValue() }} · {{ $order->patient?->full_name }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No completed tests today.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        @endif

        @if (($doctorDesk['enabled'] ?? false) === true)
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($doctorDesk['kpis']) }}">
                @foreach ($doctorDesk['kpis'] as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--navy reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </section>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="users" class="h-4 w-4" />
                                <span>Today's Patient Queue</span>
                            </h2>
                            <p>Your assigned open encounters</p>
                        </div>
                        <a href="{{ route('admin.visits.index') }}">Visit desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($doctorDesk['queue'] as $visit)
                            <li>
                                <a href="{{ route('admin.visits.show', $visit) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $visit->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $visit->visit_no }} · {{ $visit->statusLabel() }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No patients in your queue.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clock" class="h-4 w-4" />
                                <span>Waiting Patients</span>
                            </h2>
                            <p>Checked in for your clinic</p>
                        </div>
                        <a href="{{ route('admin.visits.index', ['status' => 'waiting']) }}">Waiting</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($doctorDesk['waiting'] as $visit)
                            <li>
                                <a href="{{ route('admin.visits.show', $visit) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $visit->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $visit->visit_no }}</span>
                                        <em>{{ $visit->arrived_at?->diffForHumans() }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No waiting patients.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                                <span>Completed Consultations</span>
                            </h2>
                            <p>Finished today</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($doctorDesk['completed'] as $visit)
                            <li>
                                <a href="{{ route('admin.visits.show', $visit) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-green"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $visit->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $visit->diagnosis ?: $visit->visit_no }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No completed consultations yet today.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="calendar" class="h-4 w-4" />
                                <span>Follow-up Patients</span>
                            </h2>
                            <p>Recent encounters with follow-up notes</p>
                        </div>
                        <a href="{{ route('admin.appointments.create') }}">Book follow-up</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($doctorDesk['followUps'] as $visit)
                            <li>
                                <a href="{{ route('admin.appointments.create', ['patient_id' => $visit->patient_id]) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-blue"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $visit->patient?->full_name ?: 'Patient' }}</strong>
                                        <span>{{ $visit->follow_up_instructions }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No follow-up patients in the last 14 days.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <div class="mc-layout mc-layout--pair">
                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="flask" class="h-4 w-4" />
                                <span>Pending Laboratory Reports</span>
                            </h2>
                            <p>Lab / imaging orders for your patients</p>
                        </div>
                        <a href="{{ route('admin.labs.index') }}">Lab desk</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($doctorDesk['pendingLabs'] as $order)
                            <li>
                                <a href="{{ route('admin.labs.show', $order) }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-amber"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $order->test?->name ?: 'Order' }}</strong>
                                        <span>{{ $order->patient?->full_name }} · {{ $order->status }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No pending laboratory reports.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                                <span>Care actions</span>
                            </h2>
                            <p>Admit, follow up, and review history</p>
                        </div>
                    </div>
                    <ul class="mc-activity">
                        <li>
                            <a href="{{ route('admin.admissions.create') }}" class="mc-activity__item">
                                <span class="mc-activity__dot is-blue"></span>
                                <span class="mc-activity__body">
                                    <strong>Recommend admission</strong>
                                    <span>Start an inpatient admission for a patient under your care.</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.appointments.index', ['doctor' => $doctorDesk['doctor']->id]) }}" class="mc-activity__item">
                                <span class="mc-activity__dot is-green"></span>
                                <span class="mc-activity__body">
                                    <strong>Consultation history</strong>
                                    <span>Your personal appointment and visit schedule.</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
        @endif

        @if ($kpis !== [])
            <section class="mc-kpis mc-desk__kpis" style="--kpi-count: {{ count($kpis) }}">
                @foreach ($kpis as $index => $kpi)
                    <a
                        href="{{ $kpi['href'] ?: '#' }}"
                        class="mc-kpi mc-kpi--{{ $kpi['tone'] }} reveal"
                        style="--delay: {{ min(5, $index) * 60 }}ms"
                    >
                        <div class="mc-kpi__top">
                            <div>
                                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                            </div>
                            <span class="mc-kpi__icon" aria-hidden="true">
                                <x-admin.nav-icon :name="$kpi['icon'] ?? 'cross'" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="mc-progress"><span style="width: {{ $kpi['percent'] }}%"></span></div>
                        <p @class(['mc-kpi__trend', 'is-up' => $kpi['trendUp'], 'is-down' => ! $kpi['trendUp']])>{{ $kpi['trend'] }}</p>
                    </a>
                @endforeach
            </section>
        @endif

        <div class="mc-layout">
            <div class="mc-main">
                @foreach ($queueSections as $section)
                    <section id="queue-{{ $section['desk'] }}-{{ $section['kind'] }}" class="mc-card mc-card--queue reveal">
                        <div class="mc-card__head mc-card__head--table">
                            <div>
                                <h2 class="mc-card__title">
                                    <x-admin.nav-icon :name="$section['icon'] ?? 'cross'" class="h-4 w-4" />
                                    <span>{{ $section['label'] }}</span>
                                </h2>
                                <p>{{ $section['desk_label'] }} · {{ $section['items']->count() }} open</p>
                            </div>
                        </div>

                        <div class="table-frame mc-table-frame">
                            <table class="pro-table mc-table">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Detail</th>
                                        <th class="col-status">Status</th>
                                        <th class="col-queue-actions">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($section['items'] as $item)
                                        <tr>
                                            <td class="col-primary">{{ $item->title }}</td>
                                            <td>{{ $item->detail ?: '—' }}</td>
                                            <td class="col-status">
                                                <x-admin.status-pill :value="$item->is_critical ? 'critical' : $item->kind" />
                                            </td>
                                            <td class="col-queue-actions">
                                                <div class="mc-queue__actions">
                                                    <a href="{{ $item->href }}" class="mc-btn mc-btn--ghost">Open</a>
                                                    @if ($item->canAcknowledge())
                                                        <form method="POST" action="{{ route('admin.work.acknowledge', $item) }}">
                                                            @csrf
                                                            <button type="submit" class="mc-btn mc-btn--ghost">Acknowledge</button>
                                                        </form>
                                                    @endif
                                                    @if ($item->canEscalate())
                                                        <form method="POST" action="{{ route('admin.work.escalate', $item) }}">
                                                            @csrf
                                                            <button type="submit" class="mc-btn mc-btn--primary">Escalate</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="is-empty"><td colspan="4">Queue clear.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>

            <aside class="mc-side mc-side--sticky">
                @if ($showOccupancy)
                    <section class="mc-card mc-card--occupancy reveal">
                        <div class="mc-card__head">
                            <div>
                                <h2 class="mc-card__title">
                                    <x-admin.nav-icon name="bed" class="h-4 w-4" />
                                    <span>Bed occupancy</span>
                                </h2>
                                <p>Live ward capacity</p>
                            </div>
                        </div>
                        <div class="mc-occupancy">
                            <span class="mc-donut mc-donut--lg mc-donut--teal" style="--p: {{ $occupancy['percent'] }}">
                                <em>{{ $occupancy['percent'] }}%</em>
                            </span>
                            <ul class="mc-occupancy__stats">
                                <li><span>Occupied</span><strong>{{ $occupancy['occupied'] }}</strong></li>
                                <li><span>Available</span><strong>{{ $occupancy['available'] }}</strong></li>
                                <li><span>Dirty</span><strong>{{ $occupancy['dirty'] }}</strong></li>
                                <li><span>Cleaning</span><strong>{{ $occupancy['cleaning'] }}</strong></li>
                                <li><span>Maintenance</span><strong>{{ $occupancy['maintenance'] }}</strong></li>
                            </ul>
                        </div>
                    </section>
                @endif

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="pulse" class="h-4 w-4" />
                                <span>Unread alerts</span>
                            </h2>
                            <p>{{ $alertTotal }} unread alert{{ $alertTotal === 1 ? '' : 's' }}</p>
                        </div>
                        <a href="{{ $alertsHref }}">View all</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($alerts as $alert)
                            <li>
                                <a href="{{ $alert['href'] }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-{{ $alert['tone'] }}"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $alert['title'] }}</strong>
                                        <span>{{ $alert['detail'] }}</span>
                                        <em>{{ $alert['time'] }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No unread alerts right now.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="mc-card reveal">
                    <div class="mc-card__head">
                        <div>
                            <h2 class="mc-card__title">
                                <x-admin.nav-icon name="clipboard-clock" class="h-4 w-4" />
                                <span>Queue history</span>
                            </h2>
                            <p>Completed work stays here</p>
                        </div>
                        <a href="{{ $historyHref }}">View all</a>
                    </div>
                    <ul class="mc-activity">
                        @forelse ($history as $item)
                            <li>
                                <a href="{{ $item->href }}" class="mc-activity__item">
                                    <span class="mc-activity__dot is-{{ $item->tone }}"></span>
                                    <span class="mc-activity__body">
                                        <strong>{{ $item->title }}</strong>
                                        <span>{{ $item->kindLabel() }}</span>
                                        <em>{{ $item->completed_at?->diffForHumans() }}</em>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="mc-activity__empty">No completed work yet.</li>
                        @endforelse
                    </ul>
                </section>

                @if ($reportLinks !== [])
                    <section class="mc-card reveal">
                        <div class="mc-card__head">
                            <div>
                                <h2 class="mc-card__title">
                                    <x-admin.nav-icon name="thermometer" class="h-4 w-4" />
                                    <span>Essential reports</span>
                                </h2>
                                <p>Ops shortcuts</p>
                            </div>
                        </div>
                        <nav class="mc-links">
                            @foreach ($reportLinks as $link)
                                <a href="{{ $link['href'] }}">
                                    <x-admin.nav-icon :name="$link['icon'] ?? 'cross'" class="h-4 w-4" />
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
