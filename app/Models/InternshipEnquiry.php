<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipEnquiry extends Model
{
    protected $fillable = [
        'reg_no', 'institution_id', 'internship_course_id', 'first_name', 'last_name', 'email',
        'contact_number', 'whatsapp_number', 'educational_qualification', 'college_name',
        'gender', 'interested_course_text', 'city', 'state', 'preferred_timing', 'message',
        'resume_path', 'status',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(InternshipCourse::class, 'internship_course_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
