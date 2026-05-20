<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id', 'code', 'name', 'duration', 'fees', 'description', 'status',
    ];

    protected function casts(): array
    {
        return [
            'fees' => 'decimal:2',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
