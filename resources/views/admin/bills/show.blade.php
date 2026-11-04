@extends('layouts.admin')
@section('title', $bill->bill_no)
@section('heading', $bill->bill_no)
@section('content')
    <div class="bill-desk">
        <div class="bill-desk__main">
            <section class="form-card">
                <header class="form-card__head">
                    <div class="form-card__title">
                        <div>
                            <h2>Bill summary</h2>
                            <p>{{ $bill->patient?->full_name }} · {{ $bill->patient?->phone ?: 'No phone' }}</p>
                        </div>
                    </div>
                    <x-admin.status-pill :value="$bill->status" />
                </header>
                <div class="form-card__body">
                    <div class="bill-desk__meta">
                        <div>
                            <p class="bill-desk__label">Bill date</p>
                            <p class="bill-desk__value">{{ $bill->bill_date?->format('d M Y') }}</p>
                        </div>
                        @if ($bill->appointment)
                            <div>
                                <p class="bill-desk__label">Appointment</p>
                                <p class="bill-desk__value">{{ $bill->appointment->appointment_no }} · {{ $bill->appointment->doctor?->name }}</p>
                            </div>
                        @endif
                        @if ($bill->issued_at)
                            <div>
                                <p class="bill-desk__label">Issued</p>
                                <p class="bill-desk__value">{{ $bill->issued_at->format('d M Y H:i') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="table-frame bill-desk__table">
                        <table class="pro-table">
                            <thead>
                                <tr>
                                    <th class="col-idx">#</th>
                                    <th>Item</th>
                                    <th class="col-count">Qty</th>
                                    <th class="col-num">Unit</th>
                                    <th class="col-num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bill->billItems as $item)
                                    <tr>
                                        <td class="col-idx">{{ $loop->iteration }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="col-count">{{ $item->quantity }}</td>
                                        <td class="col-num">{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="col-num">{{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <dl class="bill-desk__totals">
                        <div><dt>Subtotal</dt><dd>{{ number_format((float) $bill->subtotal, 2) }}</dd></div>
                        <div><dt>Discount</dt><dd>{{ number_format((float) $bill->discount, 2) }}</dd></div>
                        <div class="is-strong"><dt>Total</dt><dd>{{ number_format((float) $bill->total, 2) }}</dd></div>
                        <div><dt>Paid</dt><dd>{{ number_format((float) $bill->paid_amount, 2) }}</dd></div>
                        <div class="is-strong"><dt>Due</dt><dd>{{ number_format($bill->balanceDue(), 2) }}</dd></div>
                    </dl>

                    @if ($bill->payments->isNotEmpty())
                        <div class="bill-desk__payments">
                            <p class="bill-desk__label">Payments</p>
                            <ul>
                                @foreach ($bill->payments as $payment)
                                    <li>
                                        <span>{{ $payment->methodLabel() }} · {{ $payment->collected_at?->format('d M Y H:i') }} · {{ $payment->collectedBy?->name }}</span>
                                        <strong>{{ number_format((float) $payment->amount, 2) }}</strong>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (filled($bill->report_access_code))
                        <div class="bill-desk__code">
                            <p class="bill-desk__label">Lab report access code</p>
                            <p class="bill-desk__code-value">{{ $bill->report_access_code }}</p>
                            <p class="bill-desk__hint">Printed on the money receipt. Patients use phone + this code on the website.</p>
                        </div>
                    @endif

                    @if ($bill->notes)
                        <div class="bill-desk__notes">
                            <p class="bill-desk__label">Notes</p>
                            <p>{{ $bill->notes }}</p>
                        </div>
                    @endif
                </div>
            </section>

            @if ($bill->canCollectPayment())
                <div class="mt-3">
                    <x-admin.form-page
                        :action="route('admin.bills.pay', $bill)"
                        method="POST"
                        :cancel="route('admin.bills.index')"
                        submit="Collect payment"
                    >
                        <x-admin.form-section title="Collect payment" note="Cash, Card, bKash, or Nagad. Partial payments keep the bill open until the due is cleared.">
                            <x-form.input name="amount" label="Amount" type="number" step="0.01" :value="old('amount', number_format($bill->balanceDue(), 2, '.', ''))" required />
                            <x-form.select name="method" label="Method" required>
                                <option value="">Select method</option>
                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.input name="reference" label="Txn / card reference" :value="old('reference')" hint="Required for Card, bKash, and Nagad" />
                            <div class="form-span-2">
                                <x-form.input name="notes" label="Collection notes" :value="old('notes')" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                </div>
            @endif

            @if ($bill->canRequestRefund())
                <div class="mt-3">
                    <x-admin.form-page
                        :action="route('admin.bills.refund-request', $bill)"
                        method="POST"
                        :cancel="route('admin.bills.show', $bill)"
                        submit="Submit refund request"
                    >
                        <x-admin.form-section title="Refund request" note="Cashiers create refund requests. Director approval is required before money is returned.">
                            <x-form.input name="amount" label="Refund amount" type="number" step="0.01" :value="old('amount', number_format((float) $bill->paid_amount, 2, '.', ''))" required />
                            <div class="form-span-2">
                                <x-form.input name="reason" label="Reason" :value="old('reason')" required />
                            </div>
                            <div class="form-span-2">
                                <x-form.textarea name="notes" label="Notes" :value="old('notes')" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                </div>
            @endif
        </div>

        <aside class="bill-desk__side" data-bill-actions>
            <p class="bill-desk__side-title">Actions</p>

            <a href="{{ route('admin.bills.print', $bill) }}" class="bill-desk__btn bill-desk__btn--primary">Print money receipt</a>
            <a href="{{ route('admin.bills.collections') }}" class="bill-desk__btn bill-desk__btn--ghost">Daily collections</a>

            @if ($bill->isEditable())
                <div class="bill-desk__side-group">
                    <a href="{{ route('admin.bills.edit', $bill) }}" class="bill-desk__btn bill-desk__btn--teal">Edit draft</a>
                    <form method="POST" action="{{ route('admin.bills.issue', $bill) }}">
                        @csrf
                        <button type="submit" class="bill-desk__btn bill-desk__btn--ghost">Issue bill</button>
                    </form>
                    <x-admin.delete-button :action="route('admin.bills.destroy', $bill)" label="Delete draft" class="bill-desk__btn bill-desk__btn--danger" />
                </div>
            @endif

            @if ($bill->canCancel())
                <form method="POST" action="{{ route('admin.bills.cancel', $bill) }}" class="bill-desk__cancel">
                    @csrf
                    <input type="text" name="cancellation_reason" placeholder="Cancellation reason" class="bill-desk__input">
                    <button type="submit" class="bill-desk__btn bill-desk__btn--danger">Cancel bill</button>
                </form>
            @endif

            <a href="{{ route('admin.bills.index') }}" class="bill-desk__back">Back to bills</a>
        </aside>
    </div>
@endsection
