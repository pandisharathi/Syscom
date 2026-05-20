<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateVerificationLog extends Model
{
    protected $fillable = [
        'internship_certificate_id', 'ip_address', 'user_agent', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(InternshipCertificate::class, 'internship_certificate_id');
    }
}
