<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\InternshipBatch;
use App\Models\InternshipCourse;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipBatchController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.internship-batches.index');
    }

    public function create(): View
    {
        $courses = InternshipCourse::query()->where('status', 'active')->orderBy('name')->get();
        $faculties = Faculty::query()->where('status', 'active')->orderBy('first_name')->get();
        return view('admin.internship-batches.create', compact('courses', 'faculties'));
    }

    public function edit(InternshipBatch $internship_batch): View
    {
        $this->guardInstitution($internship_batch->institution_id);
        $courses = InternshipCourse::query()->where('status', 'active')->orderBy('name')->get();
        $faculties = Faculty::query()->where('status', 'active')->orderBy('first_name')->get();
        return view('admin.internship-batches.edit', compact('internship_batch', 'courses', 'faculties'));
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipBatch::query()->with(['course', 'faculty'])->withCount('students')->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['name', 'timing', 'status'],
            fn (InternshipBatch $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'course' => $b->course?->name,
                'faculty' => $b->faculty?->full_name,
                'start_date' => $b->start_date?->format('Y-m-d'),
                'end_date' => $b->end_date?->format('Y-m-d'),
                'timing' => $b->timing,
                'capacity' => $b->capacity,
                'students_count' => $b->students_count,
                'number_of_days' => $b->number_of_days,
                'status' => $b->status,
                'internship_course_id' => $b->internship_course_id,
                'faculty_id' => $b->faculty_id,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'internship_course_id' => ['required', 'exists:internship_courses,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'name' => ['nullable', 'string', 'max:191'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'timing' => ['nullable', 'string', 'max:191'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'number_of_days' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,inactive,completed'],
        ]);

        $course = InternshipCourse::query()->findOrFail($data['internship_course_id']);
        abort_unless((int) $course->institution_id === $this->currentInstitutionId(), 403);

        if (! empty($data['faculty_id'])) {
            $faculty = Faculty::query()->findOrFail($data['faculty_id']);
            abort_unless((int) $faculty->institution_id === $this->currentInstitutionId(), 403);
        }

        $data['institution_id'] = $this->currentInstitutionId();
        $row = InternshipBatch::query()->create($data);
        $this->activityLog->log('internship_batch.created', $row);

        return response()->json(['message' => 'Batch created successfully', 'data' => $row]);
    }

    public function update(Request $request, InternshipBatch $internship_batch): JsonResponse
    {
        $this->guardInstitution($internship_batch->institution_id);
        $data = $request->validate([
            'internship_course_id' => ['sometimes', 'exists:internship_courses,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'name' => ['nullable', 'string', 'max:191'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'timing' => ['nullable', 'string', 'max:191'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'number_of_days' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,inactive,completed'],
        ]);

        $internship_batch->update($data);
        $this->activityLog->log('internship_batch.updated', $internship_batch);

        return response()->json(['message' => 'Batch updated successfully']);
    }

    public function destroy(Request $request, InternshipBatch $internship_batch): JsonResponse
    {
        $this->guardInstitution($internship_batch->institution_id);
        $internship_batch->delete();
        $this->activityLog->log('internship_batch.deleted', $internship_batch);

        return response()->json(['message' => 'Deleted']);
    }

    private function guardInstitution(?int $institutionId): void
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
