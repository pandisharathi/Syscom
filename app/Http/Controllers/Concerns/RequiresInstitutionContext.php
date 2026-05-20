<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Institution;

trait RequiresInstitutionContext
{
    protected function currentInstitutionId(): int
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $institutionId = request()->input('institution_id');
            if ($institutionId) {
                return (int) $institutionId;
            }
            $inst = Institution::where('is_active', true)->first();
            abort_if(!$inst, 403, 'No active institution found. Please create an institution first or provide institution_id in the request.');

            return (int) $inst->id;
        }

        $id = $user->institution_id;
        abort_if(! $id, 403);

        return (int) $id;
    }
}
