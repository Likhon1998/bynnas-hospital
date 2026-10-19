@props(['service'])

<article class="flex h-full flex-col border border-sand-100 bg-white p-4 transition hover:border-teal-700">
    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-800">
        <x-icon :name="$service->icon ?: 'heart'" class="h-5 w-5" />
    </div>
    <h3 class="mt-3 text-base font-semibold text-navy-900">{{ $service->title }}</h3>
    <p class="mt-2 flex-1 text-sm leading-6 text-ink-600">{{ $service->summary ?: \Illuminate\Support\Str::limit(strip_tags($service->body), 110) }}</p>
    <a href="#services" class="mt-3 text-sm font-semibold text-teal-800">Learn More →</a>
</article>
