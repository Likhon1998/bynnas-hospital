<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.services.index')"
    lede="Write the service the way a patient should understand it, then attach it to a department."
>
    <x-admin.form-section title="Service" note="Title and summary appear on the homepage and services list.">
        <x-form.input name="title" label="Title" :value="$service->title" required />
        <x-form.select name="department_id" label="Department">
            <option value="">None</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $service->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="slug" label="Slug" :value="$service->slug" />
        <div class="form-span-2">
            <x-form.textarea name="summary" label="Short description" :value="$service->summary" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Details" note="Shown on the service page after a visitor clicks through." :columns="false">
        <x-form.textarea name="body" label="Full description" :value="$service->body" :rows="7" />
    </x-admin.form-section>

    <x-admin.form-section title="Presentation">
        <x-form.input name="icon" label="Icon name" :value="$service->icon" />
        <x-form.input name="image" label="Image URL" :value="$service->image" />
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$service->sort_order ?? 0" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <div class="form-grid">
            <x-form.checkbox name="is_featured" label="Featured service" :checked="$service->is_featured" />
            <x-form.checkbox name="is_active" label="Active on the website" :checked="$service->is_active" />
        </div>
    </x-admin.form-section>
</x-admin.form-page>
