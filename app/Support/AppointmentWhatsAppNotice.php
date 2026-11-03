<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\HospitalSetting;

class AppointmentWhatsAppNotice
{
    public function phoneDigits(?string $phone): ?string
    {
        if (! filled($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = '880'.substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '880'.$digits;
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public function message(Appointment $appointment): ?string
    {
        if ($appointment->serial_no === null) {
            return null;
        }

        $appointment->loadMissing(['patient', 'doctor', 'department']);

        $hospital = HospitalSetting::map();
        $hospitalName = filled($hospital->get('hospital_name'))
            ? (string) $hospital->get('hospital_name')
            : (string) config('app.name');
        $patientName = $appointment->patient?->full_name ?: 'Patient';
        $doctorName = $appointment->doctor?->name ?: 'Doctor';
        $department = $appointment->department?->name;
        $date = $appointment->appointment_date?->format('d M Y') ?: '—';
        $time = $appointment->timeRangeLabel();
        $serial = $appointment->serialLabel();
        $visitNo = $appointment->appointment_no;
        $phone = filled($hospital->get('phone')) ? (string) $hospital->get('phone') : null;

        $lines = [
            'Assalamu Alaikum '.$patientName.',',
            '',
            'Your appointment at *'.$hospitalName.'* is confirmed.',
            '',
            '🎫 *Serial: '.$serial.'*',
            '👨‍⚕️ Doctor: '.$doctorName.($department ? ' ('.$department.')' : ''),
            '📅 Date: '.$date,
            '🕐 Time: '.$time,
            '🧾 Visit: '.$visitNo,
            '',
            'Please arrive 10–15 minutes early and show this serial at reception.',
        ];

        if ($phone !== null) {
            $lines[] = 'Hospital phone: '.$phone;
        }

        $lines[] = '';
        $lines[] = 'Thank you — '.$hospitalName;

        return implode("\n", $lines);
    }

    public function url(Appointment $appointment): ?string
    {
        $digits = $this->phoneDigits($appointment->patient?->phone);
        $message = $this->message($appointment);

        if ($digits === null || $message === null) {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
