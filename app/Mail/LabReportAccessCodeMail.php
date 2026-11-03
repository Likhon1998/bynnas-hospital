<?php

namespace App\Mail;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LabReportAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, object{bill_no: string, report_access_code: string, bill_date: string}>  $codes
     */
    public function __construct(
        public Patient $patient,
        public Collection $codes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your laboratory report access codes',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.lab-report-access-codes',
        );
    }
}
