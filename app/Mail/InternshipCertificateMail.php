<?php

namespace App\Mail;

use App\Models\InternshipCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InternshipCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public InternshipCertificate $cert) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Internship Certificate - ' . $this->cert->certificate_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.internship-certificate',
        );
    }

    public function attachments(): array
    {
        $qrSvg = QrCode::size(120)->format('svg')->generate($this->cert->verificationUrl());
        $pdf = Pdf::loadView('admin.internship-certificates.pdf', [
            'cert' => $this->cert,
            'qrSvg' => $qrSvg,
        ])->setPaper('a4', 'landscape')->output();

        return [
            Attachment::fromData(fn () => $pdf, 'Certificate-' . $this->cert->certificate_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
