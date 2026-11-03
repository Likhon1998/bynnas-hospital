@extends('layouts.admin')
@section('title', 'Finance approvals')
@section('heading', 'Finance approvals')
@section('content')
    <div class="owner-approvals">
        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="clipboard-plus" class="h-4 w-4" />
                        <span>Request discount or refund</span>
                    </h2>
                    <p>Owner / Director approval required before settlement.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.owner.approvals.store') }}" class="form-grid owner-approvals__form">
                @csrf
                <div>
                    <label class="form-label" for="type">Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="discount" @selected(old('type') === 'discount')>Discount</option>
                        <option value="refund" @selected(old('type') === 'refund')>Refund</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="bill_id">Related bill (optional)</label>
                    <select id="bill_id" name="bill_id" class="form-control">
                        <option value="">—</option>
                        @foreach ($bills as $bill)
                            <option value="{{ $bill->id }}" @selected((string) old('bill_id') === (string) $bill->id)>
                                {{ $bill->bill_no }} · {{ $bill->patient?->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-form.input name="amount" label="Amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
                <x-form.input name="reason" label="Reason" :value="old('reason')" required />
                <x-form.input name="notes" label="Notes" :value="old('notes')" />
                <div class="owner-approvals__submit">
                    <button type="submit" class="admin-action">Submit request</button>
                </div>
            </form>
        </section>

        <section class="mc-card owner-approvals__queue">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                        <span>Approval queue</span>
                    </h2>
                    <p>{{ $approvals->total() }} {{ \Illuminate\Support\Str::plural('request', $approvals->total()) }}</p>
                </div>
            </div>

            @if ($approvals->isEmpty())
                <div class="owner-approvals__empty">
                    <p class="owner-approvals__empty-title">No pending approvals</p>
                    <p>Discount and refund requests will show here for Owner / Director review.</p>
                </div>
            @else
                <div class="table-frame table-frame--fit owner-approvals__table">
                    <div class="table-scroll admin-scroll">
                        <table class="pro-table pro-table--form">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th class="col-num">Amount</th>
                                    <th>Reason</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-open">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($approvals as $approval)
                                    <tr>
                                        <td>{{ ucfirst($approval->type) }}</td>
                                        <td class="col-num">৳{{ number_format((float) $approval->amount, 2) }}</td>
                                        <td>
                                            <span class="col-primary">{{ $approval->reason }}</span>
                                            @if ($approval->bill)
                                                <span class="cell-sub">{{ $approval->bill->bill_no }}</span>
                                            @endif
                                        </td>
                                        <td class="col-status">{{ ucfirst($approval->status) }}</td>
                                        <td class="col-open">
                                            @if ($approval->isPending() && auth()->user()?->can('finance.approve'))
                                                <div class="table-actions">
                                                    <form method="POST" action="{{ route('admin.owner.approvals.decide', $approval) }}" class="inline-flex gap-2">
                                                        @csrf
                                                        <button type="submit" name="decision" value="approved" class="table-btn table-btn--edit">Approve</button>
                                                        <button type="submit" name="decision" value="rejected" class="table-btn table-btn--danger">Reject</button>
                                                    </form>
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="table-pager">{{ $approvals->links() }}</div>
            @endif
        </section>
    </div>
@endsection
