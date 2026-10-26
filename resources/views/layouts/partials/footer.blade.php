@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone');
    $emergency = $hospital->get('emergency_phone');
    $email = $hospital->get('email');
    $address = $hospital->get('address');
    $weekdayHours = $workingHours['saturday'] ?? $workingHours['monday'] ?? '9:00 AM – 9:00 PM';
@endphp

<footer id="contact" class="bg-[#122447] text-[#dbe4f0]">
    <div class="web-wrap grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="text-base font-bold text-white">{{ $hospitalName }}</p>
            @if ($address)
                <p class="mt-3 text-sm leading-6 text-[#94a3b8]">{{ $address }}</p>
            @endif
            @if ($phone)
                <p class="mt-3 text-sm"><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hover:text-white">OPD {{ $phone }}</a></p>
            @endif
            @if ($emergency)
                <p class="mt-1.5 text-sm"><a href="tel:{{ preg_replace('/\s+/', '', $emergency) }}" class="font-semibold text-[#86efac] hover:text-white">Emergency {{ $emergency }}</a></p>
            @endif
            @if ($email)
                <p class="mt-1.5 text-sm"><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></p>
            @endif
        </div>

        <div>
            <p class="text-sm font-bold text-white">OPD hours</p>
            <p class="mt-3 text-sm leading-6 text-[#94a3b8]">Saturday to Thursday<br>{{ $weekdayHours }}</p>
            <p class="mt-3 text-sm leading-6 text-[#94a3b8]">Friday: emergency only<br>Emergency desk: open all night</p>
        </div>

        <div>
            <p class="text-sm font-bold text-white">On this site</p>
            <ul class="mt-3 space-y-2 text-sm text-[#94a3b8]">
                <li><a href="{{ route('about') }}" class="hover:text-white">About the hospital</a></li>
                <li><a href="{{ route('services') }}" class="hover:text-white">Services</a></li>
                <li><a href="{{ route('home') }}#appointment" class="hover:text-white">Request a visit</a></li>
                @foreach ($footerDepartments->take(3) as $department)
                    <li>{{ $department->name }}</li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="text-sm font-bold text-white">If you are coming in</p>
            <p class="mt-3 text-sm leading-6 text-[#94a3b8]">Bring a previous prescription if you have one. For a first visit, the OPD desk will open a file and send you to the right clinic.</p>
        </div>
    </div>
    <div class="border-t border-white/10">
        <p class="web-wrap py-4 text-xs text-[#64748b]">
            {{ $hospitalName }} · Gulshan, Dhaka
        </p>
    </div>
</footer>
