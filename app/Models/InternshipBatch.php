<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id', 'internship_course_id', 'faculty_id', 'name',
        'start_date', 'end_date', 'timing', 'capacity', 'number_of_days', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'capacity' => 'integer',
            'number_of_days' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(InternshipCourse::class, 'internship_course_id');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(InternshipStudent::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(InternshipAttendance::class);
    }
}
