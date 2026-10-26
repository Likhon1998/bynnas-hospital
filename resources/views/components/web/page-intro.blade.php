@props([
    'title',
    'kicker' => null,
    'note' => null,
])

<section class="border-b border-[#ddd8cc] bg-[#efeae0]">
    <div class="web-wrap py-10 sm:py-12">
        @if ($kicker)
            <p class="web-kicker">{{ $kicker }}</p>
        @endif
        <h1 @class(['web-title max-w-3xl', 'mt-2' => filled($kicker)])>{{ $title }}</h1>
        @if ($note)
            <p class="web-lead mt-4">{{ $note }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
