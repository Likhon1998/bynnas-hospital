Hello {{ $patient->full_name }},

Here are your laboratory report access codes. Use your phone number with a code at {{ url('/lab-reports') }} to view and download published reports.

@foreach ($codes as $row)
- Bill {{ $row->bill_no }} ({{ $row->bill_date }}): access code {{ $row->report_access_code }}
@endforeach

If you did not request this, you can ignore this message.

Thank you.
