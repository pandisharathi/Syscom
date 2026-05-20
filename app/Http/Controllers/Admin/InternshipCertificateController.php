<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Mail\InternshipCertificateMail;
use App\Models\CertificateTemplate;
use App\Models\InternshipBatch;
use App\Models\InternshipCertificate;
use App\Models\InternshipCourse;
use App\Models\InternshipStudent;
use App\Models\CertificateVerificationLog;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InternshipCertificateController extends Controller
{
    use RespondsWithDataTables;
    public function __construct(private ActivityLogService $activityLog) {}

    public function templates(): View
    {
        $templates = CertificateTemplate::query()
            ->where('institution_id', $this->currentInstitutionId())
            ->orderByDesc('id')
            ->get();
        return view('admin.internship-certificates.templates.index', compact('templates'));
    }

    public function templateCreate(): View
    {
        return view('admin.internship-certificates.templates.create');
    }

    public function templateStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title_main' => ['required', 'string', 'max:100'],
            'title_sub' => ['required', 'string', 'max:100'],
            'left_signature_title' => ['required', 'string', 'max:100'],
            'right_signature_title' => ['required', 'string', 'max:100'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_position' => ['required', 'string', 'in:top-left,top-center,top-right'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'font_family' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable'],
            'show_program_coordinator' => ['nullable'],
            'show_certificate_id' => ['nullable'],
            'show_department' => ['nullable'],
            'left_signature_name' => ['nullable', 'string', 'max:255'],
            'right_signature_name' => ['nullable', 'string', 'max:255'],
            'show_left_signature_name' => ['nullable'],
            'show_right_signature_name' => ['nullable'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['show_program_coordinator'] = $request->boolean('show_program_coordinator');
        $data['show_certificate_id'] = $request->boolean('show_certificate_id');
        $data['show_department'] = $request->boolean('show_department');
        $data['show_left_signature_name'] = $request->boolean('show_left_signature_name');
        $data['show_right_signature_name'] = $request->boolean('show_right_signature_name');

        $data['institution_id'] = $this->currentInstitutionId();

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('certificate-templates', 'public');
        }

        $template = CertificateTemplate::query()->create($data);
        $this->activityLog->log('certificate_template.created', $template);

        return response()->json(['message' => 'Certificate template created', 'data' => $template]);
    }

    public function templateEdit(CertificateTemplate $certificate_template): View
    {
        $this->guardInstitution($certificate_template);
        return view('admin.internship-certificates.templates.edit', compact('certificate_template'));
    }

    public function templateUpdate(Request $request, CertificateTemplate $certificate_template): JsonResponse
    {
        $this->guardInstitution($certificate_template);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title_main' => ['required', 'string', 'max:100'],
            'title_sub' => ['required', 'string', 'max:100'],
            'left_signature_title' => ['required', 'string', 'max:100'],
            'right_signature_title' => ['required', 'string', 'max:100'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_position' => ['required', 'string', 'in:top-left,top-center,top-right'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'font_family' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable'],
            'show_program_coordinator' => ['nullable'],
            'show_certificate_id' => ['nullable'],
            'show_department' => ['nullable'],
            'left_signature_name' => ['nullable', 'string', 'max:255'],
            'right_signature_name' => ['nullable', 'string', 'max:255'],
            'show_left_signature_name' => ['nullable'],
            'show_right_signature_name' => ['nullable'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['show_program_coordinator'] = $request->boolean('show_program_coordinator');
        $data['show_certificate_id'] = $request->boolean('show_certificate_id');
        $data['show_department'] = $request->boolean('show_department');
        $data['show_left_signature_name'] = $request->boolean('show_left_signature_name');
        $data['show_right_signature_name'] = $request->boolean('show_right_signature_name');

        if ($request->hasFile('background_image')) {
            if ($certificate_template->background_image) {
                Storage::disk('public')->delete($certificate_template->background_image);
            }
            $data['background_image'] = $request->file('background_image')->store('certificate-templates', 'public');
        }

        $certificate_template->update($data);
        $this->activityLog->log('certificate_template.updated', $certificate_template);

        return response()->json(['message' => 'Certificate template updated']);
    }

    public function templateDestroy(CertificateTemplate $certificate_template): JsonResponse
    {
        $this->guardInstitution($certificate_template);
        if ($certificate_template->background_image) {
            Storage::disk('public')->delete($certificate_template->background_image);
        }
        $certificate_template->delete();
        $this->activityLog->log('certificate_template.deleted', $certificate_template);

        return response()->json(['message' => 'Template deleted']);
    }

    public function generateForm(): View
    {
        $institutionId = $this->currentInstitutionId();
        $courses = InternshipCourse::query()->where('institution_id', $institutionId)->where('status', 'active')->orderBy('name')->get();
        $batches = InternshipBatch::query()->where('institution_id', $institutionId)->where('status', 'active')->orderBy('name')->get();
        $templates = CertificateTemplate::query()
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('admin.internship-certificates.generate', compact('courses', 'batches', 'templates'));
    }

    public function generateStudents(Request $request): JsonResponse
    {
        $q = InternshipStudent::query()->with(['batch.course', 'institution'])
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('id');
        $this->filterInstitution($q);

        if ($courseId = $request->input('course_id')) {
            $q->whereHas('batch', fn($b) => $b->where('internship_course_id', $courseId));
        }
        if ($batchId = $request->input('batch_id')) {
            $q->where('internship_batch_id', $batchId);
        }

        return response()->json([
            'data' => $q->get()->map(fn($s) => [
                'id' => $s->id,
                'full_name' => $s->full_name,
                'email' => $s->email,
                'course' => $s->batch?->course?->name ?? '—',
                'batch' => $s->batch?->name ?? '—',
                'has_certificate' => $s->certificate()->exists(),
            ]),
        ]);
    }

    public function generateStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:internship_students,id'],
            'certificate_template_id' => ['nullable', 'exists:certificate_templates,id'],
            'issue_date' => ['required', 'date'],
            'completion_date' => ['nullable', 'date'],
            'internship_title' => ['nullable', 'string', 'max:255'],
        ]);

        $institutionId = $this->currentInstitutionId();
        $user = $request->user();
        $generated = [];

        foreach ($data['student_ids'] as $studentId) {
            $student = InternshipStudent::query()->with(['batch.course', 'institution'])->findOrFail($studentId);

            if ((int) $student->institution_id !== $institutionId) {
                continue;
            }

            if (InternshipCertificate::query()->where('internship_student_id', $studentId)->exists()) {
                continue;
            }

            $certNumber = 'CERT-' . strtoupper(Str::random(3)) . '-' . str_pad($student->id, 6, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');
            $token = Str::random(64);

            $cert = InternshipCertificate::query()->create([
                'institution_id' => $institutionId,
                'internship_student_id' => $studentId,
                'certificate_template_id' => $data['certificate_template_id'],
                'certificate_number' => $certNumber,
                'internship_title' => $data['internship_title'] ?: ($student->batch?->course?->name),
                'encrypted_token' => $token,
                'issue_date' => $data['issue_date'],
                'completion_date' => $data['completion_date'] ?: $student->batch?->end_date,
                'status' => 'active',
                'generated_by' => $user->id,
            ]);

            $this->activityLog->log('internship_certificate.generated', $cert);
            $generated[] = $certNumber;
        }

        return response()->json(['message' => count($generated) . ' certificate(s) generated', 'count' => count($generated)]);
    }

    public function index(): View
    {
        return view('admin.internship-certificates.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = InternshipCertificate::query()->with(['student.batch.course', 'template', 'generator'])->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['certificate_number', 'internship_title'],
            fn(InternshipCertificate $c) => [
                'id' => $c->id,
                'certificate_number' => $c->certificate_number,
                'student_name' => $c->student?->full_name ?? '—',
                'course' => $c->student?->batch?->course?->name ?? '—',
                'duration' => ($c->student?->batch?->number_of_days ?? '—') . ' Days',
                'issue_date' => $c->issue_date?->format('Y-m-d'),
                'qr_status' => $c->status === 'active' ? 'Active' : 'Revoked',
                'status' => $c->status,
            ]
        );
    }

    public function show(InternshipCertificate $internship_certificate): View
    {
        $this->guardInstitution($internship_certificate);
        $cert = $internship_certificate->load(['student.batch.course', 'student.institution', 'template', 'generator']);

        $qrSvg = $this->generateQrSvg($cert->verificationUrl());

        return view('admin.internship-certificates.show', compact('cert', 'qrSvg'));
    }

    public function downloadPdf(InternshipCertificate $internship_certificate)
    {
        $this->guardInstitution($internship_certificate);
        $cert = $internship_certificate->load(['student.batch.course', 'student.institution', 'template']);

        $qrSvg = $this->generateQrSvg($cert->verificationUrl());

        $pdf = Pdf::loadView('admin.internship-certificates.pdf', compact('cert', 'qrSvg'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $cert->certificate_number . '.pdf');
    }

    public function emailCertificate(InternshipCertificate $internship_certificate): JsonResponse
    {
        $this->guardInstitution($internship_certificate);
        $cert = $internship_certificate->load(['student.batch.course', 'student.institution', 'template']);

        if (!$cert->student->email) {
            return response()->json(['message' => 'Student email not found'], 422);
        }

        try {
            Mail::to($cert->student->email)->send(new InternshipCertificateMail($cert));
            $this->activityLog->log('internship_certificate.emailed', $cert);
            return response()->json(['message' => 'Certificate emailed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    public function regenerate(InternshipCertificate $internship_certificate): JsonResponse
    {
        $this->guardInstitution($internship_certificate);

        $internship_certificate->update([
            'encrypted_token' => Str::random(64),
            'status' => 'active',
        ]);

        $this->activityLog->log('internship_certificate.regenerated', $internship_certificate);

        return response()->json(['message' => 'Certificate regenerated', 'token' => $internship_certificate->encrypted_token]);
    }

    public function revoke(InternshipCertificate $internship_certificate): JsonResponse
    {
        $this->guardInstitution($internship_certificate);
        $internship_certificate->update(['status' => 'revoked']);
        $this->activityLog->log('internship_certificate.revoked', $internship_certificate);

        return response()->json(['message' => 'Certificate revoked']);
    }

    public function verificationLogs(): View
    {
        return view('admin.internship-certificates.verification-logs');
    }

    public function verificationLogsData(Request $request): JsonResponse
    {
        $q = CertificateVerificationLog::query()->with('certificate.student')->orderByDesc('verified_at');
        // Add institution filter if needed by joining with certificates
        $q->whereHas('certificate', function($query) {
            $this->filterInstitution($query);
        });

        if ($certId = $request->input('certificate_id')) {
            $q->where('internship_certificate_id', $certId);
        }

        return $this->dataTablesJson(
            $request,
            $q,
            ['ip_address', 'user_agent'],
            fn(CertificateVerificationLog $log) => [
                'id' => $log->id,
                'certificate_number' => $log->certificate?->certificate_number ?? '—',
                'student_name' => $log->certificate?->student?->full_name ?? '—',
                'ip_address' => $log->ip_address ?? '—',
                'verified_at' => $log->verified_at?->format('Y-m-d H:i:s'),
            ]
        );
    }

    private function generateQrSvg(string $url): string
    {
        try {
            return QrCode::size(120)
                ->format('svg')
                ->errorCorrection('M')
                ->generate($url);
        } catch (\Exception) {
            return '';
        }
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

    private function filterInstitution($q): void
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $q->where('institution_id', $user->institution_id);
        }
    }

    private function guardInstitution($model): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) return;
        $instId = $model instanceof \Illuminate\Database\Eloquent\Model ? (int) $model->institution_id : (int) $model;
        if ((int) $u->institution_id !== $instId) abort(403);
    }
}
