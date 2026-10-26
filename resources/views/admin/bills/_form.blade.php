@php
    $itemRows = old('items', $bill->relationLoaded('billItems') || $bill->exists
        ? $bill->billItems->map(fn ($item) => [
            'item_type' => $item->item_type,
            'item_id' => $item->item_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->all()
        : [['item_type' => 'custom', 'item_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => '0.00']]
    );
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" data-bill-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
        <div>
            <label for="patient_id" class="text-sm font-medium text-navy-900">Patient</label>
            <select id="patient_id" name="patient_id" required data-bill-patient class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected((string) old('patient_id', $bill->patient_id) === (string) $patient->id)>{{ $patient->full_name }} · {{ $patient->phone }}</option>
                @endforeach
            </select>
            @error('patient_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="appointment_id" class="text-sm font-medium text-navy-900">Linked appointment (optional)</label>
            <select id="appointment_id" name="appointment_id" data-bill-appointment class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
                <option value="">None</option>
                @foreach ($appointments as $appointment)
                    <option
                        value="{{ $appointment->id }}"
                        data-patient-id="{{ $appointment->patient_id }}"
                        data-consultation-fee="{{ $appointment->doctor?->consultation_fee ?? 0 }}"
                        data-doctor-name="{{ $appointment->doctor?->name }}"
                        @selected((string) old('appointment_id', $bill->appointment_id) === (string) $appointment->id)
                    >{{ $appointment->appointment_no }} · {{ $appointment->appointment_date?->format('d M Y') }} {{ substr((string) $appointment->start_time, 0, 5) }}</option>
                @endforeach
            </select>
            @error('appointment_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <x-form.input name="bill_date" label="Bill date" type="date" :value="optional($bill->bill_date)->toDateString() ?? now()->toDateString()" required />
        <x-form.input name="discount" label="Discount" type="number" :value="$bill->discount ?? 0" data-bill-discount="true" />
        <x-form.textarea name="notes" label="Notes" :value="$bill->notes" />
    </section>

    <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-display text-xl text-navy-900">Bill items</h2>
            <div class="flex flex-wrap gap-2">
                <select data-bill-catalog class="rounded-full border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
                    <option value="">Add from catalog</option>
                    <optgroup label="Services">
                        @foreach ($services as $service)
                            <option value="service:{{ $service->id }}" data-type="service" data-id="{{ $service->id }}" data-description="{{ $service->title }}" data-price="0">{{ $service->title }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Packages">
                        @foreach ($packages as $package)
                            <option value="package:{{ $package->id }}" data-type="package" data-id="{{ $package->id }}" data-description="{{ $package->title }}" data-price="{{ $package->price }}">{{ $package->title }} · {{ number_format((float) $package->price, 2) }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Consultation">
                        @foreach ($doctors as $doctor)
                            <option value="consultation:{{ $doctor->id }}" data-type="consultation" data-id="{{ $doctor->id }}" data-description="Consultation — {{ $doctor->name }}" data-price="{{ $doctor->consultation_fee ?? 0 }}">{{ $doctor->name }} · {{ number_format((float) ($doctor->consultation_fee ?? 0), 2) }}</option>
                        @endforeach
                    </optgroup>
                </select>
                <button type="button" class="rounded-full bg-navy-900 px-4 py-2 text-sm font-semibold text-white" data-bill-add>Add custom item</button>
            </div>
        </div>
        @error('items') <p class="mt-3 text-sm text-red-700">{{ $message }}</p> @enderror

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-ink-400">
                    <tr>
                        <th class="py-2 pr-3">Description</th>
                        <th class="py-2 pr-3">Qty</th>
                        <th class="py-2 pr-3">Unit price</th>
                        <th class="py-2 pr-3">Line total</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody data-bill-rows>
                    @foreach ($itemRows as $index => $item)
                        <tr data-bill-row>
                            <td class="py-2 pr-3">
                                <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item['item_type'] ?? 'custom' }}">
                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item['item_id'] ?? '' }}">
                                <input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required class="w-full min-w-56 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" data-bill-qty class="w-20 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" data-bill-price class="w-28 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
                            </td>
                            <td class="py-2 pr-3 font-medium" data-bill-line>0.00</td>
                            <td class="py-2">
                                <button type="button" class="text-sm font-semibold text-red-700" data-bill-remove>Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <dl class="mt-6 flex flex-col gap-1 text-sm sm:items-end">
            <div class="flex gap-8"><dt class="text-ink-600">Subtotal</dt><dd data-bill-subtotal>0.00</dd></div>
            <div class="flex gap-8"><dt class="text-ink-600">Discount</dt><dd data-bill-discount-display>0.00</dd></div>
            <div class="flex gap-8 font-semibold text-navy-900"><dt>Total</dt><dd data-bill-total>0.00</dd></div>
        </dl>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" name="action" value="draft" class="rounded-full bg-navy-900 px-5 py-3 text-sm font-semibold text-white">Save draft</button>
        <button type="submit" name="action" value="issue" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Issue bill</button>
        <a href="{{ route('admin.bills.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>

<template id="bill-row-template">
    <tr data-bill-row>
        <td class="py-2 pr-3">
            <input type="hidden" name="items[__INDEX__][item_type]" value="custom">
            <input type="hidden" name="items[__INDEX__][item_id]" value="">
            <input name="items[__INDEX__][description]" required class="w-full min-w-56 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
        </td>
        <td class="py-2 pr-3">
            <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" data-bill-qty class="w-20 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
        </td>
        <td class="py-2 pr-3">
            <input type="number" min="0" step="0.01" name="items[__INDEX__][unit_price]" value="0" data-bill-price class="w-28 rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
        </td>
        <td class="py-2 pr-3 font-medium" data-bill-line>0.00</td>
        <td class="py-2">
            <button type="button" class="text-sm font-semibold text-red-700" data-bill-remove>Remove</button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-bill-form]');
    if (!form) return;

    const rows = form.querySelector('[data-bill-rows]');
    const template = document.getElementById('bill-row-template');
    const patientSelect = form.querySelector('[data-bill-patient]');
    const appointmentSelect = form.querySelector('[data-bill-appointment]');
    const catalog = form.querySelector('[data-bill-catalog]');
    const discountInput = form.querySelector('[name="discount"]');

    const money = (value) => Number(value || 0).toFixed(2);

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
        form.querySelector('[data-bill-subtotal]').textContent = money(subtotal);
        form.querySelector('[data-bill-discount-display]').textContent = money(discount);
        form.querySelector('[data-bill-total]').textContent = money(Math.max(0, subtotal - discount));
    };

    const addRow = (values = {}) => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(rows.children.length));
        rows.insertAdjacentHTML('beforeend', html);
        const row = rows.lastElementChild;
        if (values.item_type) row.querySelector('[name*="[item_type]"]').value = values.item_type;
        if (values.item_id) row.querySelector('[name*="[item_id]"]').value = values.item_id;
        if (values.description) row.querySelector('[name*="[description]"]').value = values.description;
        if (values.quantity) row.querySelector('[data-bill-qty]').value = values.quantity;
        if (values.unit_price !== undefined) row.querySelector('[data-bill-price]').value = values.unit_price;
        reindex();
        totals();
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

    form.querySelector('[data-bill-add]').addEventListener('click', () => addRow());
    catalog.addEventListener('change', () => {
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
        event.target.closest('[data-bill-row]')?.remove();
        if (!rows.children.length) addRow();
        reindex();
        totals();
    });
    form.addEventListener('input', totals);
    patientSelect.addEventListener('change', filterAppointments);
    appointmentSelect.addEventListener('change', () => {
        const option = appointmentSelect.selectedOptions[0];
        if (!option?.value) return;
        if (option.dataset.patientId) patientSelect.value = option.dataset.patientId;
        filterAppointments();
    });

    filterAppointments();
    totals();
});
</script>
