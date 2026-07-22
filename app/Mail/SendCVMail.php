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

class SendCVMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application, public string $examName = '') {}

    public function envelope(): Envelope
    {
        $examName = $this->examName ?: ($this->application->exam?->name ?? 'Admission Exam');

        return new Envelope(
            subject: 'Your CV - '.$examName.' | BIGM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.send-cv',
        );
    }

    public function attachments(): array
    {
        $application = $this->application;

        $filename = 'cv-'.$application->application_id.'.pdf';

        try {
            $pdf = Pdf::loadView('reports.individual-cv', [
                'application' => $application,
            ])->setPaper('a4', 'portrait');

            return [
                Attachment::fromData(
                    fn () => $pdf->output(),
                    $filename
                )->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            Log::error('SendCVMail: failed to generate PDF attachment', [
                'application_ulid' => $application->ulid,
                'error' => $e->getMessage(),
            ]);

            // Return no attachment rather than fail the whole job
            return [];
        }
    }
}

