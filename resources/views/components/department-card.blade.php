@props(['department'])

<article class="group flex h-full flex-col overflow-hidden border border-sand-100 bg-white transition hover:border-teal-700">
    <div class="relative h-40 overflow-hidden bg-teal-50">
        <img
            src="{{ $department->image ?: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=70' }}"
            alt="{{ $department->name }} department"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        >
        <div class="absolute left-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-md bg-white text-teal-800">
            <x-icon :name="$department->icon ?: 'heart'" class="h-5 w-5" />
        </div>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="text-base font-semibold text-navy-900">{{ $department->name }}</h3>
        <p class="mt-2 flex-1 text-sm leading-6 text-ink-600">{{ \Illuminate\Support\Str::limit(strip_tags($department->description), 120) }}</p>
        <a href="#departments" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">
            View Department
            <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
