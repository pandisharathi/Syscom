<?php

namespace App\Http\Controllers;

use App\Models\InternshipCertificate;
use App\Models\CertificateVerificationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicCertificateController extends Controller
{
    public function verify(string $token): View
    {
        $cert = InternshipCertificate::query()
            ->with(['student.batch.course', 'student.institution', 'template'])
            ->where('encrypted_token', $token)
            ->first();

        if (!$cert || $cert->status !== 'active') {
            return view('public.certificate.invalid');
        }

        // Log the verification attempt
        CertificateVerificationLog::query()->create([
            'internship_certificate_id' => $cert->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'verified_at' => now(),
        ]);

        $qrSvg = $this->generateQrSvg($cert->verificationUrl());

        return view('public.certificate.verify', compact('cert', 'qrSvg'));
    }

    private function generateQrSvg(string $url): string
    {
        try {
            return QrCode::size(120)
                ->format('svg')
                ->errorCorrection('M')
                ->generate($url);
        } catch (\Exception $e) {
            return '';
        }
    }
}
