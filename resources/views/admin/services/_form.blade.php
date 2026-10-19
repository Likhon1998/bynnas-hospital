<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div>
        <label for="department_id" class="text-sm font-medium text-navy-900">Department</label>
        <select id="department_id" name="department_id" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
            <option value="">None</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $service->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <x-form.input name="title" label="Title" :value="$service->title" required />
    <x-form.input name="slug" label="Slug" :value="$service->slug" />
    <x-form.textarea name="summary" label="Short description" :value="$service->summary" />
    <x-form.textarea name="body" label="Details" :value="$service->body" :rows="7" />
    <x-form.input name="icon" label="Icon name" :value="$service->icon" />
    <x-form.input name="image" label="Image URL" :value="$service->image" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$service->sort_order ?? 0" />
    <div class="flex flex-wrap gap-6">
        <x-form.checkbox name="is_featured" label="Featured" :checked="$service->is_featured" />
        <x-form.checkbox name="is_active" label="Active" :checked="$service->is_active" />
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.services.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
