@extends('layouts.admin')
@section('title', 'Finance approvals')
@section('heading', 'Finance approvals')
@section('content')
    <div class="mc-layout" style="margin-bottom: 1.25rem;">
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
            <form method="POST" action="{{ route('admin.owner.approvals.store') }}" class="form-grid" style="padding: 1rem 1.15rem 1.25rem;">
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
                <div>
                    <button type="submit" class="admin-action">Submit request</button>
                </div>
            </form>
        </section>

        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="clipboard-check" class="h-4 w-4" />
                        <span>Approval queue</span>
                    </h2>
                    <p>{{ $approvals->total() }} requests</p>
                </div>
            </div>
            <div class="table-frame">
                <div class="table-scroll admin-scroll">
                    <table class="pro-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="col-open">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($approvals as $approval)
                                <tr>
                                    <td>{{ ucfirst($approval->type) }}</td>
                                    <td>৳{{ number_format((float) $approval->amount, 2) }}</td>
                                    <td>
                                        <span class="col-primary">{{ $approval->reason }}</span>
                                        @if ($approval->bill)
                                            <span class="cell-sub">{{ $approval->bill->bill_no }}</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($approval->status) }}</td>
                                    <td class="col-open">
                                        @if ($approval->isPending() && auth()->user()?->can('finance.approve'))
                                            <form method="POST" action="{{ route('admin.owner.approvals.decide', $approval) }}" class="inline-flex gap-2">
                                                @csrf
                                                <button type="submit" name="decision" value="approved" class="admin-action">Approve</button>
                                                <button type="submit" name="decision" value="rejected" class="admin-action">Reject</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="is-empty"><td colspan="5">No approval requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-pager">{{ $approvals->links() }}</div>
        </section>
    </div>
@endsection
