@php
    $isNew = ! $bill->exists;
    $itemRows = old('items', $bill->relationLoaded('billItems') || $bill->exists
        ? $bill->billItems->map(fn ($item) => [
            'item_type' => $item->item_type,
            'item_id' => $item->item_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->all()
        : []
    );
@endphp

<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.bills.index')"
    submit="Issue bill"
    submit-name="action"
    submit-value="issue"
    lede="1) Choose the patient. 2) Add charges from the catalog or a custom line. 3) Issue and collect payment."
    class="bill-form"
    data-bill-form
>
    <x-slot:actions>
        <button type="submit" name="action" value="draft" class="form-btn form-btn--navy">Save draft</button>
    </x-slot:actions>

    <x-admin.form-section title="1. Patient" note="Who is this bill for? Link today’s appointment if there is one — consultation fee fills in automatically." tone="teal" step="1">
        <x-form.select name="patient_id" label="Patient" required data-bill-patient>
            <option value="">Select patient</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((string) old('patient_id', $bill->patient_id) === (string) $patient->id)>
                    {{ $patient->full_name }} · {{ $patient->phone }}
                </option>
            @endforeach
        </x-form.select>
        <x-form.select name="appointment_id" label="Linked appointment (optional)" data-bill-appointment>
            <option value="">No linked appointment</option>
            @foreach ($appointments as $appointment)
                <option
                    value="{{ $appointment->id }}"
                    data-patient-id="{{ $appointment->patient_id }}"
                    data-consultation-fee="{{ $appointment->doctor?->consultation_fee ?? 0 }}"
                    data-doctor-id="{{ $appointment->doctor_id }}"
                    data-doctor-name="{{ $appointment->doctor?->name }}"
                    @selected((string) old('appointment_id', $bill->appointment_id) === (string) $appointment->id)
                >
                    {{ $appointment->appointment_no }}
                    · {{ $appointment->appointment_date?->format('d M Y') }}
                    {{ substr((string) $appointment->start_time, 0, 5) }}
                    · {{ $appointment->doctor?->name ?: 'Doctor' }}
                </option>
            @endforeach
        </x-form.select>
        <x-form.input name="bill_date" label="Bill date" type="date" :value="old('bill_date', optional($bill->bill_date)->toDateString() ?? now()->toDateString())" required />
    </x-admin.form-section>

    <x-admin.form-section title="2. Charges" note="Add priced lines. Empty zero rows are not allowed — use the catalog or Add custom charge." :columns="false" step="2">
        <div class="bill-form__toolbar">
            <div class="bill-form__catalog">
                <label class="text-sm font-medium text-navy-900" for="bill-catalog">Add from catalog</label>
                <select id="bill-catalog" data-bill-catalog class="admin-field mt-2 w-full px-3 py-2 text-sm">
                    <option value="">Choose a charge…</option>
                    <optgroup label="Consultation">
                        @foreach ($doctors as $doctor)
                            <option
                                value="consultation:{{ $doctor->id }}"
                                data-type="consultation"
                                data-id="{{ $doctor->id }}"
                                data-description="Consultation — {{ $doctor->name }}"
                                data-price="{{ $doctor->consultation_fee ?? 0 }}"
                            >{{ $doctor->name }} · ৳{{ number_format((float) ($doctor->consultation_fee ?? 0), 2) }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Health packages">
                        @foreach ($packages as $package)
                            <option
                                value="package:{{ $package->id }}"
                                data-type="package"
                                data-id="{{ $package->id }}"
                                data-description="{{ $package->title }}"
                                data-price="{{ $package->price }}"
                            >{{ $package->title }} · ৳{{ number_format((float) $package->price, 2) }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Services (set price after adding)">
                        @foreach ($services as $service)
                            <option
                                value="service:{{ $service->id }}"
                                data-type="service"
                                data-id="{{ $service->id }}"
                                data-description="{{ $service->title }}"
                                data-price="0"
                            >{{ $service->title }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="bill-form__quick">
                <button type="button" class="form-btn form-btn--navy" data-bill-add>Add custom charge</button>
                <button type="button" class="form-btn form-btn--ghost" data-bill-add-bed>Add bed / ward charge</button>
            </div>
        </div>

        @error('items') <p class="mb-3 text-sm text-red-700">{{ $message }}</p> @enderror
        @error('items.*') <p class="mb-3 text-sm text-red-700">{{ $message }}</p> @enderror

        <div class="table-frame table-frame--fit bill-form__table">
            <table class="pro-table pro-table--form">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="col-num">Qty</th>
                        <th class="col-num">Unit price (৳)</th>
                        <th class="col-num">Line total</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody data-bill-rows>
                    @forelse ($itemRows as $index => $item)
                        @php $isLab = ($item['item_type'] ?? '') === 'lab'; @endphp
                        <tr data-bill-row @if ($isLab) data-bill-locked @endif>
                            <td>
                                <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item['item_type'] ?? 'custom' }}">
                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item['item_id'] ?? '' }}">
                                <input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required class="admin-field w-full min-w-56 px-3 py-2 text-sm" placeholder="Charge description" @if ($isLab) readonly @endif>
                            </td>
                            <td class="col-num">
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" data-bill-qty class="admin-field w-20 px-3 py-2 text-right text-sm" @if ($isLab) readonly @endif>
                            </td>
                            <td class="col-num">
                                <input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" data-bill-price class="admin-field w-28 px-3 py-2 text-right text-sm" @if ($isLab) readonly @endif>
                            </td>
                            <td class="col-num" data-bill-line>0.00</td>
                            <td class="col-action">
                                @unless ($isLab)
                                    <button type="button" class="table-btn table-btn--danger" data-bill-remove>Remove</button>
                                @else
                                    <span class="text-xs font-semibold text-ink-500">Lab locked</span>
                                @endunless
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
            <p class="bill-form__empty @if (count($itemRows) > 0) is-hidden @endif" data-bill-empty>
                No charges yet. Pick a consultation/package from the catalog, or add a custom charge.
            </p>
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="3. Settle" note="Discount needs Director approval when it is ৳500+ or 10%+ of the bill." step="3">
        <x-form.input name="discount" label="Discount (৳)" type="number" step="0.01" min="0" :value="old('discount', $bill->discount ?? 0)" data-bill-discount="true" />
        <div class="form-span-2">
            <x-form.textarea name="notes" label="Internal notes (optional)" :value="old('notes', $bill->notes)" rows="3" />
        </div>
        <div class="form-span-2">
            <dl class="bill-form__totals">
                <div><dt>Subtotal</dt><dd data-bill-subtotal>0.00</dd></div>
                <div><dt>Discount</dt><dd data-bill-discount-display>0.00</dd></div>
                <div class="bill-form__totals-total"><dt>Total due</dt><dd data-bill-total>0.00</dd></div>
            </dl>
            <p class="bill-form__hint" data-bill-hint>Add at least one charge with a price before issuing.</p>
        </div>
    </x-admin.form-section>
</x-admin.form-page>

<template id="bill-row-template">
    <tr data-bill-row>
        <td>
            <input type="hidden" name="items[__INDEX__][item_type]" value="custom">
            <input type="hidden" name="items[__INDEX__][item_id]" value="">
            <input name="items[__INDEX__][description]" required class="admin-field w-full min-w-56 px-3 py-2 text-sm" placeholder="Charge description">
        </td>
        <td class="col-num">
            <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" data-bill-qty class="admin-field w-20 px-3 py-2 text-right text-sm">
        </td>
        <td class="col-num">
            <input type="number" min="0" step="0.01" name="items[__INDEX__][unit_price]" value="0" data-bill-price class="admin-field w-28 px-3 py-2 text-right text-sm">
        </td>
        <td class="col-num" data-bill-line>0.00</td>
        <td class="col-action">
            <button type="button" class="table-btn table-btn--danger" data-bill-remove>Remove</button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-bill-form]');
    if (!form) return;

    const rows = form.querySelector('[data-bill-rows]');
    const empty = form.querySelector('[data-bill-empty]');
    const template = document.getElementById('bill-row-template');
    const patientSelect = form.querySelector('[data-bill-patient]');
    const appointmentSelect = form.querySelector('[data-bill-appointment]');
    const catalog = form.querySelector('[data-bill-catalog]');
    const discountInput = form.querySelector('[name="discount"]');
    const hint = form.querySelector('[data-bill-hint]');
    const issueBtn = form.querySelector('button[name="action"][value="issue"]');

    const money = (value) => Number(value || 0).toFixed(2);

    const syncEmpty = () => {
        const hasRows = rows.querySelectorAll('[data-bill-row]').length > 0;
        empty?.classList.toggle('is-hidden', hasRows);
    };

    const reindex = () => {
        [...rows.querySelectorAll('[data-bill-row]')].forEach((row, index) => {
            row.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    };

    const totals = () => {
        let subtotal = 0;
        rows.querySelectorAll('[data-bill-row]').forEach((row) => {
            const qty = Number(row.querySelector('[data-bill-qty]')?.value || 0);
            const price = Number(row.querySelector('[data-bill-price]')?.value || 0);
            const line = qty * price;
            subtotal += line;
            const lineCell = row.querySelector('[data-bill-line]');
            if (lineCell) lineCell.textContent = money(line);
        });
        const discount = Math.min(Number(discountInput?.value || 0), subtotal);
        const total = Math.max(0, subtotal - discount);
        form.querySelector('[data-bill-subtotal]').textContent = money(subtotal);
        form.querySelector('[data-bill-discount-display]').textContent = money(discount);
        form.querySelector('[data-bill-total]').textContent = money(total);

        const ready = rows.querySelectorAll('[data-bill-row]').length > 0 && total > 0;
        if (hint) {
            hint.textContent = ready
                ? 'Ready to issue. Patient pays the total due, or save as draft first.'
                : 'Add at least one charge with a price before issuing.';
            hint.classList.toggle('is-ready', ready);
        }
        if (issueBtn) {
            issueBtn.toggleAttribute('disabled', !ready);
        }
    };

    const addRow = (values = {}) => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(rows.children.length));
        rows.insertAdjacentHTML('beforeend', html);
        const row = rows.lastElementChild;
        if (values.item_type) row.querySelector('[name*="[item_type]"]').value = values.item_type;
        if (values.item_id !== undefined) row.querySelector('[name*="[item_id]"]').value = values.item_id;
        if (values.description) row.querySelector('[name*="[description]"]').value = values.description;
        if (values.quantity) row.querySelector('[data-bill-qty]').value = values.quantity;
        if (values.unit_price !== undefined) row.querySelector('[data-bill-price]').value = values.unit_price;
        reindex();
        syncEmpty();
        totals();
        row.querySelector('[name*="[description]"]')?.focus();
        return row;
    };

    const ensureConsultationFromAppointment = () => {
        const option = appointmentSelect.selectedOptions[0];
        if (!option?.value) return;

        const fee = Number(option.dataset.consultationFee || 0);
        const doctorId = option.dataset.doctorId || '';
        const doctorName = option.dataset.doctorName || 'Doctor';
        const description = `Consultation — ${doctorName}`;

        const existing = [...rows.querySelectorAll('[data-bill-row]')].find((row) => {
            return row.querySelector('[name*="[item_type]"]')?.value === 'consultation'
                && String(row.querySelector('[name*="[item_id]"]')?.value || '') === String(doctorId);
        });

        if (existing) {
            existing.querySelector('[name*="[description]"]').value = description;
            existing.querySelector('[data-bill-price]').value = fee;
            totals();
            return;
        }

        addRow({
            item_type: 'consultation',
            item_id: doctorId,
            description,
            quantity: 1,
            unit_price: fee,
        });
    };

    const filterAppointments = () => {
        const patientId = patientSelect.value;
        [...appointmentSelect.options].forEach((option) => {
            if (!option.value) return;
            option.hidden = Boolean(patientId) && option.dataset.patientId !== patientId;
        });
        const selected = appointmentSelect.selectedOptions[0];
        if (selected && selected.hidden) appointmentSelect.value = '';
    };

    form.querySelector('[data-bill-add]')?.addEventListener('click', () => addRow({
        item_type: 'custom',
        description: '',
        quantity: 1,
        unit_price: 0,
    }));

    form.querySelector('[data-bill-add-bed]')?.addEventListener('click', () => addRow({
        item_type: 'bed',
        description: 'Bed / ward charge',
        quantity: 1,
        unit_price: 0,
    }));

    catalog?.addEventListener('change', () => {
        const option = catalog.selectedOptions[0];
        if (!option?.dataset.type) return;
        addRow({
            item_type: option.dataset.type,
            item_id: option.dataset.id,
            description: option.dataset.description,
            quantity: 1,
            unit_price: option.dataset.price,
        });
        catalog.value = '';
    });

    rows.addEventListener('click', (event) => {
        if (!event.target.closest('[data-bill-remove]')) return;
        const row = event.target.closest('[data-bill-row]');
        if (row?.hasAttribute('data-bill-locked')) return;
        row?.remove();
        reindex();
        syncEmpty();
        totals();
    });

    form.addEventListener('input', totals);
    patientSelect?.addEventListener('change', filterAppointments);
    appointmentSelect?.addEventListener('change', () => {
        const option = appointmentSelect.selectedOptions[0];
        if (!option?.value) return;
        if (option.dataset.patientId) patientSelect.value = option.dataset.patientId;
        filterAppointments();
        ensureConsultationFromAppointment();
    });

    form.addEventListener('submit', (event) => {
        const action = event.submitter?.value || 'draft';
        const usableRows = [...rows.querySelectorAll('[data-bill-row]')].filter((row) => {
            const description = row.querySelector('[name*="[description]"]')?.value?.trim();
            return Boolean(description);
        });

        if (usableRows.length === 0) {
            event.preventDefault();
            hint.textContent = 'Add at least one charge before saving.';
            hint.classList.remove('is-ready');
            return;
        }

        // Drop blank leftover rows so validation stays clean.
        [...rows.querySelectorAll('[data-bill-row]')].forEach((row) => {
            const description = row.querySelector('[name*="[description]"]')?.value?.trim();
            if (!description && !row.hasAttribute('data-bill-locked')) {
                row.remove();
            }
        });
        reindex();

        const total = Number(form.querySelector('[data-bill-total]')?.textContent || 0);
        if (action === 'issue' && total <= 0) {
            event.preventDefault();
            hint.textContent = 'Set a unit price greater than zero before issuing the bill.';
            hint.classList.remove('is-ready');
        }
    });

    filterAppointments();
    syncEmpty();
    totals();

    @if ($isNew && old('appointment_id', $bill->appointment_id))
        ensureConsultationFromAppointment();
    @endif
});
</script>
