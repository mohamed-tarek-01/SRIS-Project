<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyCarTip extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $tip,
        public string $userName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💡 Daily Car Tip — ' . $this->tip['title'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.daily_tip',
        );
    }
}
