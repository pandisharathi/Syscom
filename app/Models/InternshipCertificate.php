<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipCertificate extends Model
{
    protected $fillable = [
        'institution_id', 'internship_student_id', 'certificate_template_id',
        'certificate_number', 'internship_title', 'encrypted_token', 'issue_date', 'completion_date',
        'custom_fields', 'status', 'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'completion_date' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(InternshipStudent::class, 'internship_student_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(CertificateVerificationLog::class, 'internship_certificate_id');
    }

    public function verificationUrl(): string
    {
        return route('public.certificate.verify', $this->encrypted_token);
    }
}
