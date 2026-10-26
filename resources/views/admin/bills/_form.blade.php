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

<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.bills.index')"
    submit="Issue bill"
    submit-name="action"
    submit-value="issue"
    lede="Attach the patient and visit first, then add every charge as a line so the bill is ready to issue."
    data-bill-form
>
    <x-slot:actions>
        <button type="submit" name="action" value="draft" class="form-btn form-btn--navy">Save draft</button>
    </x-slot:actions>

    <x-admin.form-section title="Patient and visit" note="The bill stays linked to one patient. An appointment is optional.">
        <x-form.select name="patient_id" label="Patient" required data-bill-patient>
            <option value="">Select patient</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((string) old('patient_id', $bill->patient_id) === (string) $patient->id)>{{ $patient->full_name }} · {{ $patient->phone }}</option>
            @endforeach
        </x-form.select>
        <x-form.select name="appointment_id" label="Linked appointment" data-bill-appointment>
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
        </x-form.select>
        <x-form.input name="bill_date" label="Bill date" type="date" :value="optional($bill->bill_date)->toDateString() ?? now()->toDateString()" required />
        <x-form.input name="discount" label="Discount" type="number" :value="$bill->discount ?? 0" data-bill-discount="true" />
        <div class="form-span-2">
            <x-form.textarea name="notes" label="Notes" :value="$bill->notes" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Bill items" note="Add from the catalog or type a custom charge." :columns="false">
        <div class="mb-4 flex flex-wrap gap-2">
            <select data-bill-catalog class="admin-field px-3 py-2 text-sm">
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
            <button type="button" class="form-btn form-btn--navy" data-bill-add>Add custom item</button>
        </div>
        @error('items') <p class="mb-3 text-sm text-red-700">{{ $message }}</p> @enderror

        <div class="table-frame table-frame--fit">
            <table class="pro-table pro-table--form">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="col-num">Qty</th>
                        <th class="col-num">Unit price</th>
                        <th class="col-num">Line total</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody data-bill-rows>
                    @foreach ($itemRows as $index => $item)
                        <tr data-bill-row>
                            <td>
                                <input type="hidden" name="items[{{ $index }}][item_type]" value="{{ $item['item_type'] ?? 'custom' }}">
                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item['item_id'] ?? '' }}">
                                <input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required class="admin-field w-full min-w-56 px-3 py-2 text-sm">
                            </td>
                            <td class="col-num">
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" data-bill-qty class="admin-field w-20 px-3 py-2 text-right text-sm">
                            </td>
                            <td class="col-num">
                                <input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" data-bill-price class="admin-field w-28 px-3 py-2 text-right text-sm">
                            </td>
                            <td class="col-num" data-bill-line>0.00</td>
                            <td class="col-action">
                                <button type="button" class="text-sm font-semibold text-red-700" data-bill-remove>Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <dl class="mt-5 ml-auto max-w-xs space-y-1 text-sm">
            <div class="flex justify-between gap-8"><dt class="text-ink-600">Subtotal</dt><dd data-bill-subtotal>0.00</dd></div>
            <div class="flex justify-between gap-8"><dt class="text-ink-600">Discount</dt><dd data-bill-discount-display>0.00</dd></div>
            <div class="flex justify-between gap-8 font-semibold text-navy-900"><dt>Total</dt><dd data-bill-total>0.00</dd></div>
        </dl>
    </x-admin.form-section>
</x-admin.form-page>

<template id="bill-row-template">
    <tr data-bill-row>
        <td>
            <input type="hidden" name="items[__INDEX__][item_type]" value="custom">
            <input type="hidden" name="items[__INDEX__][item_id]" value="">
            <input name="items[__INDEX__][description]" required class="admin-field w-full min-w-56 px-3 py-2 text-sm">
        </td>
        <td class="col-num">
            <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" data-bill-qty class="admin-field w-20 px-3 py-2 text-right text-sm">
        </td>
        <td class="col-num">
            <input type="number" min="0" step="0.01" name="items[__INDEX__][unit_price]" value="0" data-bill-price class="admin-field w-28 px-3 py-2 text-right text-sm">
        </td>
        <td class="col-num" data-bill-line>0.00</td>
        <td class="col-action">
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
