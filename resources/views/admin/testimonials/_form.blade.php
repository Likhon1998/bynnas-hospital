<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-form.input name="display_name" label="Name" :value="$testimonial->display_name" required />
    <x-form.input name="display_role" label="Role / relation" :value="$testimonial->display_role" />
    <x-form.textarea name="quote" label="Quote" :value="$testimonial->quote" required />
    <x-form.input name="rating" label="Rating (1-5)" type="number" :value="$testimonial->rating ?? 5" />
    <x-form.input name="photo" label="Photo URL" :value="$testimonial->photo" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$testimonial->sort_order ?? 0" />
    <x-form.checkbox name="is_active" label="Approved / visible" :checked="$testimonial->is_active" />
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.testimonials.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
