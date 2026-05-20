<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipAttendanceDetail extends Model
{
    protected $fillable = ['internship_attendance_id', 'internship_student_id', 'status'];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(InternshipAttendance::class, 'internship_attendance_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(InternshipStudent::class, 'internship_student_id');
    }
}
