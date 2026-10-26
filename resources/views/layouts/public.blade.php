<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $hospital->get('hospital_name') ?: 'Hospital')</title>
    <meta name="description" content="@yield('meta_description', $hospital->get('tagline') ?: 'Hospital care in Dhaka.')">
    <link rel="canonical" href="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8fafc] font-sans text-ink-900 antialiased">
    @include('layouts.partials.topbar')
    @include('layouts.partials.header')
    <main>
        @if (session('status'))
            <p class="border-b border-[#bbf7d0] bg-[#ecfdf3] py-3 text-center text-sm font-medium text-[#166534]"><span class="web-wrap block">{{ session('status') }}</span></p>
        @endif
        @if (session('error'))
            <p class="border-b border-[#fecaca] bg-[#fef2f2] py-3 text-center text-sm font-medium text-[#991b1b]"><span class="web-wrap block">{{ session('error') }}</span></p>
        @endif
        @yield('content')
    </main>
    @include('layouts.partials.footer')
</body>
</html>
