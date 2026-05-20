<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.courses.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = Course::query()->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['code', 'name', 'duration', 'status'],
            fn (Course $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'duration' => $c->duration,
                'fees' => (float) $c->fees,
                'status' => $c->status,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['institution_id'] = $this->currentInstitutionId();
        $course = Course::query()->create($data);
        $this->activityLog->log('course.created', $course);

        return response()->json(['message' => 'Saved', 'data' => $course]);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorizeInstitution($course->institution_id);
        $course->update($this->validateData($request, $course->id, $course->institution_id));
        $this->activityLog->log('course.updated', $course);

        return response()->json(['message' => 'Updated', 'data' => $course]);
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        $this->authorizeInstitution($course->institution_id);
        $course->delete();
        $this->activityLog->log('course.deleted', $course);

        return response()->json(['message' => 'Deleted']);
    }

    public function toggleStatus(Course $course): JsonResponse
    {
        $this->authorizeInstitution($course->institution_id);
        $course->update(['status' => $course->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['status' => $course->status]);
    }

    private function validateData(Request $request, ?int $id = null, ?int $institutionId = null): array
    {
        $institutionId ??= $request->user()->institution_id;

        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->where(fn ($q) => $q->where('institution_id', $institutionId))->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'duration' => ['nullable', 'string', 'max:100'],
            'fees' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
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
