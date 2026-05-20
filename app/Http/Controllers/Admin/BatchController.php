<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Faculty;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.batches.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = Batch::query()->with(['course', 'faculty'])->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['name', 'timing', 'status'],
            fn (Batch $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'course' => $b->course?->name,
                'faculty' => $b->faculty?->full_name,
                'start_date' => $b->start_date?->format('Y-m-d'),
                'end_date' => $b->end_date?->format('Y-m-d'),
                'timing' => $b->timing,
                'number_of_days' => $b->number_of_days,
                'status' => $b->status,
                'course_id' => $b->course_id,
                'faculty_id' => $b->faculty_id,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $course = Course::query()->findOrFail($data['course_id']);
        abort_unless((int) $course->institution_id === $this->currentInstitutionId(), 403);
        if (! empty($data['faculty_id'])) {
            $faculty = Faculty::query()->findOrFail($data['faculty_id']);
            abort_unless((int) $faculty->institution_id === $this->currentInstitutionId(), 403);
        }
        $data['institution_id'] = $this->currentInstitutionId();
        $batch = Batch::query()->create($data);
        $this->activityLog->log('batch.created', $batch);

        return response()->json(['message' => 'Saved', 'data' => $batch]);
    }

    public function update(Request $request, Batch $batch): JsonResponse
    {
        $this->authorizeInstitution($batch->institution_id);
        $batch->update($this->validateData($request));
        $this->activityLog->log('batch.updated', $batch);

        return response()->json(['message' => 'Updated', 'data' => $batch]);
    }

    public function destroy(Request $request, Batch $batch): JsonResponse
    {
        $this->authorizeInstitution($batch->institution_id);
        $batch->delete();
        $this->activityLog->log('batch.deleted', $batch);

        return response()->json(['message' => 'Deleted']);
    }

    public function toggleStatus(Batch $batch): JsonResponse
    {
        $this->authorizeInstitution($batch->institution_id);
        $batch->update(['status' => $batch->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['status' => $batch->status]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'name' => ['nullable', 'string', 'max:191'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'timing' => ['nullable', 'string', 'max:191'],
            'number_of_days' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,inactive,completed'],
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
