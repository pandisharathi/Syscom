<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseType extends Model
{
    use SoftDeletes;

    protected $fillable = ['institution_id', 'code', 'name', 'status'];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
