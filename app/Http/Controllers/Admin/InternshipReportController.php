<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Controller;
use App\Models\InternshipAttendance;
use App\Models\InternshipAttendanceDetail;
use App\Models\InternshipBatch;
use App\Models\InternshipCourse;
use App\Models\InternshipEnquiry;
use App\Models\InternshipStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternshipReportController extends Controller
{
    use AppliesInstitutionScope;

    public function index(): View
    {
        $user = auth()->user();
        $courses = InternshipCourse::query()
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('institution_id', $user->institution_id))
            ->orderBy('name')->get(['id', 'code', 'name']);
        $batches = InternshipBatch::query()
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('institution_id', $user->institution_id))
            ->orderBy('name')->get(['id', 'name']);
        return view('admin.internship-reports.index', compact('courses', 'batches'));
    }

    public function data(Request $request): JsonResponse
    {
        $type = $request->input('type', 'active_students');
        $user = $request->user();
        $institutionId = $user->institution_id;

        $courseId = $request->input('course_id');
        $batchId = $request->input('batch_id');
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        if ($type === 'active_students') {
            $q = InternshipStudent::query()->with('batch.course')
                ->where('status', 'active')
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($courseId, fn($qq) => $qq->whereHas('batch', fn($b) => $b->where('internship_course_id', $courseId)))
                ->when($batchId, fn($qq) => $qq->where('internship_batch_id', $batchId))
                ->when($from, fn($qq) => $qq->whereDate('joining_date', '>=', $from))
                ->when($to, fn($qq) => $qq->whereDate('joining_date', '<=', $to))
                ->orderBy('first_name');

            $rows = $q->get()->map(fn($s) => [
                'full_name' => $s->full_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'batch' => $s->batch?->name,
                'course' => $s->batch?->course?->name,
                'college_name' => $s->college_name,
                'status' => $s->status,
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'completed_students') {
            $q = InternshipStudent::query()->with('batch.course')
                ->whereIn('status', ['completed', 'relieved'])
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($courseId, fn($qq) => $qq->whereHas('batch', fn($b) => $b->where('internship_course_id', $courseId)))
                ->when($batchId, fn($qq) => $qq->where('internship_batch_id', $batchId))
                ->when($status, fn($qq) => $qq->where('status', $status))
                ->orderBy('first_name');

            $rows = $q->get()->map(fn($s) => [
                'full_name' => $s->full_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'batch' => $s->batch?->name,
                'course' => $s->batch?->course?->name,
                'college_name' => $s->college_name,
                'status' => $s->status,
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'enrollment') {
            $q = InternshipBatch::query()->with('course')->withCount('students')
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($courseId, fn($qq) => $qq->where('internship_course_id', $courseId))
                ->when($batchId, fn($qq) => $qq->where('id', $batchId))
                ->orderBy('name');

            $rows = $q->get()->map(fn($b) => [
                'batch' => $b->name,
                'course' => $b->course?->name,
                'students' => (int) $b->students_count,
                'capacity' => $b->capacity ?? '—',
                'status' => $b->status,
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'course_wise') {
            $q = InternshipCourse::query()->withCount('batches')
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($courseId, fn($qq) => $qq->where('id', $courseId))
                ->orderBy('name');

            $rows = $q->get()->map(function ($c) {
                $studentCount = InternshipStudent::query()->whereHas('batch', fn($b) => $b->where('internship_course_id', $c->id))->count();
                return [
                    'course' => $c->name,
                    'batches' => (int) $c->batches_count,
                    'students' => $studentCount,
                    'status' => $c->status,
                ];
            });

            return response()->json(['data' => $rows]);
        }

        if ($type === 'batch_wise') {
            $q = InternshipBatch::query()->with('course')->withCount('students')
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($courseId, fn($qq) => $qq->where('internship_course_id', $courseId))
                ->when($batchId, fn($qq) => $qq->where('id', $batchId))
                ->when($status, fn($qq) => $qq->where('status', $status))
                ->orderBy('name');

            $rows = $q->get()->map(fn($b) => [
                'batch' => $b->name,
                'course' => $b->course?->name,
                'timing' => $b->timing,
                'students' => (int) $b->students_count,
                'status' => $b->status,
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'present_details') {
            $q = InternshipAttendanceDetail::query()->with(['student', 'attendance.batch.course'])
                ->where('status', 'present')
                ->whereHas('attendance', function ($qq) use ($user, $institutionId, $batchId, $from, $to) {
                    $qq->when(!$user->isSuperAdmin(), fn($w) => $w->where('institution_id', $institutionId))
                        ->when($batchId, fn($w) => $w->where('internship_batch_id', $batchId))
                        ->when($from, fn($w) => $w->whereDate('attendance_date', '>=', $from))
                        ->when($to, fn($w) => $w->whereDate('attendance_date', '<=', $to));
                })->orderByDesc(
                    InternshipAttendance::select('attendance_date')
                        ->whereColumn('id', 'internship_attendance_details.internship_attendance_id')
                        ->limit(1)
                );

            $rows = $q->get()->map(fn($d) => [
                'student_name' => $d->student?->full_name ?? '—',
                'email' => $d->student?->email ?? '—',
                'phone' => $d->student?->phone ?? '—',
                'batch' => $d->attendance?->batch?->name ?? '—',
                'course' => $d->attendance?->batch?->course?->name ?? '—',
                'date' => $d->attendance?->attendance_date?->format('Y-m-d'),
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'absent_details') {
            $q = InternshipAttendanceDetail::query()->with(['student', 'attendance.batch.course'])
                ->where('status', 'absent')
                ->whereHas('attendance', function ($qq) use ($user, $institutionId, $batchId, $from, $to) {
                    $qq->when(!$user->isSuperAdmin(), fn($w) => $w->where('institution_id', $institutionId))
                        ->when($batchId, fn($w) => $w->where('internship_batch_id', $batchId))
                        ->when($from, fn($w) => $w->whereDate('attendance_date', '>=', $from))
                        ->when($to, fn($w) => $w->whereDate('attendance_date', '<=', $to));
                })->orderByDesc(
                    InternshipAttendance::select('attendance_date')
                        ->whereColumn('id', 'internship_attendance_details.internship_attendance_id')
                        ->limit(1)
                );

            $rows = $q->get()->map(fn($d) => [
                'student_name' => $d->student?->full_name ?? '—',
                'email' => $d->student?->email ?? '—',
                'phone' => $d->student?->phone ?? '—',
                'batch' => $d->attendance?->batch?->name ?? '—',
                'course' => $d->attendance?->batch?->course?->name ?? '—',
                'date' => $d->attendance?->attendance_date?->format('Y-m-d'),
            ]);

            return response()->json(['data' => $rows]);
        }

        if ($type === 'attendance') {
            $q = InternshipAttendance::query()->with('batch.course')
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->when($batchId, fn($qq) => $qq->where('internship_batch_id', $batchId))
                ->when($from, fn($qq) => $qq->whereDate('attendance_date', '>=', $from))
                ->when($to, fn($qq) => $qq->whereDate('attendance_date', '<=', $to))
                ->orderByDesc('attendance_date');

            $rows = $q->get()->map(function ($a) {
                $present = $a->details()->where('status', 'present')->count();
                $absent = $a->details()->where('status', 'absent')->count();
                return [
                    'batch' => $a->batch?->name,
                    'course' => $a->batch?->course?->name,
                    'date' => $a->attendance_date?->format('Y-m-d'),
                    'present' => $present,
                    'absent' => $absent,
                    'total' => $present + $absent,
                ];
            });

            return response()->json(['data' => $rows]);
        }

        if ($type === 'gender') {
            $q = InternshipStudent::query()
                ->select('gender', DB::raw('count(*) as c'))
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->whereNotNull('gender')
                ->groupBy('gender');

            return response()->json(['data' => $q->get()]);
        }

        if ($type === 'college') {
            $q = InternshipStudent::query()
                ->select('college_name', DB::raw('count(*) as c'))
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->whereNotNull('college_name')
                ->groupBy('college_name')
                ->orderByDesc('c')
                ->limit(50);

            return response()->json(['data' => $q->get()]);
        }

        if ($type === 'enquiry') {
            $q = InternshipEnquiry::query()
                ->select('status', DB::raw('count(*) as c'))
                ->when(!$user->isSuperAdmin(), fn($qq) => $qq->where('institution_id', $institutionId))
                ->groupBy('status');

            return response()->json(['data' => $q->get()]);
        }

        return response()->json(['data' => []]);
    }
}
