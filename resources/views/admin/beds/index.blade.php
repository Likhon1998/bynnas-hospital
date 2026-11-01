@extends('layouts.admin')

@section('title', 'Bed & Wards')
@section('heading', 'Bed & Wards')

@section('content')
    <section class="mc-kpis">
        @foreach ($kpis as $kpi)
            <a
                href="{{ $kpi['href'] }}"
                @class([
                    'mc-kpi',
                    'mc-kpi--'.$kpi['tone'],
                    'mc-kpi--link',
                    'is-active' => ($status === '' && $kpi['label'] === 'Census beds')
                        || ($status === 'occupied' && $kpi['label'] === 'Occupied')
                        || ($status === 'available' && $kpi['label'] === 'Ready to admit')
                        || ($status === 'turnaround' && $kpi['label'] === 'Turnaround queue'),
                ])
            >
                <div class="mc-kpi__top">
                    <div>
                        <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                        <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                        @if (! empty($kpi['meta']))
                            <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                        @endif
                    </div>
                    @if ($kpi['chart'] === 'donut')
                        <span class="mc-donut" style="--p: {{ $kpi['percent'] }}"><em>{{ $kpi['percent'] }}%</em></span>
                    @else
                        <span class="mc-kpi__pct">{{ $kpi['percent'] }}%</span>
                    @endif
                </div>
                @if ($kpi['chart'] === 'bar')
                    <div class="mc-progress"><span style="width: {{ $kpi['percent'] }}%"></span></div>
                @endif
                <p @class(['mc-kpi__trend', 'is-up' => $kpi['trendUp'], 'is-down' => ! $kpi['trendUp']])>{{ $kpi['trend'] }}</p>
            </a>
        @endforeach
    </section>

    <section class="mc-card mc-card--board">
        <div class="mc-card__head mc-card__head--table">
            <div>
                <h2>Bed board</h2>
                <p>Admit → discharge → clean → ready</p>
            </div>
            <div class="mc-toolbar">
                <a href="{{ route('admin.beds.events') }}" class="mc-btn mc-btn--ghost">History</a>
                <form method="GET" action="{{ route('admin.beds.index') }}" class="mc-toolbar__filters">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search bed or patient" class="mc-input">
                    <select name="status" class="mc-input mc-input--status" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Bed::STATUSES as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                        <option value="turnaround" @selected($status === 'turnaround')>Dirty + cleaning</option>
                    </select>
                    @if ($type !== '')
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    <button type="submit" class="mc-btn mc-btn--dark">Filter</button>
                    @if ($search !== '' || $status !== '' || $type !== '')
                        <a href="{{ route('admin.beds.index') }}" class="mc-link-btn">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="mc-board-errors" role="alert">
                <strong>Could not update bed</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mc-board-filters">
            <div class="mc-tabs" role="tablist" aria-label="Ward type">
                <a href="{{ route('admin.beds.index', array_filter(['status' => $status ?: null])) }}" @class(['is-active' => $type === ''])>All · {{ $typeCounts['all'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => 'icu', 'status' => $status ?: null])) }}" @class(['is-active' => $type === 'icu'])>ICU · {{ $typeCounts['icu'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => 'general', 'status' => $status ?: null])) }}" @class(['is-active' => $type === 'general'])>General · {{ $typeCounts['general'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => 'nicu', 'status' => $status ?: null])) }}" @class(['is-active' => $type === 'nicu'])>NICU · {{ $typeCounts['nicu'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => 'isolation', 'status' => $status ?: null])) }}" @class(['is-active' => $type === 'isolation'])>Isolation · {{ $typeCounts['isolation'] }}</a>
            </div>
            <div class="mc-status-chips" aria-label="Bed status">
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null])) }}" @class(['is-active' => $status === ''])>All</a>
                <a href="{{ route('admin.beds.index', ['type' => 'icu', 'status' => 'occupied']) }}" @class(['is-active' => $type === 'icu' && $status === 'occupied'])>ICU occupied · {{ $icuCensus->count() }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'available'])) }}" @class(['is-active' => $status === 'available'])>Available · {{ $statusCounts['available'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'occupied'])) }}" @class(['is-active' => $status === 'occupied' && $type !== 'icu'])>Occupied · {{ $statusCounts['occupied'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'dirty'])) }}" @class(['is-active' => $status === 'dirty'])>Dirty · {{ $statusCounts['dirty'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'cleaning'])) }}" @class(['is-active' => $status === 'cleaning'])>Cleaning · {{ $statusCounts['cleaning'] }}</a>
            </div>
        </div>

        <section class="mc-census" aria-label="ICU census">
            <div class="mc-census__head">
                <div>
                    <h3>ICU census</h3>
                    <p>{{ $icuCensus->count() }} occupied · who is in and why</p>
                </div>
                <a href="{{ route('admin.beds.index', ['type' => 'icu', 'status' => 'occupied']) }}" class="mc-link-btn">Open ICU occupied</a>
            </div>
            @if ($icuCensus->isEmpty())
                <p class="mc-census__empty">No ICU beds are occupied right now.</p>
            @else
                <ul class="mc-census__list">
                    @foreach ($icuCensus as $icuBed)
                        <li>
                            <a href="{{ route('admin.beds.index', ['type' => 'icu', 'status' => 'occupied']) }}#bed-{{ $icuBed->id }}">
                                <span class="mc-census__bed">{{ $icuBed->bed_code }}</span>
                                <span class="mc-census__who">{{ $icuBed->patient?->full_name }}</span>
                                <span class="mc-census__why">{{ $icuBed->admission_reason ?: 'Reason not recorded' }}</span>
                                <span class="mc-census__stay">{{ $icuBed->lengthOfStayLabel() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="mc-board-scroll">
            <table class="mc-beds">
                <thead>
                    <tr>
                        <th scope="col">Bed</th>
                        <th scope="col">Patient</th>
                        <th scope="col">Status</th>
                        <th scope="col">Ward</th>
                        <th scope="col" class="mc-beds__action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beds as $bed)
                        <tr
                            id="bed-{{ $bed->id }}"
                            @class([
                                'mc-beds__row',
                                'mc-beds__row--'.$bed->status,
                                'is-highlight' => $search !== '' && str_contains(strtolower($bed->bed_code), strtolower($search)),
                            ])
                        >
                            <td>
                                <div class="mc-beds__bed">
                                    <span class="mc-beds__code">{{ $bed->bed_code }}</span>
                                    @if ($bed->operationalLabel())
                                        <span class="mc-flag mc-flag--{{ strtolower($bed->operationalLabel()) }}">{{ $bed->operationalLabel() }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($bed->patient)
                                    <div class="mc-beds__person">
                                        @can('patients.view')
                                            <a href="{{ route('admin.patients.show', $bed->patient) }}" class="mc-beds__name mc-beds__name--link">{{ $bed->patient->full_name }}</a>
                                        @else
                                            <span class="mc-beds__name">{{ $bed->patient->full_name }}</span>
                                        @endcan
                                        <span class="mc-beds__meta">Stay {{ $bed->lengthOfStayLabel() }}@if ($bed->patient->phone) · {{ $bed->patient->phone }}@endif</span>
                                        @if ($bed->admission_reason)
                                            <span class="mc-beds__why">{{ $bed->admission_reason }}</span>
                                        @endif
                                        @if ($bed->patient->careFlagLabels() !== [])
                                            <span class="mc-beds__flags">
                                                @foreach ($bed->patient->careFlagLabels() as $flag)
                                                    <em>{{ $flag }}</em>
                                                @endforeach
                                            </span>
                                        @endif
                                    </div>
                                @elseif ($bed->hasActiveReservation())
                                    <div class="mc-beds__person">
                                        <span class="mc-beds__name">{{ $bed->reservedPatient?->full_name }}</span>
                                        <span class="mc-beds__meta">Hold{{ $bed->reserved_until ? ' until '.$bed->reserved_until->format('M j, H:i') : '' }}</span>
                                    </div>
                                @elseif ($bed->is_maintenance)
                                    <span class="mc-beds__meta">{{ $bed->maintenance_reason ?: 'Out of service' }}</span>
                                @else
                                    <span class="mc-beds__empty">Empty</span>
                                @endif
                            </td>
                            <td>
                                <div class="mc-beds__status">
                                    <span class="mc-status mc-status--{{ $bed->status }}">{{ ucfirst($bed->status) }}</span>
                                    <span class="mc-beds__age">{{ $bed->statusAgeLabel() }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="mc-beds__ward">
                                    <span class="mc-beds__name">{{ $bed->ward?->name }}</span>
                                    <span class="mc-beds__meta">{{ $bed->ward?->typeLabel() }}</span>
                                </div>
                            </td>
                            <td class="mc-beds__action">
                                <div class="mc-ops">
                                    @if ($adminCanOperateBeds && $bed->status === 'available' && ! $bed->is_maintenance)
                                        @php
                                            $eligiblePatients = $admitPatients->when(
                                                $bed->hasActiveReservation(),
                                                fn ($patients) => $patients->where('id', $bed->reserved_patient_id)->values(),
                                                fn ($patients) => $patients
                                            );
                                        @endphp

                                        <div class="mc-ops-group">
                                            <div class="mc-ops-toolbar">
                                                @if ($eligiblePatients->isEmpty())
                                                    <span class="mc-ops-note">No free patients</span>
                                                    @can('patients.manage')
                                                        <a href="{{ route('admin.patients.create', ['return_to' => 'beds']) }}" class="mc-btn">New patient</a>
                                                    @endcan
                                                @else
                                                    <form method="POST" action="{{ route('admin.beds.update', $bed) }}" class="mc-alloc">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <input type="hidden" name="status" value="occupied">
                                                        <select name="patient_id" required class="mc-input mc-input--compact" aria-label="Patient for {{ $bed->bed_code }}">
                                                            <option value="">Select patient</option>
                                                            @foreach ($eligiblePatients as $patient)
                                                                <option
                                                                    value="{{ $patient->id }}"
                                                                    @selected($bed->reserved_patient_id === $patient->id)
                                                                >{{ $patient->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input
                                                            list="admit-reasons"
                                                            name="admission_reason"
                                                            required
                                                            class="mc-input mc-input--compact"
                                                            placeholder="Why occupied"
                                                            aria-label="Admission reason for {{ $bed->bed_code }}"
                                                        >
                                                        <button type="submit" class="mc-btn">Admit</button>
                                                    </form>
                                                    @can('patients.manage')
                                                        <a href="{{ route('admin.patients.create', ['return_to' => 'beds']) }}" class="mc-ops-chip">New patient</a>
                                                    @endcan
                                                @endif

                                                @if ($bed->hasActiveReservation())
                                                    <form method="POST" action="{{ route('admin.beds.reservation.cancel', $bed) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <button type="submit" class="mc-ops-chip mc-ops-chip--danger">Cancel hold</button>
                                                    </form>
                                                @else
                                                    @if ($admitPatients->isNotEmpty())
                                                        <button type="button" class="mc-ops-chip" data-mc-reveal="reserve-{{ $bed->id }}" aria-expanded="false">Reserve</button>
                                                    @endif
                                                    @if ($adminCanMaintainBeds)
                                                        <button type="button" class="mc-ops-chip" data-mc-reveal="maintain-{{ $bed->id }}" aria-expanded="false">Maintenance</button>
                                                    @endif
                                                @endif
                                            </div>

                                            @unless ($bed->hasActiveReservation())
                                                @if ($admitPatients->isNotEmpty())
                                                    <div id="reserve-{{ $bed->id }}" class="mc-reveal" hidden>
                                                        <form method="POST" action="{{ route('admin.beds.reserve', $bed) }}" class="mc-alloc">
                                                            @csrf
                                                            <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                            <select name="patient_id" required class="mc-input mc-input--compact" aria-label="Reserve {{ $bed->bed_code }} for patient">
                                                                <option value="">Reserve for</option>
                                                                @foreach ($admitPatients as $patient)
                                                                    <option value="{{ $patient->id }}">{{ $patient->full_name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="mc-btn">Confirm</button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if ($adminCanMaintainBeds)
                                                    <div id="maintain-{{ $bed->id }}" class="mc-reveal" hidden>
                                                        <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}" class="mc-alloc">
                                                            @csrf
                                                            <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                            <input type="hidden" name="action" value="start">
                                                            <input type="text" name="reason" required class="mc-input mc-input--compact" placeholder="Reason" aria-label="Maintenance reason for {{ $bed->bed_code }}">
                                                            <button type="submit" class="mc-btn">Start</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endunless
                                        </div>
                                    @endif

                                    @if ($adminCanMaintainBeds && $bed->status === 'available' && $bed->is_maintenance)
                                        <div class="mc-ops-group">
                                            <div class="mc-ops-toolbar">
                                                <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="action" value="end">
                                                    <button type="submit" class="mc-btn">End maintenance</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'occupied')
                                        <div class="mc-ops-group">
                                            <div class="mc-ops-toolbar">
                                                @if ($bed->currentAdmission && ! $bed->currentAdmission->isAuthorizedForRelease())
                                                    @can('admissions.view')
                                                        <a href="{{ route('admin.admissions.show', $bed->currentAdmission) }}" class="mc-btn">Open admission</a>
                                                    @else
                                                        <span class="mc-ops-chip">Needs discharge authorization</span>
                                                    @endcan
                                                @else
                                                    <form method="POST" action="{{ route('admin.beds.update', $bed) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <input type="hidden" name="status" value="dirty">
                                                        <button type="submit" class="mc-btn">Discharge</button>
                                                    </form>
                                                @endif
                                                @if ($transferTargets->where('id', '!=', $bed->id)->isNotEmpty())
                                                    <button type="button" class="mc-ops-chip" data-mc-reveal="transfer-{{ $bed->id }}" aria-expanded="false">Transfer</button>
                                                @endif
                                            </div>
                                            @if ($transferTargets->where('id', '!=', $bed->id)->isNotEmpty())
                                                <div id="transfer-{{ $bed->id }}" class="mc-reveal" hidden>
                                                    <form method="POST" action="{{ route('admin.beds.transfer', $bed) }}" class="mc-alloc">
                                                        @csrf
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <input type="hidden" name="to_lock_version" value="">
                                                        <select
                                                            name="to_bed_id"
                                                            required
                                                            class="mc-input mc-input--compact"
                                                            aria-label="Transfer {{ $bed->bed_code }} to bed"
                                                            onchange="this.form.to_lock_version.value=this.selectedOptions[0]?.dataset.lockVersion||''"
                                                        >
                                                            <option value="">Choose bed</option>
                                                            @foreach ($transferTargets->where('id', '!=', $bed->id) as $target)
                                                                <option value="{{ $target->id }}" data-lock-version="{{ $target->lock_version }}">{{ $target->bed_code }} · {{ $target->ward?->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" name="reason" required class="mc-input mc-input--compact" placeholder="Transfer reason">
                                                        <button type="submit" class="mc-btn">Confirm</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'dirty')
                                        <div class="mc-ops-group">
                                            <div class="mc-ops-toolbar">
                                                <form method="POST" action="{{ route('admin.beds.update', $bed) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="status" value="cleaning">
                                                    <button type="submit" class="mc-btn">Start cleaning</button>
                                                </form>
                                                @if ($adminCanMaintainBeds && ! $bed->is_maintenance)
                                                    <button type="button" class="mc-ops-chip" data-mc-reveal="maintain-{{ $bed->id }}" aria-expanded="false">Maintenance</button>
                                                @endif
                                            </div>
                                            @if ($adminCanMaintainBeds && ! $bed->is_maintenance)
                                                <div id="maintain-{{ $bed->id }}" class="mc-reveal" hidden>
                                                    <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}" class="mc-alloc">
                                                        @csrf
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <input type="hidden" name="action" value="start">
                                                        <input type="text" name="reason" required class="mc-input mc-input--compact" placeholder="Reason" aria-label="Maintenance reason for {{ $bed->bed_code }}">
                                                        <button type="submit" class="mc-btn">Start</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'cleaning')
                                        <div class="mc-ops-group">
                                            <div class="mc-ops-toolbar">
                                                <form method="POST" action="{{ route('admin.beds.inspect', $bed) }}">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="result" value="pass">
                                                    <button type="submit" class="mc-btn">Pass inspection</button>
                                                </form>
                                                <button type="button" class="mc-ops-chip mc-ops-chip--danger" data-mc-reveal="fail-{{ $bed->id }}" aria-expanded="false">Fail</button>
                                            </div>
                                            <div id="fail-{{ $bed->id }}" class="mc-reveal" hidden>
                                                <form method="POST" action="{{ route('admin.beds.inspect', $bed) }}" class="mc-alloc">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="result" value="fail">
                                                    <input type="text" name="notes" required class="mc-input mc-input--compact" placeholder="Fail reason" aria-label="Fail reason for {{ $bed->bed_code }}">
                                                    <button type="submit" class="mc-btn">Confirm</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    @unless ($adminCanOperateBeds || $adminCanMaintainBeds)
                                        <span class="mc-cell-sub">View only</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="mc-beds__empty-row">
                            <td colspan="5">No beds match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mc-pager">{{ $beds->links() }}</div>
    </section>

    <datalist id="admit-reasons">
        <option value="Respiratory support"></option>
        <option value="Cardiac monitoring"></option>
        <option value="Post-operative recovery"></option>
        <option value="Sepsis / shock"></option>
        <option value="Neuro observation"></option>
        <option value="Isolation precautions"></option>
    </datalist>
@endsection
