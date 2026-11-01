@php($test = $test ?? null)
<x-admin.form-section title="Catalog" note="Price is charged once when the test is ordered.">
    <x-form.input name="code" label="Code" :value="old('code', $test->code ?? '')" required />
    <x-form.input name="name" label="Name" :value="old('name', $test->name ?? '')" required />
    <x-form.select name="category" label="Category" required>
        @foreach (\App\Models\LabTest::CATEGORIES as $value => $label)
            <option value="{{ $value }}" @selected((string) old('category', $test->category ?? 'chemistry') === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="specimen" label="Specimen" :value="old('specimen', $test->specimen ?? 'blood')" required />
    <x-form.input name="unit" label="Unit" :value="old('unit', $test->unit ?? '')" />
    <x-form.input name="reference_range" label="Reference range" :value="old('reference_range', $test->reference_range ?? '')" />
    <x-form.input name="critical_low" label="Critical low" type="number" step="0.001" :value="old('critical_low', $test->critical_low ?? '')" />
    <x-form.input name="critical_high" label="Critical high" type="number" step="0.001" :value="old('critical_high', $test->critical_high ?? '')" />
    <x-form.input name="price" label="Price" type="number" step="0.01" :value="old('price', $test->price ?? 0)" required />
    <div class="form-span-2">
        <x-form.checkbox name="is_active" label="Active and available to order" :checked="old('is_active', $test->is_active ?? true)" />
    </div>
</x-admin.form-section>
