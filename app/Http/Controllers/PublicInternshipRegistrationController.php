<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InternshipCourse;
use App\Models\InternshipEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicInternshipRegistrationController extends Controller
{
    public function show(string $code): View|RedirectResponse
    {
        $institution = Institution::query()->where('code', $code)->where('is_active', true)->firstOrFail();
        if (! $institution->enquiry_enabled) {
            return redirect('/')->withErrors(['enquiry' => 'Registration is disabled for this institution.']);
        }

        $courses = InternshipCourse::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('public.internship-register', compact('institution', 'courses'));
    }

    public function store(Request $request, string $code): RedirectResponse
    {
        $institution = Institution::query()->where('code', $code)->where('is_active', true)->firstOrFail();
        abort_unless($institution->enquiry_enabled, 403);

        $data = $request->validate([
            'reg_no' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'educational_qualification' => ['nullable', 'string', 'max:191'],
            'college_name' => ['nullable', 'string', 'max:191'],
            'internship_course_id' => ['nullable', 'exists:internship_courses,id'],
            'interested_course_text' => ['nullable', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'preferred_timing' => ['nullable', 'string', 'max:191'],
            'message' => ['nullable', 'string'],
        ]);

        if (! empty($data['internship_course_id'])) {
            $belongs = InternshipCourse::query()
                ->where('id', $data['internship_course_id'])
                ->where('institution_id', $institution->id)
                ->exists();
            abort_unless($belongs, 422);
        }

        $data['institution_id'] = $institution->id;
        $data['status'] = 'new';

        InternshipEnquiry::query()->create($data);

        return back()->with('success', 'Registration submitted successfully. We will contact you shortly.');
    }
}
