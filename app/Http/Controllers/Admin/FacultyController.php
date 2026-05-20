<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FacultyController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.faculties.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = Faculty::query()->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['first_name', 'last_name', 'email', 'phone', 'status'],
            fn (Faculty $f) => [
                'id' => $f->id,
                'first_name' => $f->first_name,
                'last_name' => $f->last_name,
                'qualification' => $f->qualification,
                'experience_years' => $f->experience_years,
                'email' => $f->email,
                'phone' => $f->phone,
                'photo_url' => $f->photo ? Storage::disk('public')->url($f->photo) : null,
                'status' => $f->status,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['institution_id'] = $this->currentInstitutionId();
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('faculty', 'public');
        }
        $faculty = Faculty::query()->create($data);
        $this->activityLog->log('faculty.created', $faculty);

        return response()->json(['message' => 'Saved', 'data' => $faculty]);
    }

    public function update(Request $request, Faculty $faculty): JsonResponse
    {
        $this->authorizeInstitution($faculty->institution_id);
        $data = $this->validateData($request, false);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('faculty', 'public');
        }
        $faculty->update($data);
        $this->activityLog->log('faculty.updated', $faculty);

        return response()->json(['message' => 'Updated', 'data' => $faculty]);
    }

    public function destroy(Request $request, Faculty $faculty): JsonResponse
    {
        $this->authorizeInstitution($faculty->institution_id);
        $faculty->delete();
        $this->activityLog->log('faculty.deleted', $faculty);

        return response()->json(['message' => 'Deleted']);
    }

    public function toggleStatus(Faculty $faculty): JsonResponse
    {
        $this->authorizeInstitution($faculty->institution_id);
        $faculty->update(['status' => $faculty->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['status' => $faculty->status]);
    }

    private function validateData(Request $request, bool $create = true): array
    {
        return $request->validate([
            'first_name' => [$create ? 'required' : 'sometimes', 'string', 'max:100'],
            'last_name' => [$create ? 'required' : 'sometimes', 'string', 'max:100'],
            'qualification' => ['nullable', 'string', 'max:191'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:191'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
    }

    private function authorizeInstitution(?int $institutionId): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) {
            return;
        }
        if ((int) $u->institution_id !== (int) $institutionId) {
            abort(403);
        }
    }
}
