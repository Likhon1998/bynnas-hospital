@extends('layouts.admin')

@section('title', 'Critical Alerts')
@section('heading', 'Critical Alerts')

@section('content')
    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--orange">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Bed turnaround</p>
                    <p class="mc-kpi__value">{{ $counts['beds'] }}</p>
                    <p class="mc-kpi__meta">Dirty or cleaning</p>
                </div>
            </div>
        </article>
        <article class="mc-kpi mc-kpi--blue">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Pending visits</p>
                    <p class="mc-kpi__value">{{ $counts['pending'] }}</p>
                    <p class="mc-kpi__meta">Need confirmation</p>
                </div>
            </div>
        </article>
        <article class="mc-kpi mc-kpi--navy">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Unpaid bills</p>
                    <p class="mc-kpi__value">{{ $counts['unpaid'] }}</p>
                    <p class="mc-kpi__meta">Billing follow-up</p>
                </div>
            </div>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Unread mail</p>
                    <p class="mc-kpi__value">{{ $counts['messages'] }}</p>
                    <p class="mc-kpi__meta">Website inbox</p>
                </div>
            </div>
        </article>
    </section>

    <section class="mc-card mc-card--follow">
        <div class="mc-card__head">
            <div>
                <h2>Active alerts</h2>
                <p>What needs staff attention now</p>
            </div>
        </div>
        <div class="mc-alerts">
            @forelse ($alerts as $alert)
                <a href="{{ $alert['href'] }}" class="mc-alert mc-alert--{{ $alert['tone'] }}">
                    <strong>{{ $alert['title'] }}</strong>
                    <span>{{ $alert['detail'] }}</span>
                    <small>{{ $alert['time'] }}</small>
                </a>
            @empty
                <p class="mc-empty">No critical alerts right now.</p>
            @endforelse
        </div>
    </section>
@endsection
