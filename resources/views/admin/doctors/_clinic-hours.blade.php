<section class="form-card mt-4">
    <header class="form-card__head">
        <h2>Weekly clinic days</h2>
        <p>Turn a day on or off and set the hours. The public booking calendar updates as soon as you save.</p>
    </header>
    <div class="form-card__body">
        <form method="POST" action="{{ route('admin.doctors.schedules.update', $doctor) }}" class="clinic-hours">
            @csrf
            @method('PUT')

            @error('days')
                <p class="clinic-hours__error">{{ $message }}</p>
            @enderror

            <div class="clinic-days">
                @foreach ($hours as $weekday => $day)
                    <article @class(['clinic-day', 'is-open' => $day['open']])>
                        <label class="clinic-day__switch">
                            <input type="checkbox" name="days[{{ $weekday }}][open]" value="1" @checked($day['open'])>
                            <span>{{ $day['label'] }}</span>
                        </label>

                        <div class="clinic-day__hours">
                            <label>
                                <span>Starts</span>
                                <input type="time" name="days[{{ $weekday }}][start_time]" value="{{ $day['start_time'] }}">
                            </label>
                            <label>
                                <span>Ends</span>
                                <input type="time" name="days[{{ $weekday }}][end_time]" value="{{ $day['end_time'] }}">
                            </label>
                            <label>
                                <span>Break from</span>
                                <input type="time" name="days[{{ $weekday }}][break_start]" value="{{ $day['break_start'] }}">
                            </label>
                            <label>
                                <span>Break until</span>
                                <input type="time" name="days[{{ $weekday }}][break_end]" value="{{ $day['break_end'] }}">
                            </label>
                            <label>
                                <span>Slot</span>
                                <select name="days[{{ $weekday }}][slot_duration_minutes]">
                                    @foreach ([15, 20, 30, 45, 60] as $minutes)
                                        <option value="{{ $minutes }}" @selected((int) $day['slot_duration_minutes'] === $minutes)>{{ $minutes }} min</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        @error('days.'.$weekday.'.start_time')
                            <p class="clinic-hours__error">{{ $message }}</p>
                        @enderror
                        @error('days.'.$weekday.'.end_time')
                            <p class="clinic-hours__error">{{ $message }}</p>
                        @enderror
                        @error('days.'.$weekday.'.break_start')
                            <p class="clinic-hours__error">{{ $message }}</p>
                        @enderror
                        @error('days.'.$weekday.'.break_end')
                            <p class="clinic-hours__error">{{ $message }}</p>
                        @enderror
                    </article>
                @endforeach
            </div>

            <div class="form-span-2 mt-4">
                <button type="submit" class="form-btn form-btn--navy">Save clinic days</button>
            </div>
        </form>
    </div>
</section>

<section class="form-card mt-4">
    <header class="form-card__head">
        <h2>Leave and vacation</h2>
        <p>Block specific dates. Those days stay red and closed on the public calendar until you remove the leave.</p>
    </header>
    <div class="form-card__body">
        @if ($doctor->doctorLeaves->isEmpty())
            <p class="text-sm text-ink-600">No leave recorded. Clinic days still come from the weekly schedule.</p>
        @else
            <div class="table-frame">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>From</th>
                            <th>Until</th>
                            <th>Reason</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($doctor->doctorLeaves as $leave)
                            <tr>
                                <td>{{ $leave->typeLabel() }}</td>
                                <td>{{ $leave->starts_on?->format('d M Y') }}</td>
                                <td>{{ $leave->ends_on?->format('d M Y') }}</td>
                                <td>{{ $leave->reason ?: '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.doctors.leaves.destroy', [$doctor, $leave]) }}" onsubmit="return confirm('Open these dates for booking again?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="form-btn form-btn--ghost">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.doctors.leaves.store', $doctor) }}" class="form-grid mt-4">
            @csrf
            <x-form.input name="starts_on" label="Starts on" type="date" :value="old('starts_on')" required />
            <x-form.input name="ends_on" label="Ends on" type="date" :value="old('ends_on')" required />
            <x-form.select name="type" label="Type" required>
                @foreach (\App\Models\DoctorLeave::TYPES as $type => $label)
                    <option value="{{ $type }}" @selected(old('type', 'vacation') === $type)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="reason" label="Reason" :value="old('reason')" />
            <div class="form-span-2">
                <button type="submit" class="form-btn form-btn--navy">Block these dates</button>
            </div>
        </form>
    </div>
</section>
