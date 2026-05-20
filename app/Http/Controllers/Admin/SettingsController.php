<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $institution = $request->user()->isSuperAdmin()
            ? null
            : Institution::query()->with('modules')->findOrFail($request->user()->institution_id);

        return view('admin.settings.index', compact('institution'));
    }

    public function updateInstitution(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        abort_if($request->user()->isSuperAdmin(), 403);

        $institution = Institution::query()->findOrFail($request->user()->institution_id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);
        $institution->update($data);
        $this->activityLog->log('institution.self_updated', $institution);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Updated']);
        }

        return back()->with('success', 'Updated');
    }

    public function toggleEnquiry(Request $request): JsonResponse
    {
        abort_if($request->user()->isSuperAdmin(), 403);
        $institution = Institution::query()->findOrFail($request->user()->institution_id);
        $institution->update(['enquiry_enabled' => ! $institution->enquiry_enabled]);

        return response()->json(['enquiry_enabled' => $institution->enquiry_enabled]);
    }
}
