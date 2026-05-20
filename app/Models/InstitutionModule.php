<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionModule extends Model
{
    protected $fillable = ['institution_id', 'module_key', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
