<?php

namespace App\Http\Requests\Admin;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = $this->route('doctor');

        return $doctor instanceof Doctor
            && ($this->user()?->can('clinic-hours.manage', $doctor) ?? false);
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'days' => ['required', 'array'],
            'days.*.open' => ['sometimes', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
            'days.*.break_start' => ['nullable', 'date_format:H:i'],
            'days.*.break_end' => ['nullable', 'date_format:H:i'],
            'days.*.slot_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:120'],
        ];
    }

    /**
     * @return list<array{weekday: string, start_time: string, end_time: string, break_start: string|null, break_end: string|null, slot_duration_minutes: int, is_active: bool}>
     */
    public function sessions(): array
    {
        $sessions = [];

        foreach (array_keys(DoctorSchedule::WEEKDAYS) as $weekday) {
            $day = $this->input('days.'.$weekday, []);

            if (! ($day['open'] ?? false)) {
                continue;
            }

            $sessions[] = [
                'weekday' => $weekday,
                'start_time' => $this->clock((string) ($day['start_time'] ?? '')),
                'end_time' => $this->clock((string) ($day['end_time'] ?? '')),
                'break_start' => filled($day['break_start'] ?? null) ? $this->clock((string) $day['break_start']) : null,
                'break_end' => filled($day['break_end'] ?? null) ? $this->clock((string) $day['break_end']) : null,
                'slot_duration_minutes' => max(15, (int) ($day['slot_duration_minutes'] ?? 30)),
                'is_active' => true,
            ];
        }

        return $sessions;
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $openCount = 0;

                foreach (DoctorSchedule::WEEKDAYS as $weekday => $label) {
                    $day = $this->input('days.'.$weekday, []);

                    if (! ($day['open'] ?? false)) {
                        continue;
                    }

                    $openCount++;
                    $start = (string) ($day['start_time'] ?? '');
                    $end = (string) ($day['end_time'] ?? '');

                    if ($start === '' || $end === '') {
                        $validator->errors()->add('days.'.$weekday.'.start_time', 'Set clinic hours for '.$label.'.');

                        continue;
                    }

                    if ($end <= $start) {
                        $validator->errors()->add('days.'.$weekday.'.end_time', 'End time must be after the start time.');
                    }

                    $breakStart = (string) ($day['break_start'] ?? '');
                    $breakEnd = (string) ($day['break_end'] ?? '');

                    if (($breakStart === '') !== ($breakEnd === '')) {
                        $validator->errors()->add('days.'.$weekday.'.break_start', 'Set both break times, or leave them empty.');
                    }

                    if ($breakStart !== '' && $breakEnd !== '' && $breakEnd <= $breakStart) {
                        $validator->errors()->add('days.'.$weekday.'.break_end', 'Break end must be after break start.');
                    }
                }

                if ($openCount === 0) {
                    $validator->errors()->add('days', 'Choose at least one clinic day.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $days = [];

        foreach (array_keys(DoctorSchedule::WEEKDAYS) as $weekday) {
            $day = $this->input('days.'.$weekday, []);
            $days[$weekday] = [
                'open' => $this->boolean('days.'.$weekday.'.open'),
                'start_time' => $this->clockInput($day['start_time'] ?? null),
                'end_time' => $this->clockInput($day['end_time'] ?? null),
                'break_start' => $this->clockInput($day['break_start'] ?? null),
                'break_end' => $this->clockInput($day['break_end'] ?? null),
                'slot_duration_minutes' => (int) ($day['slot_duration_minutes'] ?? 30),
            ];
        }

        $this->merge(['days' => $days]);
    }

    private function clockInput(mixed $value): ?string
    {
        $time = trim((string) $value);

        if ($time === '') {
            return null;
        }

        return substr($time, 0, 5);
    }

    private function clock(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
