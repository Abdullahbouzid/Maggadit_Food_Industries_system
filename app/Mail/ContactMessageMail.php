<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رسالة تواصل جديدة: '.($this->contactMessage->subject ?? 'بدون عنوان'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_message',
            with: ['contactMessage' => $this->contactMessage],
        );
    }
}
