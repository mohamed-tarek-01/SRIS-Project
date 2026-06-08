<?php

namespace App\Mail;

use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceDueAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Vehicle $vehicle,
        public Reminder $reminder,
        public int $currentOdometer
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Service Due Soon — ' . $this->reminder->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.service_due_alert',
        );
    }
}
