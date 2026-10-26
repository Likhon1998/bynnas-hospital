@php
    $footerImage = 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=2000&q=80';
@endphp

<footer id="contact" class="relative overflow-hidden text-white">
    <img src="{{ $footerImage }}" alt="" class="absolute inset-0 h-full w-full scale-105 object-cover blur-[1px]">
    <div class="absolute inset-0 bg-teal-900/70"></div>
    <div class="relative mx-auto flex min-h-[22rem] max-w-4xl flex-col items-center justify-center px-6 py-24 text-center">
        <p class="text-7xl font-bold tracking-tight text-white/85 sm:text-8xl">Dr.</p>
        <a href="#services" class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-white/90 transition hover:text-white">
            Check out for more
            <span aria-hidden="true" class="text-base">↓</span>
        </a>
    </div>
</footer>
