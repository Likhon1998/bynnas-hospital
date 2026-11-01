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
