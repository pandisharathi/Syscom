<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipAttendance extends Model
{
    protected $fillable = [
        'institution_id', 'internship_batch_id', 'attendance_date', 'marked_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InternshipBatch::class, 'internship_batch_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(InternshipAttendanceDetail::class);
    }
}
