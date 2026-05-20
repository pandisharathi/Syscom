<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\InternshipAttendance;
use App\Models\InternshipAttendanceDetail;
use App\Models\InternshipBatch;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternshipAttendanceController extends Controller
{
    use AppliesInstitutionScope;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.internship-attendances.index');
    }

    public function mark(InternshipBatch $internship_batch): View
    {
        $this->authorizeBatch($internship_batch);
        $students = $internship_batch->students()->attendanceEligible()->orderBy('first_name')->get();

        return view('admin.internship-attendances.mark', compact('internship_batch', 'students'));
    }

    public function store(Request $request, InternshipBatch $internship_batch): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeBatch($internship_batch);
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:present,absent'],
            'notes' => ['nullable', 'string'],
        ]);

        $eligibleIds = $internship_batch->students()->attendanceEligible()->pluck('id');

        $attendanceDate = Carbon::parse($data['attendance_date'])->startOfDay();

        DB::transaction(function () use ($internship_batch, $data, $eligibleIds, $request, $attendanceDate) {
            $att = InternshipAttendance::query()->updateOrCreate(
                [
                    'internship_batch_id' => $internship_batch->id,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'institution_id' => $internship_batch->institution_id,
                    'marked_by' => $request->user()->id,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            foreach ($data['status'] as $studentId => $status) {
                $studentId = (int) $studentId;
                if (! $eligibleIds->contains($studentId)) {
                    continue;
                }
                InternshipAttendanceDetail::query()->updateOrCreate(
                    [
                        'internship_attendance_id' => $att->id,
                        'internship_student_id' => $studentId,
                    ],
                    ['status' => $status]
                );
            }
        });

        $this->activityLog->log('internship_attendance.marked', $internship_batch, ['date' => $data['attendance_date']]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Attendance saved successfully']);
        }

        return redirect()->route('admin.internship-attendances.index')->with('success', 'Attendance saved');
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipAttendance::query()->with(['batch.course'])->orderByDesc('attendance_date');
        $this->filterInstitution($q);

        if ($request->filled('batch_id')) {
            $q->where('internship_batch_id', $request->input('batch_id'));
        }
        if ($request->filled('from')) {
            $q->whereDate('attendance_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('attendance_date', '<=', $request->input('to'));
        }

        return $this->dataTablesJson(
            $request,
            $q,
            ['notes'],
            function (InternshipAttendance $a) {
                $present = $a->details()->where('status', 'present')->count();
                $absent = $a->details()->where('status', 'absent')->count();
                return [
                    'id' => $a->id,
                    'batch' => $a->batch?->name,
                    'course' => $a->batch?->course?->name,
                    'date' => $a->attendance_date?->format('Y-m-d'),
                    'present_count' => $present,
                    'absent_count' => $absent,
                    'internship_batch_id' => $a->internship_batch_id,
                ];
            },
            function (Builder $w, string $search) {
                $w->whereHas('batch', fn(Builder $b) => $b->where('name', 'like', '%'.$search.'%'));
            }
        );
    }

    private function authorizeBatch(InternshipBatch $batch): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) {
            return;
        }
        if ((int) $batch->institution_id !== (int) $u->institution_id) {
            abort(403);
        }
    }
}
