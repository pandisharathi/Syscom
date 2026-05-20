<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseReportController;
use App\Http\Controllers\Admin\ExpenseTypeController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\InternshipAttendanceController;
use App\Http\Controllers\Admin\InternshipBatchController;
use App\Http\Controllers\Admin\InternshipCourseController;
use App\Http\Controllers\Admin\InternshipEnquiryController;
use App\Http\Controllers\Admin\InternshipPaymentController;
use App\Http\Controllers\Admin\InternshipReportController;
use App\Http\Controllers\Admin\InternshipStudentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicInternshipEnquiryController;
use App\Http\Controllers\PublicInternshipRegistrationController;
use App\Http\Controllers\Admin\InternshipCertificateController;
use App\Http\Controllers\PublicCertificateController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/reset-cache', function () {
    $exitCode = Artisan::call('optimize:clear');
    $output = Artisan::output();
    return response("<pre>$output</pre>");
})->name('reset-cache');

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::get('internship-enquiry/{code}', [PublicInternshipEnquiryController::class, 'show'])->name('public.internship-enquiry');
Route::post('internship-enquiry/{code}', [PublicInternshipEnquiryController::class, 'store'])->name('public.internship-enquiry.store');
Route::get('internship-register/{code}', [PublicInternshipRegistrationController::class, 'show'])->name('public.internship-register');
Route::post('internship-register/{code}', [PublicInternshipRegistrationController::class, 'store'])->name('public.internship-register.store');

Route::get('certificate/verify/{token}', [PublicCertificateController::class, 'verify'])->name('public.certificate.verify');

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
        Route::get('dashboard/charts', [DashboardController::class, 'charts'])->middleware('permission:dashboard.view')->name('dashboard.charts');

        Route::middleware('permission:institutions.manage')->group(function () {
            Route::get('institutions', [InstitutionController::class, 'index'])->name('institutions.index');
            Route::get('institutions/data', [InstitutionController::class, 'data'])->name('institutions.data');
            Route::get('institutions/create', [InstitutionController::class, 'create'])->name('institutions.create');
            Route::post('institutions', [InstitutionController::class, 'store'])->name('institutions.store');
            Route::get('institutions/{institution}/edit', [InstitutionController::class, 'edit'])->name('institutions.edit');
            Route::put('institutions/{institution}', [InstitutionController::class, 'update'])->name('institutions.update');
            Route::delete('institutions/{institution}', [InstitutionController::class, 'destroy'])->name('institutions.destroy');
            Route::post('institutions/{institution}/toggle-active', [InstitutionController::class, 'toggleActive'])->name('institutions.toggle-active');
        });

        Route::middleware(['permission:courses.manage', 'institution.module:students'])->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
            Route::get('courses/data', [CourseController::class, 'data'])->name('courses.data');
            Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
            Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
            Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
            Route::post('courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        });

        Route::middleware(['permission:batches.manage', 'institution.module:students'])->group(function () {
            Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
            Route::get('batches/data', [BatchController::class, 'data'])->name('batches.data');
            Route::post('batches', [BatchController::class, 'store'])->name('batches.store');
            Route::put('batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
            Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
            Route::post('batches/{batch}/toggle-status', [BatchController::class, 'toggleStatus'])->name('batches.toggle-status');
        });

        Route::middleware(['permission:faculty.manage', 'institution.module:students'])->group(function () {
            Route::get('faculties', [FacultyController::class, 'index'])->name('faculties.index');
            Route::get('faculties/data', [FacultyController::class, 'data'])->name('faculties.data');
            Route::post('faculties', [FacultyController::class, 'store'])->name('faculties.store');
            Route::put('faculties/{faculty}', [FacultyController::class, 'update'])->name('faculties.update');
            Route::delete('faculties/{faculty}', [FacultyController::class, 'destroy'])->name('faculties.destroy');
            Route::post('faculties/{faculty}/toggle-status', [FacultyController::class, 'toggleStatus'])->name('faculties.toggle-status');
        });

        Route::middleware(['permission:students.manage', 'institution.module:students'])->group(function () {
            Route::get('students', [StudentController::class, 'index'])->name('students.index');
            Route::get('students/data', [StudentController::class, 'data'])->name('students.data');
            Route::post('students', [StudentController::class, 'store'])->name('students.store');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::post('students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
            Route::post('students/{student}/sync-batches', [StudentController::class, 'syncBatches'])->name('students.sync-batches');
        });

        Route::middleware(['permission:attendance.manage', 'institution.module:attendance'])->group(function () {
            Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::get('attendances/batch/{batch}/mark', [AttendanceController::class, 'mark'])->name('attendances.mark');
            Route::post('attendances/batch/{batch}', [AttendanceController::class, 'store'])->name('attendances.store');
            Route::get('attendances/data', [AttendanceController::class, 'data'])->name('attendances.data');
        });

        Route::middleware(['permission:attendance.reports', 'institution.module:attendance'])->group(function () {
            Route::get('attendance-reports', [AttendanceReportController::class, 'index'])->name('attendance-reports.index');
            Route::get('attendance-reports/data', [AttendanceReportController::class, 'data'])->name('attendance-reports.data');
        });

        Route::middleware(['permission:users.manage', 'institution.module:users'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/data', [UserController::class, 'data'])->name('users.data');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:permissions.manage')->group(function () {
            Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
            Route::post('role-permissions/{role}', [RolePermissionController::class, 'update'])->name('role-permissions.update');
        });

        Route::middleware(['permission:internship.courses', 'institution.module:internship'])->group(function () {
            Route::get('internship-courses', [InternshipCourseController::class, 'index'])->name('internship-courses.index');
            Route::get('internship-courses/create', [InternshipCourseController::class, 'create'])->name('internship-courses.create');
            Route::get('internship-courses/data', [InternshipCourseController::class, 'data'])->name('internship-courses.data');
            Route::post('internship-courses', [InternshipCourseController::class, 'store'])->name('internship-courses.store');
            Route::get('internship-courses/{internship_course}', [InternshipCourseController::class, 'show'])->name('internship-courses.show');
            Route::get('internship-courses/{internship_course}/edit', [InternshipCourseController::class, 'edit'])->name('internship-courses.edit');
            Route::put('internship-courses/{internship_course}', [InternshipCourseController::class, 'update'])->name('internship-courses.update');
            Route::delete('internship-courses/{internship_course}', [InternshipCourseController::class, 'destroy'])->name('internship-courses.destroy');
            Route::post('internship-courses/{internship_course}/toggle-status', [InternshipCourseController::class, 'toggleStatus'])->name('internship-courses.toggle-status');
        });

        Route::middleware(['permission:internship.batches', 'institution.module:internship'])->group(function () {
            Route::get('internship-batches', [InternshipBatchController::class, 'index'])->name('internship-batches.index');
            Route::get('internship-batches/create', [InternshipBatchController::class, 'create'])->name('internship-batches.create');
            Route::get('internship-batches/data', [InternshipBatchController::class, 'data'])->name('internship-batches.data');
            Route::post('internship-batches', [InternshipBatchController::class, 'store'])->name('internship-batches.store');
            Route::get('internship-batches/{internship_batch}/edit', [InternshipBatchController::class, 'edit'])->name('internship-batches.edit');
            Route::put('internship-batches/{internship_batch}', [InternshipBatchController::class, 'update'])->name('internship-batches.update');
            Route::delete('internship-batches/{internship_batch}', [InternshipBatchController::class, 'destroy'])->name('internship-batches.destroy');
        });

        Route::middleware(['permission:internship.enquiries', 'institution.module:internship'])->group(function () {
            Route::get('internship-enquiries', [InternshipEnquiryController::class, 'index'])->name('internship-enquiries.index');
            Route::get('internship-enquiries/data', [InternshipEnquiryController::class, 'data'])->name('internship-enquiries.data');
            Route::get('internship-enquiries/{internship_enquiry}', [InternshipEnquiryController::class, 'show'])->name('internship-enquiries.show');
            Route::get('internship-enquiries/{internship_enquiry}/convert', [InternshipEnquiryController::class, 'convertForm'])->name('internship-enquiries.convert-form');
            Route::put('internship-enquiries/{internship_enquiry}', [InternshipEnquiryController::class, 'update'])->name('internship-enquiries.update');
            Route::post('internship-enquiries/{internship_enquiry}/convert', [InternshipEnquiryController::class, 'convert'])->name('internship-enquiries.convert');
        });

        Route::middleware(['permission:internship.students', 'institution.module:internship'])->group(function () {
            Route::get('internship-students', [InternshipStudentController::class, 'index'])->name('internship-students.index');
            Route::get('internship-students/create', [InternshipStudentController::class, 'create'])->name('internship-students.create');
            Route::get('internship-students/data', [InternshipStudentController::class, 'data'])->name('internship-students.data');
            Route::post('internship-students', [InternshipStudentController::class, 'store'])->name('internship-students.store');
            Route::get('internship-students/{internship_student}', [InternshipStudentController::class, 'show'])->name('internship-students.show');
            Route::get('internship-students/{internship_student}/edit', [InternshipStudentController::class, 'edit'])->name('internship-students.edit');
            Route::put('internship-students/{internship_student}', [InternshipStudentController::class, 'update'])->name('internship-students.update');
            Route::delete('internship-students/{internship_student}', [InternshipStudentController::class, 'destroy'])->name('internship-students.destroy');
        });

        Route::middleware(['permission:internship.attendance', 'institution.module:internship'])->group(function () {
            Route::get('internship-attendances', [InternshipAttendanceController::class, 'index'])->name('internship-attendances.index');
            Route::get('internship-attendances/batch/{internship_batch}/mark', [InternshipAttendanceController::class, 'mark'])->name('internship-attendances.mark');
            Route::post('internship-attendances/batch/{internship_batch}', [InternshipAttendanceController::class, 'store'])->name('internship-attendances.store');
            Route::get('internship-attendances/data', [InternshipAttendanceController::class, 'data'])->name('internship-attendances.data');
        });

        Route::middleware(['permission:internship.reports', 'institution.module:internship'])->group(function () {
            Route::get('internship-reports', [InternshipReportController::class, 'index'])->name('internship-reports.index');
            Route::get('internship-reports/data', [InternshipReportController::class, 'data'])->name('internship-reports.data');
        });

        Route::middleware(['permission:internship.payments', 'institution.module:internship'])->group(function () {
            Route::get('internship-payments', [InternshipPaymentController::class, 'index'])->name('internship-payments.index');
            Route::get('internship-payments/data', [InternshipPaymentController::class, 'data'])->name('internship-payments.data');
            Route::get('internship-payments/create', [InternshipPaymentController::class, 'create'])->name('internship-payments.create');
            Route::post('internship-payments', [InternshipPaymentController::class, 'store'])->name('internship-payments.store');
            Route::get('internship-payments/{internship_payment}/edit', [InternshipPaymentController::class, 'edit'])->name('internship-payments.edit');
            Route::put('internship-payments/{internship_payment}', [InternshipPaymentController::class, 'update'])->name('internship-payments.update');
            Route::delete('internship-payments/{internship_payment}', [InternshipPaymentController::class, 'destroy'])->name('internship-payments.destroy');
            Route::get('internship-payments/student/{internship_student}', [InternshipPaymentController::class, 'studentData'])->name('internship-payments.student-data');

            Route::get('internship-payments/students', [InternshipPaymentController::class, 'students'])->name('internship-payments.students');
            Route::get('internship-payments/students/data', [InternshipPaymentController::class, 'studentsData'])->name('internship-payments.students-data');
            Route::post('internship-payments/students/{internship_student}/pay', [InternshipPaymentController::class, 'studentPay'])->name('internship-payments.student-pay');
            Route::get('internship-payments/students/{internship_student}/certificate', [InternshipPaymentController::class, 'certificate'])->name('internship-payments.certificate');
        });

        Route::middleware(['permission:expense.types', 'institution.module:expense'])->group(function () {
            Route::get('expense-types', [ExpenseTypeController::class, 'index'])->name('expense-types.index');
            Route::get('expense-types/data', [ExpenseTypeController::class, 'data'])->name('expense-types.data');
            Route::post('expense-types', [ExpenseTypeController::class, 'store'])->name('expense-types.store');
            Route::put('expense-types/{expense_type}', [ExpenseTypeController::class, 'update'])->name('expense-types.update');
            Route::delete('expense-types/{expense_type}', [ExpenseTypeController::class, 'destroy'])->name('expense-types.destroy');
        });

        Route::middleware(['permission:expense.manage', 'institution.module:expense'])->group(function () {
            Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
            Route::get('expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
            Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
            Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
            Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::get('expenses/{expense}/attachments/{attachment}', [ExpenseController::class, 'downloadAttachment'])->name('expenses.attachments.download');
        });

        Route::middleware(['permission:expense.reports', 'institution.module:expense'])->group(function () {
            Route::get('expense-reports', [ExpenseReportController::class, 'index'])->name('expense-reports.index');
            Route::get('expense-reports/data', [ExpenseReportController::class, 'data'])->name('expense-reports.data');
        });

        Route::middleware(['permission:reports.view', 'institution.module:reports'])->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        });

        Route::middleware(['permission:settings.manage', 'institution.module:settings'])->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::put('settings/institution', [SettingsController::class, 'updateInstitution'])->name('settings.institution');
            Route::post('settings/enquiry-toggle', [SettingsController::class, 'toggleEnquiry'])->name('settings.enquiry-toggle');
        });

        Route::middleware(['permission:internship.certificates', 'institution.module:internship'])->group(function () {
            Route::get('internship-certificates/templates', [InternshipCertificateController::class, 'templates'])->name('internship-certificates.templates');
            Route::get('internship-certificates/templates/create', [InternshipCertificateController::class, 'templateCreate'])->name('internship-certificates.templates.create');
            Route::post('internship-certificates/templates', [InternshipCertificateController::class, 'templateStore'])->name('internship-certificates.templates.store');
            Route::get('internship-certificates/templates/{certificate_template}/edit', [InternshipCertificateController::class, 'templateEdit'])->name('internship-certificates.templates.edit');
            Route::put('internship-certificates/templates/{certificate_template}', [InternshipCertificateController::class, 'templateUpdate'])->name('internship-certificates.templates.update');
            Route::delete('internship-certificates/templates/{certificate_template}', [InternshipCertificateController::class, 'templateDestroy'])->name('internship-certificates.templates.destroy');

            Route::get('internship-certificates/generate', [InternshipCertificateController::class, 'generateForm'])->name('internship-certificates.generate');
            Route::get('internship-certificates/generate/students', [InternshipCertificateController::class, 'generateStudents'])->name('internship-certificates.generate-students');
            Route::post('internship-certificates/generate', [InternshipCertificateController::class, 'generateStore'])->name('internship-certificates.generate-store');

            Route::get('internship-certificates', [InternshipCertificateController::class, 'index'])->name('internship-certificates.index');
            Route::get('internship-certificates/data', [InternshipCertificateController::class, 'data'])->name('internship-certificates.data');
            Route::get('internship-certificates/{internship_certificate}', [InternshipCertificateController::class, 'show'])->name('internship-certificates.show');
            Route::get('internship-certificates/{internship_certificate}/download', [InternshipCertificateController::class, 'downloadPdf'])->name('internship-certificates.download');
            Route::post('internship-certificates/{internship_certificate}/email', [InternshipCertificateController::class, 'emailCertificate'])->name('internship-certificates.email');
            Route::put('internship-certificates/{internship_certificate}/regenerate', [InternshipCertificateController::class, 'regenerate'])->name('internship-certificates.regenerate');
            Route::put('internship-certificates/{internship_certificate}/revoke', [InternshipCertificateController::class, 'revoke'])->name('internship-certificates.revoke');

            Route::get('internship-certificates/verification-logs', [InternshipCertificateController::class, 'verificationLogs'])->name('internship-certificates.verification-logs');
            Route::get('internship-certificates/verification-logs/data', [InternshipCertificateController::class, 'verificationLogsData'])->name('internship-certificates.verification-logs-data');
        });
    });
});
