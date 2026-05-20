<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id', 'course_id', 'faculty_id', 'name',
        'start_date', 'end_date', 'timing', 'number_of_days', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'number_of_days' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'batch_student')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
