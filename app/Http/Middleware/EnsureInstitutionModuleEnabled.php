<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $user->institution_id) {
            abort(403);
        }

        /** @var Institution $inst */
        $inst = Institution::with('modules')->findOrFail($user->institution_id);
        if (! $inst->moduleEnabled($module)) {
            abort(403, 'This module is disabled for your institution.');
        }

        return $next($request);
    }
}
