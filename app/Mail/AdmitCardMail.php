<?php

namespace App\Mail;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

    public function attachments(): array
    {
        $application = $this->application;
        $mailType    = $this->mailType;

        $filename = match ($mailType) {
            'viva_eligibility'  => 'viva-eligibility-notice.pdf',
            'program_selection' => 'program-selection-notice.pdf',
            default             => 'admit-card.pdf',
        };

        try {
            $pdf = Pdf::loadView('pdf.admit-card', [
                'application' => $application,
                'mailType'    => $mailType,
            ])->setPaper('a4', 'portrait');

            return [
                Attachment::fromData(
                    fn () => $pdf->output(),
                    $filename
                )->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            Log::error('AdmitCardMail: failed to generate PDF attachment', [
                'application_ulid' => $application->ulid,
                'mail_type'        => $mailType,
                'error'            => $e->getMessage(),
            ]);

            // Return no attachment rather than fail the whole job
            return [];
        }
    }
}
