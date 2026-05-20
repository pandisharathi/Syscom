<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\InternshipBatch;
use App\Models\InternshipCourse;
use App\Models\InternshipEnquiry;
use App\Models\InternshipStudent;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipEnquiryController extends Controller
{
    use AppliesInstitutionScope;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.internship-enquiries.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipEnquiry::query()->with('course')->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['first_name', 'last_name', 'email', 'status', 'city'],
            fn (InternshipEnquiry $e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'email' => $e->email,
                'phone' => $e->contact_number,
                'course' => $e->course?->name ?? $e->interested_course_text,
                'status' => $e->status,
                'city' => $e->city,
                'created_at' => $e->created_at?->format('Y-m-d H:i'),
                'resume_url' => $e->resume_path ? asset('storage/'.$e->resume_path) : null,
            ]
        );
    }

    public function update(Request $request, InternshipEnquiry $internship_enquiry): JsonResponse
    {
        $this->guardInstitution($internship_enquiry->institution_id);
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,interested,enrolled,rejected'],
            'internship_course_id' => ['nullable', 'exists:internship_courses,id'],
        ]);
        if (! empty($data['internship_course_id'])) {
            abort_unless(
                InternshipCourse::query()
                    ->where('id', $data['internship_course_id'])
                    ->where('institution_id', $internship_enquiry->institution_id)
                    ->exists(),
                422
            );
        }
        $internship_enquiry->update($data);
        $this->activityLog->log('internship_enquiry.updated', $internship_enquiry);

        return response()->json(['message' => 'Updated']);
    }

    public function show(InternshipEnquiry $internship_enquiry): View
    {
        $this->guardInstitution($internship_enquiry->institution_id);
        $courses = InternshipCourse::query()
            ->where('institution_id', $internship_enquiry->institution_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $batches = InternshipBatch::query()->with('course')
            ->where('institution_id', $internship_enquiry->institution_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $internship_enquiry->load('course');
        return view('admin.internship-enquiries.show', compact('internship_enquiry', 'courses', 'batches'));
    }

    public function convertForm(InternshipEnquiry $internship_enquiry): View
    {
        $this->guardInstitution($internship_enquiry->institution_id);
        $batches = InternshipBatch::query()->with('course')
            ->where('institution_id', $internship_enquiry->institution_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        return view('admin.internship-enquiries.convert', compact('internship_enquiry', 'batches'));
    }

    public function convert(Request $request, InternshipEnquiry $internship_enquiry): JsonResponse
    {
        $this->guardInstitution($internship_enquiry->institution_id);
        $data = $request->validate([
            'internship_batch_id' => ['required', 'exists:internship_batches,id'],
        ]);
        $batch = InternshipBatch::query()->findOrFail($data['internship_batch_id']);
        if ((int) $batch->institution_id !== (int) $internship_enquiry->institution_id) {
            abort(422, 'Invalid batch');
        }

        $student = InternshipStudent::query()->create([
            'institution_id' => $internship_enquiry->institution_id,
            'internship_batch_id' => $batch->id,
            'internship_enquiry_id' => $internship_enquiry->id,
            'first_name' => $internship_enquiry->first_name,
            'last_name' => $internship_enquiry->last_name,
            'email' => $internship_enquiry->email,
            'phone' => $internship_enquiry->contact_number,
            'gender' => $internship_enquiry->gender,
            'address' => trim(($internship_enquiry->city ?? '').', '.($internship_enquiry->state ?? '')),
            'status' => 'active',
        ]);

        $internship_enquiry->update(['status' => 'enrolled', 'internship_course_id' => $batch->internship_course_id]);
        $this->activityLog->log('internship_enquiry.converted', $internship_enquiry, ['student_id' => $student->id]);

        return response()->json(['message' => 'Converted', 'student_id' => $student->id]);
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
