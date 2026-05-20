<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class InternshipStudent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reg_no', 'institution_id', 'internship_batch_id', 'internship_enquiry_id', 'user_id',
        'first_name', 'last_name', 'email', 'phone', 'whatsapp_number',
        'gender', 'date_of_birth', 'educational_qualification', 'department', 'college_name',
        'address', 'city', 'state', 'pincode', 'photo', 'joining_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
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

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(InternshipEnquiry::class, 'internship_enquiry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAttendanceEligible(Builder $q): Builder
    {
        return $q->whereIn('status', ['active', 'inactive']);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InternshipPayment::class, 'internship_student_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(InternshipCertificate::class, 'internship_student_id');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::url($this->photo);
    }
}
