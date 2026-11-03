@extends('layouts.admin')
@section('title', 'Finance overview')
@section('heading', 'Finance overview')
@section('content')
    <div class="mc-kpis mc-desk__kpis" style="--kpi-count: 3; margin-bottom: 1.25rem;">
        <div class="mc-kpi mc-kpi--navy">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Month income</p>
                    <p class="mc-kpi__value">৳{{ number_format($owner['profitLoss']['income'], 2) }}</p>
                    <p class="mc-kpi__meta">Paid bills this month</p>
                </div>
            </div>
        </div>
        <div class="mc-kpi mc-kpi--navy">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Month expenses</p>
                    <p class="mc-kpi__value">৳{{ number_format($owner['profitLoss']['expenses'], 2) }}</p>
                    <p class="mc-kpi__meta">Recorded this month</p>
                </div>
            </div>
        </div>
        <div class="mc-kpi mc-kpi--navy">
            <div class="mc-kpi__top">
                <div>
                    <p class="mc-kpi__label">Profit / Loss</p>
                    <p class="mc-kpi__value">৳{{ number_format($owner['profitLoss']['profit'], 2) }}</p>
                    <p class="mc-kpi__meta">Income minus expenses</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mc-layout" style="margin-bottom: 1.25rem;">
        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="badge" class="h-4 w-4" />
                        <span>Outstanding dues</span>
                    </h2>
                    <p>Unpaid patient balances</p>
                </div>
                @can('billing.manage')
                    <a href="{{ route('admin.bills.index') }}">All bills</a>
                @endcan
            </div>
            <ul class="mc-activity">
                @forelse ($outstandingBills as $bill)
                    <li>
                        <a href="{{ route('admin.bills.show', $bill) }}" class="mc-activity__item">
                            <span class="mc-activity__dot is-amber"></span>
                            <span class="mc-activity__body">
                                <strong>{{ $bill->bill_no }}</strong>
                                <span>{{ $bill->patient?->full_name ?: 'Patient' }} · ৳{{ number_format((float) ($bill->total - $bill->paid_amount), 2) }}</span>
                                <em>{{ $bill->status }}</em>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="mc-activity__empty">No outstanding dues.</li>
                @endforelse
            </ul>
        </section>

        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="hospital" class="h-4 w-4" />
                        <span>Department revenue</span>
                    </h2>
                    <p>Paid bill revenue this month</p>
                </div>
            </div>
            <ul class="mc-activity">
                @forelse ($owner['departmentRevenue'] as $row)
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

    <div class="mc-layout">
        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="clipboard" class="h-4 w-4" />
                        <span>Record expense</span>
                    </h2>
                    <p>Track hospital operating costs</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.owner.finance.expenses.store') }}" class="form-grid" style="padding: 1rem 1.15rem 1.25rem;">
                @csrf
                <x-form.input name="expense_date" label="Date" type="date" :value="old('expense_date', now()->toDateString())" required />
                <x-form.input name="category" label="Category" :value="old('category')" required />
                <x-form.input name="description" label="Description" :value="old('description')" required />
                <x-form.input name="amount" label="Amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
                <div>
                    <button type="submit" class="admin-action">Save expense</button>
                </div>
            </form>
        </section>

        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="receipt" class="h-4 w-4" />
                        <span>Expense ledger</span>
                    </h2>
                    <p>{{ $expenses->total() }} recorded</p>
                </div>
            </div>
            <div class="table-frame">
                <div class="table-scroll admin-scroll">
                    <table class="pro-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date?->format('j M Y') }}</td>
                                    <td>{{ $expense->category }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td>৳{{ number_format((float) $expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr class="is-empty"><td colspan="4">No expenses recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-pager">{{ $expenses->links() }}</div>
        </section>
    </div>
@endsection
