<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Student;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.students.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = Student::query()->with('batches')->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['first_name', 'last_name', 'email', 'phone', 'status'],
            fn (Student $s) => [
                'id' => $s->id,
                'first_name' => $s->first_name,
                'last_name' => $s->last_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'gender' => $s->gender,
                'status' => $s->status,
                'batches' => $s->batches->pluck('id')->values()->all(),
                'photo_url' => $s->photo ? Storage::disk('public')->url($s->photo) : null,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $data['institution_id'] = $this->currentInstitutionId();
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }
        $student = Student::query()->create($data);
        $this->activityLog->log('student.created', $student);

        return response()->json(['message' => 'Saved', 'data' => $student]);
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $this->authorizeInstitution($student->institution_id);
        $data = $this->validateData($request, false);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }
        $student->update($data);
        $this->activityLog->log('student.updated', $student);

        return response()->json(['message' => 'Updated', 'data' => $student]);
    }

    public function destroy(Request $request, Student $student): JsonResponse
    {
        $this->authorizeInstitution($student->institution_id);
        $student->delete();
        $this->activityLog->log('student.deleted', $student);

        return response()->json(['message' => 'Deleted']);
    }

    public function toggleStatus(Student $student): JsonResponse
    {
        $this->authorizeInstitution($student->institution_id);
        $order = ['active', 'inactive', 'relieved'];
        $idx = array_search($student->status, $order, true);
        $next = $order[($idx === false ? 0 : ($idx + 1) % count($order))];
        $student->update(['status' => $next]);

        return response()->json(['status' => $student->status]);
    }

    public function syncBatches(Request $request, Student $student): JsonResponse
    {
        $this->authorizeInstitution($student->institution_id);
        $ids = $request->validate(['batch_ids' => ['array'], 'batch_ids.*' => ['integer', 'exists:batches,id']])['batch_ids'] ?? [];
        $valid = Batch::query()->whereIn('id', $ids)->where('institution_id', $student->institution_id)->pluck('id');
        $student->batches()->sync($valid);
        $this->activityLog->log('student.batches_synced', $student, ['batch_ids' => $valid->all()]);

        return response()->json(['message' => 'Batches updated']);
    }

    private function validateData(Request $request, bool $create = true): array
    {
        return $request->validate([
            'first_name' => [$create ? 'required' : 'sometimes', 'string', 'max:100'],
            'last_name' => [$create ? 'required' : 'sometimes', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive,relieved'],
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
