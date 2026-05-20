<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\InstitutionModule;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.institutions.index');
    }

    public function create(): View
    {
        return view('admin.institutions.create');
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $this->validated($request);
        $institution = Institution::query()->create($data);
        $this->syncDefaultModules($institution);

        $this->activityLog->log('institution.created', $institution);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Institution created', 'id' => $institution->id]);
        }

        return redirect()->route('admin.institutions.index')->with('success', 'Institution created.');
    }

    public function edit(Institution $institution): View
    {
        $institution->load('modules');

        return view('admin.institutions.edit', compact('institution'));
    }

    public function update(Request $request, Institution $institution): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $this->validated($request, $institution->id);
        $institution->update($data);

        if ($request->has('modules')) {
            foreach ([
                'dashboard', 'institutions', 'students', 'internship', 'attendance', 'expense', 'reports', 'settings', 'users',
            ] as $key) {
                InstitutionModule::query()->updateOrCreate(
                    ['institution_id' => $institution->id, 'module_key' => $key],
                    ['enabled' => $request->boolean('modules.'.$key)]
                );
            }
        }

        $this->activityLog->log('institution.updated', $institution);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Institution updated']);
        }

        return redirect()->route('admin.institutions.index')->with('success', 'Institution updated.');
    }

    public function destroy(Request $request, Institution $institution): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $institution->delete();
        $this->activityLog->log('institution.deleted', $institution);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Institution deleted']);
        }

        return redirect()->route('admin.institutions.index')->with('success', 'Institution deleted.');
    }

    public function toggleActive(Request $request, Institution $institution): JsonResponse
    {
        $institution->update(['is_active' => ! $institution->is_active]);

        return response()->json(['is_active' => $institution->is_active]);
    }

    public function data(Request $request): JsonResponse
    {
        $q = Institution::query()->orderByDesc('id');

        return $this->dataTablesJson(
            $request,
            $q,
            ['name', 'code', 'email', 'phone', 'subscription_plan'],
            function (Institution $i) {
                return [
                    'id' => $i->id,
                    'name' => $i->name,
                    'code' => $i->code,
                    'email' => $i->email,
                    'subscription_plan' => $i->subscription_plan,
                    'is_active' => $i->is_active,
                    'enquiry_enabled' => $i->enquiry_enabled,
                    'created_at' => $i->created_at?->format('Y-m-d'),
                ];
            }
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'max:50', Rule::unique('institutions', 'code')->ignore($ignoreId)],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'subscription_plan' => ['nullable', 'string', 'max:100'],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'enquiry_enabled' => ['sometimes', 'boolean'],
        ]);
    }

    private function syncDefaultModules(Institution $institution): void
    {
        foreach ([
            'dashboard', 'institutions', 'students', 'internship', 'attendance', 'expense', 'reports', 'settings', 'users',
        ] as $key) {
            InstitutionModule::query()->firstOrCreate(
                ['institution_id' => $institution->id, 'module_key' => $key],
                ['enabled' => true]
            );
        }
    }
}
