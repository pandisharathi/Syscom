<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Controller;
use App\Models\AttendanceDetail;
use App\Models\Expense;
use App\Models\Institution;
use App\Models\InternshipAttendanceDetail;
use App\Models\InternshipEnquiry;
use App\Models\InternshipPayment;
use App\Models\InternshipStudent;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AppliesInstitutionScope;

    public function index(Request $request): View
    {
        return view('admin.dashboard');
    }

    public function charts(Request $request): JsonResponse
    {
        $user = $request->user();

        $institutionIds = null;
        if (! $user->isSuperAdmin()) {
            $institutionIds = [$user->institution_id];
        }

        $institutionQuery = Institution::query();
        if ($institutionIds) {
            $institutionQuery->whereIn('id', $institutionIds);
        }

        $studentsTotal = Student::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->count();

        $presentMonth = AttendanceDetail::query()
            ->when($institutionIds, fn ($q) => $q->whereHas('attendance', fn ($a) => $a->whereIn('institution_id', $institutionIds)))
            ->where('status', 'present')
            ->whereMonth('created_at', now()->month)
            ->count();

        $absentMonth = AttendanceDetail::query()
            ->when($institutionIds, fn ($q) => $q->whereHas('attendance', fn ($a) => $a->whereIn('institution_id', $institutionIds)))
            ->where('status', 'absent')
            ->whereMonth('created_at', now()->month)
            ->count();

        $expenseMonth = Expense::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('total_amount');

        $enquiryStatuses = InternshipEnquiry::query()
            ->select('status', DB::raw('count(*) as c'))
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->groupBy('status')
            ->pluck('c', 'status');

        $expenseByMonth = Expense::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->where('expense_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as ym, SUM(total_amount) as total')
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $intStudentsTotal = InternshipStudent::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->count();

        $intStudentsActive = InternshipStudent::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->where('status', 'active')
            ->count();

        $recentEnquiries = InternshipEnquiry::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->with('course')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'email' => $e->email,
                'course' => $e->course?->name ?? $e->interested_course_text,
                'status' => $e->status,
                'created_at' => $e->created_at?->diffForHumans(),
            ]);

        $paymentQuery = InternshipPayment::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds));

        $paymentsToday = (clone $paymentQuery)->whereDate('payment_date', today())->sum('amount');
        $paymentsWeek = (clone $paymentQuery)->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $paymentsMonth = (clone $paymentQuery)->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month)->sum('amount');
        $paymentsTotal = (clone $paymentQuery)->sum('amount');

        $intPresent = InternshipAttendanceDetail::query()
            ->whereHas('attendance', fn ($a) => $a->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds)))
            ->where('status', 'present')
            ->count();

        $intAbsent = InternshipAttendanceDetail::query()
            ->whereHas('attendance', fn ($a) => $a->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds)))
            ->where('status', 'absent')
            ->count();

        $totalFees = InternshipStudent::query()
            ->when($institutionIds, fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->whereIn('status', ['active', 'inactive'])
            ->whereHas('batch.course')
            ->get()
            ->sum(function($student) {
                return (float) ($student->batch?->course?->fees ?? 0);
            });

        $totalPending = $totalFees - $paymentsTotal;

        return response()->json([
            'cards' => [
                'institutions' => $user->isSuperAdmin() ? $institutionQuery->count() : null,
                'students' => $studentsTotal,
                'present_this_month' => $presentMonth,
                'absent_this_month' => $absentMonth,
                'expense_this_month' => (float) $expenseMonth,
                'internship_students' => $intStudentsTotal,
                'internship_active' => $intStudentsActive,
                'payment_today' => (float) $paymentsToday,
                'payment_week' => (float) $paymentsWeek,
                'payment_month' => (float) $paymentsMonth,
                'payment_total' => (float) $paymentsTotal,
                'payment_total_fees' => (float) $totalFees,
                'payment_pending' => (float) $totalPending,
                'int_present' => $intPresent,
                'int_absent' => $intAbsent,
            ],
            'enquiry_by_status' => $enquiryStatuses,
            'expense_trend' => $expenseByMonth,
            'recent_enquiries' => $recentEnquiries,
        ]);
    }
}
