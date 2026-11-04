<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Staff sign in')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-guest min-h-screen bg-lilac-950 font-sans text-white antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden lg:block">
            <img
                src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1600&q=80"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="hero-lilac-overlay absolute inset-0"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <p class="flex items-center gap-3 text-[1.65rem] font-bold tracking-tight">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                        <svg viewBox="0 0 32 32" class="h-7 w-7" fill="none" aria-hidden="true">
                            <rect x="3" y="3" width="26" height="26" rx="8" fill="#1769AA"/>
                            <path d="M16 9v14M9 16h14" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round"/>
                        </svg>
                    </span>
                    Staff
                </p>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Staff console</p>
                    <h1 class="mt-3 max-w-md text-4xl font-bold leading-tight tracking-tight">
                        Care operations, in one quiet place.
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-7 text-white/75">
                        Appointments, patients, doctors, billing, and hospital content — designed for everyday staff work.
                    </p>
                </div>
                <p class="text-sm text-white/45">Authorized personnel only.</p>
            </div>
        </div>
        <div class="flex items-center justify-center bg-lilac-50 px-6 py-16">
            <div class="w-full max-w-md rounded-[1.75rem] bg-white p-8 text-lilac-900 shadow-[0_24px_50px_-28px_rgb(18_48_74_/_0.28)] ring-1 ring-lilac-400/25">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
