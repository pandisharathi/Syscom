<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'institution_id', 'name', 'title_main', 'title_sub', 'background_image', 'logo_position',
        'primary_color', 'secondary_color', 'accent_color', 'font_family',
        'border_style', 'is_active', 'show_program_coordinator', 'show_certificate_id',
        'left_signature_title', 'right_signature_title',
        'left_signature_name', 'right_signature_name', 
        'show_left_signature_name', 'show_right_signature_name'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_program_coordinator' => 'boolean',
            'show_certificate_id' => 'boolean',
            'show_left_signature_name' => 'boolean',
            'show_right_signature_name' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
