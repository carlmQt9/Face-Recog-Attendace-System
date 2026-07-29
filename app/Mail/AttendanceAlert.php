<?php

namespace App\Mail;

use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AttendanceRecord $record
    ) {}

    public function envelope(): Envelope
    {
        $studentName = $this->record->student->user->name;

        return new Envelope(
            subject: "Attendance Alert: {$studentName} has arrived safely",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendance-alert',
        );
    }
}
