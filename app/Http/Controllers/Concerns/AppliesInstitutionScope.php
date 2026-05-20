<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesInstitutionScope
{
    protected function filterInstitution(Builder $query): void
    {
        $user = auth()->user();
        if ($user && ! $user->isSuperAdmin()) {
            $query->where('institution_id', $user->institution_id);
        }
    }
}
