<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\InternshipBatch;
use App\Models\InternshipStudent;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipStudentController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.internship-students.index');
    }

    public function create(): View
    {
        $batches = InternshipBatch::query()->where('status', 'active')->orderBy('name')->get();
        return view('admin.internship-students.create', compact('batches'));
    }

    public function edit(InternshipStudent $internship_student): View
    {
        $this->guardInstitution($internship_student->institution_id);
        $batches = InternshipBatch::query()->where('status', 'active')->orderBy('name')->get();
        return view('admin.internship-students.edit', compact('internship_student', 'batches'));
    }

    public function show(InternshipStudent $internship_student): View
    {
        $this->guardInstitution($internship_student->institution_id);
        $internship_student->load(['batch.course', 'enquiry']);
        return view('admin.internship-students.show', compact('internship_student'));
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipStudent::query()->with(['batch.course', 'payments'])->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['first_name', 'last_name', 'email', 'phone', 'status', 'reg_no'],
            fn (InternshipStudent $s) => [
                'id' => $s->id,
                'reg_no' => $s->reg_no,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'full_name' => $s->full_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'whatsapp_number' => $s->whatsapp_number,
                'gender' => $s->gender,
                'college_name' => $s->college_name,
                'city' => $s->city,
                'batch' => $s->batch?->name,
                'course' => $s->batch?->course?->name,
                'status' => $s->status,
                'photo_url' => $s->photo_url,
                'joining_date' => $s->joining_date?->format('Y-m-d'),
                'internship_batch_id' => $s->internship_batch_id,
                'total_paid' => $s->total_paid,
                'fees' => (float) ($s->batch?->course?->fees ?? 0),
                'has_paid' => $s->total_paid > 0,
                'payment_status' => $s->total_paid >= ($s->batch?->course?->fees ?? 0) ? 'paid' : ($s->total_paid > 0 ? 'partial' : 'pending'),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $this->currentInstitutionId();
        $data = $request->validate([
            'reg_no' => ['nullable', 'string', 'max:50'],
            'internship_batch_id' => ['required', 'exists:internship_batches,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('internship_students', 'email')->where(fn ($q) => $q->where('institution_id', $institutionId))],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'educational_qualification' => ['nullable', 'string', 'max:191'],
            'college_name' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive,completed,relieved'],
        ]);

        $batch = InternshipBatch::query()->findOrFail($data['internship_batch_id']);
        abort_unless((int) $batch->institution_id === $institutionId, 403);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('internship-students', 'public');
        }

        $data['institution_id'] = $institutionId;
        $row = InternshipStudent::query()->create($data);
        $this->activityLog->log('internship_student.created', $row);

        return response()->json(['message' => 'Student enrolled successfully', 'data' => $row]);
    }

    public function update(Request $request, InternshipStudent $internship_student): JsonResponse
    {
        $this->guardInstitution($internship_student->institution_id);
        $data = $request->validate([
            'reg_no' => ['nullable', 'string', 'max:50'],
            'internship_batch_id' => ['sometimes', 'exists:internship_batches,id'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'educational_qualification' => ['nullable', 'string', 'max:191'],
            'college_name' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive,completed,relieved'],
        ]);

        if ($request->hasFile('photo')) {
            if ($internship_student->photo) {
                Storage::disk('public')->delete($internship_student->photo);
            }
            $data['photo'] = $request->file('photo')->store('internship-students', 'public');
        }

        $internship_student->update($data);
        $this->activityLog->log('internship_student.updated', $internship_student);

        return response()->json(['message' => 'Student updated successfully']);
    }

    public function destroy(Request $request, InternshipStudent $internship_student): JsonResponse
    {
        $this->guardInstitution($internship_student->institution_id);
        if ($internship_student->photo) {
            Storage::disk('public')->delete($internship_student->photo);
        }
        $internship_student->delete();
        $this->activityLog->log('internship_student.deleted', $internship_student);

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
