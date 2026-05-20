<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Batch;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    use AppliesInstitutionScope;

    public function index(Request $request): View
    {
        return view('admin.attendance-reports.index');
    }

    public function data(Request $request): JsonResponse
    {
        $type = $request->input('type', 'daily');
        $institutionId = $request->user()->institution_id;

        if ($type === 'student') {
            return $this->studentWise($request, $institutionId);
        }
        if ($type === 'batch') {
            return $this->batchWise($request, $institutionId);
        }
        if ($type === 'faculty') {
            return $this->facultyWise($request, $institutionId);
        }

        $q = Attendance::query()->with(['batch.course', 'batch.faculty', 'details'])->orderByDesc('attendance_date');
        if (! $request->user()->isSuperAdmin()) {
            $q->where('institution_id', $institutionId);
        }

        if ($type === 'weekly') {
            $q->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($type === 'monthly') {
            $q->whereMonth('attendance_date', now()->month)->whereYear('attendance_date', now()->year);
        } elseif ($type === 'daily' && $request->filled('date')) {
            $q->whereDate('attendance_date', $request->date('date'));
        }

        $rows = $q->limit(500)->get()->map(function (Attendance $a) {
            $present = $a->details->where('status', 'present')->count();
            $absent = $a->details->where('status', 'absent')->count();
            $total = max(1, $present + $absent);

            return [
                'date' => $a->attendance_date?->format('Y-m-d'),
                'batch' => $a->batch?->name,
                'course' => $a->batch?->course?->name,
                'faculty' => $a->batch?->faculty?->full_name,
                'present' => $present,
                'absent' => $absent,
                'percent' => round($present * 100 / $total, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    private function studentWise(Request $request, ?int $institutionId): JsonResponse
    {
        $q = AttendanceDetail::query()
            ->select([
                'attendance_details.student_id',
                DB::raw("SUM(CASE WHEN attendance_details.status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN attendance_details.status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
            ])
            ->join('attendances', 'attendances.id', '=', 'attendance_details.attendance_id')
            ->groupBy('attendance_details.student_id');

        if (! $request->user()->isSuperAdmin()) {
            $q->where('attendances.institution_id', $institutionId);
        }

        $rows = $q->get()->map(function ($row) {
            $student = Student::query()->find($row->student_id);
            $total = max(1, (int) $row->present_count + (int) $row->absent_count);

            return [
                'student' => $student?->full_name,
                'present' => (int) $row->present_count,
                'absent' => (int) $row->absent_count,
                'percent' => round((int) $row->present_count * 100 / $total, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    private function batchWise(Request $request, ?int $institutionId): JsonResponse
    {
        $q = DB::table('attendances')
            ->join('attendance_details', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->select([
                'attendances.batch_id',
                DB::raw("SUM(CASE WHEN attendance_details.status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN attendance_details.status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
            ])
            ->when(! $request->user()->isSuperAdmin(), fn ($qq) => $qq->where('attendances.institution_id', $institutionId))
            ->groupBy('attendances.batch_id');

        $rows = collect($q->get())->map(function ($row) {
            $batch = Batch::query()->find($row->batch_id);
            $total = max(1, (int) $row->present_count + (int) $row->absent_count);

            return [
                'batch' => $batch?->name,
                'present' => (int) $row->present_count,
                'absent' => (int) $row->absent_count,
                'percent' => round((int) $row->present_count * 100 / $total, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    private function facultyWise(Request $request, ?int $institutionId): JsonResponse
    {
        $q = DB::table('batches')
            ->join('attendances', 'attendances.batch_id', '=', 'batches.id')
            ->join('attendance_details', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->select([
                'batches.faculty_id',
                DB::raw("SUM(CASE WHEN attendance_details.status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN attendance_details.status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
            ])
            ->when(! $request->user()->isSuperAdmin(), fn ($qq) => $qq->where('batches.institution_id', $institutionId))
            ->whereNotNull('batches.faculty_id')
            ->groupBy('batches.faculty_id');

        $rows = collect($q->get())->map(function ($row) {
            $faculty = Faculty::query()->find($row->faculty_id);
            $total = max(1, (int) $row->present_count + (int) $row->absent_count);

            return [
                'faculty' => $faculty?->full_name,
                'present' => (int) $row->present_count,
                'absent' => (int) $row->absent_count,
                'percent' => round((int) $row->present_count * 100 / $total, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }
}
