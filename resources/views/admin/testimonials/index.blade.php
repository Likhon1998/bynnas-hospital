@extends('layouts.admin')
@section('title', 'Testimonials')
@section('heading', 'Testimonials')
@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.testimonials.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add testimonial</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Rating</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($testimonials as $testimonial)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-navy-900">{{ $testimonial->display_name }}</p>
                            <p class="text-ink-600">{{ $testimonial->display_role }}</p>
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $testimonial->rating }}/5</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$testimonial->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.testimonials.destroy', $testimonial)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No testimonials yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $testimonials->links() }}</div>
@endsection
