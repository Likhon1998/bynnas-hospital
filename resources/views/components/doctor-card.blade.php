@props(['doctor'])

<article class="flex h-full flex-col overflow-hidden border border-sand-100 bg-white">
    <div class="relative h-44 bg-navy-900">
        <img
            src="{{ $doctor->photo ?: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=70' }}"
            alt="Portrait of {{ $doctor->name }}"
            class="h-full w-full object-cover object-top opacity-95"
        >
        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-navy-950/80 to-transparent"></div>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-teal-800">{{ $doctor->department?->name }}</p>
        <h3 class="mt-1 text-lg font-semibold text-navy-900">{{ $doctor->name }}</h3>
        <p class="mt-1 text-sm text-ink-600">{{ $doctor->designation }}</p>
        @if ($doctor->specialization)
            <p class="mt-3 text-sm text-ink-600">{{ $doctor->specialization }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button href="#doctors" variant="ghost" class="px-0">View Profile</x-ui.button>
            <x-ui.button href="#contact">Book Appointment</x-ui.button>
        </div>
    </div>
</article>
