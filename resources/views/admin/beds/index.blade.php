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
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'available'])) }}" @class(['is-active' => $status === 'available'])>Available · {{ $statusCounts['available'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'occupied'])) }}" @class(['is-active' => $status === 'occupied'])>Occupied · {{ $statusCounts['occupied'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'dirty'])) }}" @class(['is-active' => $status === 'dirty'])>Dirty · {{ $statusCounts['dirty'] }}</a>
                <a href="{{ route('admin.beds.index', array_filter(['type' => $type ?: null, 'status' => 'cleaning'])) }}" @class(['is-active' => $status === 'cleaning'])>Cleaning · {{ $statusCounts['cleaning'] }}</a>
            </div>
        </div>

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
                                        <span class="mc-beds__name">{{ $bed->patient->full_name }}</span>
                                        <span class="mc-beds__meta">Stay {{ $bed->lengthOfStayLabel() }}@if ($bed->patient->phone) · {{ $bed->patient->phone }}@endif</span>
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
                                        @if ($admitPatients->isEmpty() && ! $bed->hasActiveReservation())
                                            <div class="mc-ops-primary">
                                                <span class="mc-beds__meta">No free patients</span>
                                                @can('patients.manage')
                                                    <a href="{{ route('admin.patients.create', ['return_to' => 'beds']) }}" class="mc-link-btn">New patient</a>
                                                @endcan
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('admin.beds.update', $bed) }}" class="mc-bed-actions">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                <input type="hidden" name="status" value="occupied">
                                                <select name="patient_id" required class="mc-input mc-input--compact" aria-label="Patient for {{ $bed->bed_code }}">
                                                    <option value="">Select patient</option>
                                                    @foreach ($admitPatients as $patient)
                                                        @if (! $bed->hasActiveReservation() || $bed->reserved_patient_id === $patient->id)
                                                            <option value="{{ $patient->id }}">{{ $patient->full_name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="mc-btn mc-btn--primary">Admit</button>
                                            </form>
                                        @endif

                                        <div class="mc-ops-secondary">
                                            @if ($bed->hasActiveReservation())
                                                <form method="POST" action="{{ route('admin.beds.reservation.cancel', $bed) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <button type="submit" class="mc-link-btn">Cancel hold</button>
                                                </form>
                                            @else
                                                @can('patients.manage')
                                                    @if ($admitPatients->isNotEmpty() || $bed->hasActiveReservation())
                                                        <a href="{{ route('admin.patients.create', ['return_to' => 'beds']) }}" class="mc-link-btn">New patient</a>
                                                    @endif
                                                @endcan
                                                <button type="button" class="mc-link-btn" data-mc-reveal="reserve-{{ $bed->id }}" aria-expanded="false">Reserve</button>
                                                @if ($adminCanMaintainBeds)
                                                    <button type="button" class="mc-link-btn" data-mc-reveal="maintain-{{ $bed->id }}" aria-expanded="false">Maintenance</button>
                                                @endif
                                            @endif
                                        </div>

                                        @unless ($bed->hasActiveReservation())
                                            <div id="reserve-{{ $bed->id }}" class="mc-reveal" hidden>
                                                <form method="POST" action="{{ route('admin.beds.reserve', $bed) }}" class="mc-bed-actions">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <select name="patient_id" required class="mc-input mc-input--compact">
                                                        <option value="">Reserve for</option>
                                                        @foreach ($admitPatients as $patient)
                                                            <option value="{{ $patient->id }}">{{ $patient->full_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="mc-btn mc-btn--dark">Confirm</button>
                                                </form>
                                            </div>
                                            @if ($adminCanMaintainBeds)
                                                <div id="maintain-{{ $bed->id }}" class="mc-reveal" hidden>
                                                    <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}" class="mc-bed-actions">
                                                        @csrf
                                                        <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                        <input type="hidden" name="action" value="start">
                                                        <input type="text" name="reason" required class="mc-input mc-input--compact" placeholder="Reason">
                                                        <button type="submit" class="mc-btn mc-btn--dark">Start</button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endunless
                                    @endif

                                    @if ($adminCanMaintainBeds && $bed->status === 'available' && $bed->is_maintenance)
                                        <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}">
                                            @csrf
                                            <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                            <input type="hidden" name="action" value="end">
                                            <button type="submit" class="mc-btn mc-btn--primary">End maintenance</button>
                                        </form>
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'occupied')
                                        <div class="mc-ops-primary">
                                            <form method="POST" action="{{ route('admin.beds.update', $bed) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                <input type="hidden" name="status" value="dirty">
                                                <button type="submit" class="mc-btn mc-btn--primary">Discharge</button>
                                            </form>
                                            @if ($transferTargets->where('id', '!=', $bed->id)->isNotEmpty())
                                                <button type="button" class="mc-link-btn" data-mc-reveal="transfer-{{ $bed->id }}" aria-expanded="false">Transfer</button>
                                            @endif
                                        </div>
                                        @if ($transferTargets->where('id', '!=', $bed->id)->isNotEmpty())
                                            <div id="transfer-{{ $bed->id }}" class="mc-reveal" hidden>
                                                <form method="POST" action="{{ route('admin.beds.transfer', $bed) }}" class="mc-bed-actions">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="to_lock_version" value="">
                                                    <select
                                                        name="to_bed_id"
                                                        required
                                                        class="mc-input mc-input--compact"
                                                        onchange="this.form.to_lock_version.value=this.selectedOptions[0]?.dataset.lockVersion||''"
                                                    >
                                                        <option value="">Choose bed</option>
                                                        @foreach ($transferTargets->where('id', '!=', $bed->id) as $target)
                                                            <option value="{{ $target->id }}" data-lock-version="{{ $target->lock_version }}">{{ $target->bed_code }} · {{ $target->ward?->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="mc-btn mc-btn--dark">Confirm</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'dirty')
                                        <div class="mc-ops-primary">
                                            <form method="POST" action="{{ route('admin.beds.update', $bed) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                <input type="hidden" name="status" value="cleaning">
                                                <button type="submit" class="mc-btn mc-btn--primary">Start cleaning</button>
                                            </form>
                                            @if ($adminCanMaintainBeds && ! $bed->is_maintenance)
                                                <button type="button" class="mc-link-btn" data-mc-reveal="maintain-{{ $bed->id }}" aria-expanded="false">Maintenance</button>
                                            @endif
                                        </div>
                                        @if ($adminCanMaintainBeds && ! $bed->is_maintenance)
                                            <div id="maintain-{{ $bed->id }}" class="mc-reveal" hidden>
                                                <form method="POST" action="{{ route('admin.beds.maintain', $bed) }}" class="mc-bed-actions">
                                                    @csrf
                                                    <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                    <input type="hidden" name="action" value="start">
                                                    <input type="text" name="reason" required class="mc-input mc-input--compact" placeholder="Reason">
                                                    <button type="submit" class="mc-btn mc-btn--dark">Start</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($adminCanOperateBeds && $bed->status === 'cleaning')
                                        <div class="mc-ops-primary">
                                            <form method="POST" action="{{ route('admin.beds.inspect', $bed) }}">
                                                @csrf
                                                <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                <input type="hidden" name="result" value="pass">
                                                <button type="submit" class="mc-btn mc-btn--primary">Pass inspection</button>
                                            </form>
                                            <button type="button" class="mc-link-btn" data-mc-reveal="fail-{{ $bed->id }}" aria-expanded="false">Fail</button>
                                        </div>
                                        <div id="fail-{{ $bed->id }}" class="mc-reveal" hidden>
                                            <form method="POST" action="{{ route('admin.beds.inspect', $bed) }}" class="mc-bed-actions">
                                                @csrf
                                                <input type="hidden" name="lock_version" value="{{ $bed->lock_version }}">
                                                <input type="hidden" name="result" value="fail">
                                                <input type="text" name="notes" required class="mc-input mc-input--compact" placeholder="Fail reason">
                                                <button type="submit" class="mc-btn mc-btn--dark">Confirm</button>
                                            </form>
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
@endsection
