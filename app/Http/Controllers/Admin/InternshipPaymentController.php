<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\InternshipBatch;
use App\Models\InternshipCourse;
use App\Models\InternshipPayment;
use App\Models\InternshipStudent;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipPaymentController extends Controller
{
    use AppliesInstitutionScope;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        $institutionId = $this->currentInstitutionId();

        // Calculate Total Expected Fees
        $totalFees = InternshipStudent::query()
            ->where('institution_id', $institutionId)
            ->whereIn('status', ['active', 'inactive'])
            ->whereHas('batch.course')
            ->get()
            ->sum(function($student) {
                return (float) ($student->batch?->course?->fees ?? 0);
            });

        // Calculate Total Collected Fees
        $totalCollected = InternshipPayment::query()
            ->where('institution_id', $institutionId)
            ->sum('amount');

        $totalPending = $totalFees - $totalCollected;

        return view('admin.internship-payments.index', compact('totalFees', 'totalCollected', 'totalPending'));
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipPayment::query()->with('student')->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['reference_no', 'payment_mode', 'notes'],
            fn (InternshipPayment $p) => [
                'id' => $p->id,
                'student' => $p->student?->full_name ?? '—',
                'student_id' => $p->internship_student_id,
                'amount' => (float) $p->amount,
                'payment_date' => $p->payment_date?->format('Y-m-d'),
                'payment_mode' => $p->payment_mode,
                'reference_no' => $p->reference_no ?? '—',
            ],
            function (Builder $w, string $search) {
                $w->whereHas('student', fn(Builder $q) => $q->whereRaw("first_name||' '||last_name like ?", ["%{$search}%"]));
            }
        );
    }

    public function create(): View
    {
        $students = InternshipStudent::query()
            ->where('institution_id', $this->currentInstitutionId())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        return view('admin.internship-payments.create', compact('students'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'internship_student_id' => ['required', 'exists:internship_students,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank_transfer,cheque,online,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $student = InternshipStudent::query()->findOrFail($data['internship_student_id']);
        $this->guardInstitution($student);

        $data['institution_id'] = $student->institution_id;
        $data['received_by'] = $request->user()->id;

        $payment = InternshipPayment::query()->create($data);
        $this->activityLog->log('internship_payment.created', $payment);

        return response()->json(['message' => 'Payment recorded', 'data' => $payment]);
    }

    public function edit(InternshipPayment $internship_payment): View
    {
        $this->guardInstitution($internship_payment->institution_id);
        $students = InternshipStudent::query()
            ->where('institution_id', $internship_payment->institution_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        return view('admin.internship-payments.edit', compact('internship_payment', 'students'));
    }

    public function update(Request $request, InternshipPayment $internship_payment): JsonResponse
    {
        $this->guardInstitution($internship_payment->institution_id);
        $data = $request->validate([
            'internship_student_id' => ['required', 'exists:internship_students,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank_transfer,cheque,online,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $student = InternshipStudent::query()->findOrFail($data['internship_student_id']);
        if ((int) $student->institution_id !== (int) $internship_payment->institution_id) {
            abort(422, 'Invalid student');
        }

        $internship_payment->update($data);
        $this->activityLog->log('internship_payment.updated', $internship_payment);

        return response()->json(['message' => 'Payment updated']);
    }

    public function studentData(InternshipStudent $internship_student): JsonResponse
    {
        $this->guardInstitution($internship_student);
        $payments = $internship_student->payments()->orderByDesc('payment_date')->get()->map(fn($p) => [
            'id' => $p->id,
            'amount' => (float) $p->amount,
            'payment_date' => $p->payment_date?->format('Y-m-d'),
            'payment_mode' => $p->payment_mode,
            'reference_no' => $p->reference_no,
            'notes' => $p->notes,
            'received_by' => $p->receiver?->name ?? '—',
        ]);
        return response()->json(['data' => $payments]);
    }

    public function destroy(InternshipPayment $internship_payment): JsonResponse
    {
        $this->guardInstitution($internship_payment->institution_id);
        $internship_payment->delete();
        $this->activityLog->log('internship_payment.deleted', $internship_payment);

        return response()->json(['message' => 'Payment deleted']);
    }

    public function students(): View
    {
        $courses = InternshipCourse::query()->where('status', 'active')->orderBy('name')->get();
        $batches = InternshipBatch::query()->where('status', 'active')->orderBy('name')->get();
        return view('admin.internship-payments.students', compact('courses', 'batches'));
    }

    public function studentsData(Request $request): JsonResponse
    {
        $q = InternshipStudent::query()->with(['batch.course', 'payments'])->whereIn('status', ['active', 'inactive'])->orderByDesc('id');
        $this->filterInstitution($q);

        if ($courseId = $request->input('course_id')) {
            $q->whereHas('batch', fn ($b) => $b->where('internship_course_id', $courseId));
        }

        if ($batchId = $request->input('batch_id')) {
            $q->where('internship_batch_id', $batchId);
        }

        return $this->dataTablesJson(
            $request,
            $q,
            ['first_name', 'last_name', 'email', 'phone'],
            fn (InternshipStudent $s) => [
                'id' => $s->id,
                'full_name' => $s->full_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'course' => $s->batch?->course?->name ?? '—',
                'batch' => $s->batch?->name ?? '—',
                'batch_id' => $s->internship_batch_id,
                'total_paid' => $s->total_paid,
                'fees' => (float) ($s->batch?->course?->fees ?? 0),
                'status' => $s->status,
                'has_paid' => $s->total_paid > 0,
                'payment_status' => $s->total_paid >= ($s->batch?->course?->fees ?? 0) ? 'paid' : ($s->total_paid > 0 ? 'partial' : 'pending'),
            ]
        );
    }

    public function studentPay(Request $request, InternshipStudent $internship_student): JsonResponse
    {
        $this->guardInstitution($internship_student->institution_id);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank_transfer,cheque,online,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['institution_id'] = $internship_student->institution_id;
        $data['internship_student_id'] = $internship_student->id;
        $data['received_by'] = $request->user()->id;

        $payment = InternshipPayment::query()->create($data);
        $this->activityLog->log('internship_payment.created', $payment);

        return response()->json(['message' => 'Payment recorded']);
    }

    public function certificate(InternshipStudent $internship_student): View
    {
        $this->guardInstitution($internship_student->institution_id);
        $internship_student->load(['batch.course', 'institution']);
        return view('admin.internship-payments.certificate', compact('internship_student'));
    }

    private function currentInstitutionId(): int
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $id = request()->input('institution_id');
            if ($id) return (int) $id;
            $inst = \App\Models\Institution::where('is_active', true)->first();
            abort_if(!$inst, 403);
            return (int) $inst->id;
        }
        abort_if(!$user->institution_id, 403);
        return (int) $user->institution_id;
    }

    private function guardInstitution($model): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) return;
        $instId = $model instanceof \Illuminate\Database\Eloquent\Model ? (int) $model->institution_id : (int) $model;
        if ((int) $u->institution_id !== $instId) abort(403);
    }
}
