<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmitCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application) {}

    public function envelope(): Envelope
    {
        $examName = $this->application->exam?->name ?? 'Admission Exam';

        return new Envelope(
            subject: 'Admit Card – ' . $examName . ' | BIGM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admit-card',
        );
    }
}

