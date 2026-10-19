@props(['testimonial'])

<figure class="flex h-full flex-col border border-sand-100 bg-white p-5">
    <div class="flex items-center gap-1 text-teal-700" aria-label="{{ $testimonial->rating }} out of 5">
        @for ($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= (int) $testimonial->rating ? 'opacity-100' : 'opacity-25' }}">★</span>
        @endfor
    </div>
    <blockquote class="mt-3 flex-1 text-sm leading-6 text-ink-600">“{{ $testimonial->quote }}”</blockquote>
    <figcaption class="mt-4 flex items-center gap-3">
        <img
            src="{{ $testimonial->photo ?: 'https://placehold.co/80x80/0F766E/FFFFFF?text=Patient' }}"
            alt=""
            class="h-11 w-11 rounded-full object-cover"
        >
        <div>
            <p class="text-sm font-semibold text-navy-900">{{ $testimonial->display_name }}</p>
            <p class="text-xs text-ink-400">{{ $testimonial->display_role }}</p>
        </div>
    </figcaption>
</figure>
