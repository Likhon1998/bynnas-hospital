<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Staff sign in')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-navy-950 font-sans text-white antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="hidden flex-col justify-between bg-[linear-gradient(160deg,#07141f_0%,#123044_48%,#0f5f59_100%)] p-12 lg:flex">
            <p class="text-sm uppercase tracking-[0.22em] text-gold-500">Hospital Management</p>
            <div>
                <h1 class="font-display text-4xl leading-tight">Secure staff access for clinical operations.</h1>
                <p class="mt-4 max-w-md text-white/70">Appointments, doctors, departments, and hospital content — in one professional console.</p>
            </div>
            <p class="text-sm text-white/40">Authorized personnel only.</p>
        </div>
        <div class="flex items-center justify-center px-6 py-16">
            <div class="w-full max-w-md rounded-[2rem] bg-white p-8 text-ink-900 shadow-2xl">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
