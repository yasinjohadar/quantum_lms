<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplateEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subject;
    public string $body;
    public string $fromAddress;
    public string $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, string $fromAddress, string $fromName)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: [$this->fromAddress, $this->fromName],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            text: 'emails.template',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
