<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'email', 'phone', 'address', 'logo',
        'subscription_plan', 'subscription_starts_at', 'subscription_ends_at',
        'is_active', 'enquiry_enabled', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enquiry_enabled' => 'boolean',
            'subscription_starts_at' => 'date',
            'subscription_ends_at' => 'date',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(InstitutionModule::class);
    }

    public function moduleEnabled(string $key): bool
    {
        if (! $this->relationLoaded('modules')) {
            $row = $this->modules()->where('module_key', $key)->first();

            return $row ? (bool) $row->enabled : true;
        }

        $mod = $this->modules->firstWhere('module_key', $key);

        return $mod ? (bool) $mod->enabled : true;
    }
}
