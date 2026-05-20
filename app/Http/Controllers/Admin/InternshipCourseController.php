<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\InternshipCourse;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipCourseController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.internship-courses.index');
    }

    public function create(): View
    {
        return view('admin.internship-courses.create');
    }

    public function edit(InternshipCourse $internship_course): View
    {
        $this->guardInstitution($internship_course->institution_id);
        return view('admin.internship-courses.edit', compact('internship_course'));
    }

    public function show(InternshipCourse $internship_course): View
    {
        $this->guardInstitution($internship_course->institution_id);
        return view('admin.internship-courses.show', compact('internship_course'));
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipCourse::query()->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['code', 'name', 'status'],
            fn (InternshipCourse $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'duration' => $c->duration,
                'fees' => (float) $c->fees,
                'start_date' => $c->start_date?->format('Y-m-d'),
                'end_date' => $c->end_date?->format('Y-m-d'),
                'status' => $c->status,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['institution_id'] = $this->currentInstitutionId();

        if ($request->hasFile('course_image')) {
            $data['course_image'] = $request->file('course_image')->store('internship-courses', 'public');
        }

        $row = InternshipCourse::query()->create($data);
        $this->activityLog->log('internship_course.created', $row);

        return response()->json(['message' => 'Course created successfully', 'data' => $row]);
    }

    public function update(Request $request, InternshipCourse $internship_course): JsonResponse
    {
        $this->guardInstitution($internship_course->institution_id);
        $data = $this->validated($request, $internship_course->id, $internship_course->institution_id);

        if ($request->hasFile('course_image')) {
            if ($internship_course->course_image) {
                Storage::disk('public')->delete($internship_course->course_image);
            }
            $data['course_image'] = $request->file('course_image')->store('internship-courses', 'public');
        }

        $internship_course->update($data);
        $this->activityLog->log('internship_course.updated', $internship_course);

        return response()->json(['message' => 'Course updated successfully', 'data' => $internship_course]);
    }

    public function destroy(Request $request, InternshipCourse $internship_course): JsonResponse
    {
        $this->guardInstitution($internship_course->institution_id);
        if ($internship_course->course_image) {
            Storage::disk('public')->delete($internship_course->course_image);
        }
        $internship_course->delete();
        $this->activityLog->log('internship_course.deleted', $internship_course);

        return response()->json(['message' => 'Deleted']);
    }

    public function toggleStatus(InternshipCourse $internship_course): JsonResponse
    {
        $this->guardInstitution($internship_course->institution_id);
        $internship_course->update(['status' => $internship_course->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['status' => $internship_course->status]);
    }

    private function validated(Request $request, ?int $id = null, ?int $institutionId = null): array
    {
        $institutionId ??= $request->user()->institution_id;

        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('internship_courses', 'code')->where(fn ($q) => $q->where('institution_id', $institutionId))->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'duration' => ['nullable', 'string', 'max:100'],
            'fees' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'course_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
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
