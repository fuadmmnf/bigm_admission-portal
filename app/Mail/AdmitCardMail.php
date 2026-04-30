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

    public function __construct(public Application $application, public string $mailType = 'admit_card') {}

    public function envelope(): Envelope
    {
        $examName = $this->application->exam?->name ?? 'Admission Exam';

        $subject = match ($this->mailType) {
            'viva_eligibility' => 'Viva Eligibility Notice - '.$examName.' | BIGM',
            'program_selection' => 'Program Selection Notice - '.$examName.' | BIGM',
            default => 'Admit Card - '.$examName.' | BIGM',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admit-card',
            with: [
                'mailType' => $this->mailType,
            ],
        );
    }
}

