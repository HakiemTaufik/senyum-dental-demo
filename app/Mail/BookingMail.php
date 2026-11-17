<?php

namespace App\Mail;

use App\Models\BookingRequest;
use App\Settings\ClinicSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base for all booking emails. Emails are queued, so the website responds
 * instantly and sending happens a moment later in the background.
 */
abstract class BookingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public BookingRequest $booking)
    {
        $this->afterCommit();
    }

    abstract protected function subjectLine(): string;

    abstract protected function template(): string;

    public function envelope(): Envelope
    {
        $clinic = app(ClinicSettings::class);

        return new Envelope(
            subject: $this->subjectLine().' – '.$clinic->name,
            replyTo: $clinic->email ? [$clinic->email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking.'.$this->template(),
            with: ['booking' => $this->booking, 'clinic' => app(ClinicSettings::class)],
        );
    }
}
