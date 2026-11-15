@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Bynnas Hospital';
    $phone = $hospital->get('phone') ?: '';
    $emergency = $hospital->get('emergency_phone') ?: '';
    $email = $hospital->get('email');
    $address = $hospital->get('address') ?: '';
    $tagline = $hospital->get('tagline') ?: 'Care Today • Healthier Tomorrow';
    $logo = filled($hospital->get('logo'))
        ? $hospital->get('logo')
        : '/images/branding/bynnas-hospital-logo.png';
    if (str_starts_with((string) $logo, '/images/branding/')) {
        $logoPath = public_path(ltrim((string) $logo, '/'));
        $logo .= '?v='.(is_file($logoPath) ? filemtime($logoPath) : '2');
    }
    $socials = collect([
        'Facebook' => $hospital->get('facebook_url'),
        'YouTube' => $hospital->get('youtube_url'),
        'Instagram' => $hospital->get('instagram_url'),
    ])->filter(fn (?string $url): bool => filled($url) && $url !== 'https://facebook.com' && $url !== 'https://youtube.com' && $url !== 'https://instagram.com');
    $privacy = filled($hospital->get('privacy_content'));
    $terms = filled($hospital->get('terms_content'));
@endphp

<footer id="contact" class="ch-foot">
    <div class="ch-wrap ch-foot__grid">
        <div class="ch-foot__brand">
            <a href="{{ route('home') }}" class="ch-brand has-logo" aria-label="{{ $hospitalName }} home">
                <img
                    src="{{ $logo }}"
                    alt="{{ $hospitalName }}"
                    class="ch-brand__logo ch-brand__logo--foot"
                    width="220"
                    height="72"
                    decoding="async"
                >
            </a>
            @if (filled($tagline) && ! str_contains(strtolower($tagline), 'care today'))
                <p class="mt-4">{{ $tagline }}</p>
            @endif
            @if ($socials->isNotEmpty())
                <p class="mt-3">
                    @foreach ($socials as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a>@if (! $loop->last) · @endif
                    @endforeach
                </p>
            @endif
        </div>

        <div>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('departments') }}">Departments</a></li>
                <li><a href="{{ route('doctors') }}">Doctors</a></li>
                <li><a href="{{ route('home') }}#locations">Locations</a></li>
                <li><a href="{{ route('home') }}#contact">Contact Us</a></li>
            </ul>
        </div>

        <div>
            <h3>Patients & Visitors</h3>
            <ul>
                <li><a href="{{ route('services') }}">Patient Services</a></li>
                <li><a href="{{ route('lab-reports.index') }}">Lab reports</a></li>
                <li><a href="{{ route('about') }}">Visiting Hours</a></li>
                <li><a href="{{ route('services') }}">Billing & Insurance</a></li>
                <li><a href="{{ route('notices') }}">Notices</a></li>
            </ul>
        </div>

        <div>
            <h3>Our Services</h3>
            <ul>
                @forelse ($footerServices as $service)
                    <li><a href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}">{{ $service->title }}</a></li>
                @empty
                    <li><a href="{{ route('services') }}">View services</a></li>
                @endforelse
            </ul>
        </div>

        <div>
            <h3>Contact Us</h3>
            @if ($address)
                <p>{{ $address }}</p>
            @endif
            @if ($phone)
                <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
            @endif
            @if ($email)
                <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
            @if ($emergency)
                <p>{{ $emergency }}</p>
            @endif
        </div>
    </div>
    <div class="ch-wrap ch-legal">
        <p>© {{ now()->year }} {{ $hospitalName }}. All Rights Reserved.</p>
        <p>
            @if ($terms)
                <a href="{{ route('terms') }}">Terms of Use</a>
            @endif
            @if ($terms && $privacy)
                ·
            @endif
            @if ($privacy)
                <a href="{{ route('privacy') }}">Privacy Policy</a>
            @endif
        </p>
    </div>
</footer>
