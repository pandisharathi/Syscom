<?php

namespace App\Services;

use App\Models\ActivityLog as ActivityLogModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        $user = Auth::user();

        ActivityLogModel::query()->create([
            'user_id' => $user?->id,
            'institution_id' => $user?->institution_id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
