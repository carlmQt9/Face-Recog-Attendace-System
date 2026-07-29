<?php

namespace App\Mail;

use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceTimeOut extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AttendanceRecord $record) {}

    public function envelope(): Envelope
    {
        $name = $this->record->student->user->name;
        return new Envelope(subject: "🏠 {$name} has left school");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.time-out');
    }
}
