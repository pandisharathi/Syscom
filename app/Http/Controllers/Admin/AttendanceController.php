<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Batch;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use AppliesInstitutionScope;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.attendances.index');
    }

    public function mark(Batch $batch): View
    {
        $this->authorizeBatch($batch);
        $students = $batch->students()->attendanceEligible()->orderBy('first_name')->get();

        return view('admin.attendances.mark', compact('batch', 'students'));
    }

    public function store(Request $request, Batch $batch): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeBatch($batch);
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'absent_student_ids' => ['array'],
            'absent_student_ids.*' => ['integer', 'exists:students,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = Attendance::query()->where('batch_id', $batch->id)->whereDate('attendance_date', $data['attendance_date'])->exists();
        if ($exists) {
            return $this->respondDuplicate($request);
        }

        $eligibleIds = $batch->students()->attendanceEligible()->pluck('students.id');

        DB::transaction(function () use ($batch, $data, $eligibleIds, $request) {
            $att = Attendance::query()->create([
                'institution_id' => $batch->institution_id,
                'batch_id' => $batch->id,
                'attendance_date' => $data['attendance_date'],
                'marked_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $absent = collect($data['absent_student_ids'] ?? [])->intersect($eligibleIds);

            foreach ($eligibleIds as $sid) {
                AttendanceDetail::query()->create([
                    'attendance_id' => $att->id,
                    'student_id' => $sid,
                    'status' => $absent->contains($sid) ? 'absent' : 'present',
                ]);
            }
        });

        $this->activityLog->log('attendance.marked', $batch, ['date' => $data['attendance_date']]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Attendance saved']);
        }

        return redirect()->route('admin.attendances.index')->with('success', 'Attendance saved');
    }

    public function data(Request $request): JsonResponse
    {
        $q = Attendance::query()->with('batch.course')->orderByDesc('attendance_date');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['notes'],
            function (Attendance $a) {
                return [
                    'id' => $a->id,
                    'batch' => $a->batch?->name ?? ('#'.$a->batch_id),
                    'course' => $a->batch?->course?->name,
                    'date' => $a->attendance_date?->format('Y-m-d'),
                    'batch_id' => $a->batch_id,
                ];
            },
            function (Builder $w, string $search) {
                $w->whereHas('batch', fn (Builder $b) => $b->where('name', 'like', '%'.$search.'%'));
            }
        );
    }

    private function authorizeBatch(Batch $batch): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) {
            return;
        }
        if ((int) $batch->institution_id !== (int) $u->institution_id) {
            abort(403);
        }
    }

    private function respondDuplicate(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Attendance already marked for this date.'], 422);
        }

        return back()->withErrors(['attendance_date' => 'Already marked for this date.'])->withInput();
    }
}
