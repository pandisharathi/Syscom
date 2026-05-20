<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InternshipCourse;
use App\Models\InternshipEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicInternshipEnquiryController extends Controller
{
    public function show(string $code): View|RedirectResponse
    {
        $institution = Institution::query()->where('code', $code)->where('is_active', true)->firstOrFail();
        if (! $institution->enquiry_enabled) {
            return redirect('/')->withErrors(['enquiry' => 'Enquiry is disabled for this institution.']);
        }

        $courses = InternshipCourse::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('public.internship-enquiry', compact('institution', 'courses'));
    }

    public function store(Request $request, string $code): RedirectResponse
    {
        $institution = Institution::query()->where('code', $code)->where('is_active', true)->firstOrFail();
        abort_unless($institution->enquiry_enabled, 403);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'educational_qualification' => ['nullable', 'string', 'max:191'],
            'college_name' => ['nullable', 'string', 'max:191'],
            'gender' => ['nullable', 'in:male,female,other'],
            'internship_course_id' => ['nullable', 'exists:internship_courses,id'],
            'interested_course_text' => ['nullable', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'preferred_timing' => ['nullable', 'string', 'max:191'],
            'message' => ['nullable', 'string'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:5120'],
        ]);

        if (! empty($data['internship_course_id'])) {
            $belongs = InternshipCourse::query()
                ->where('id', $data['internship_course_id'])
                ->where('institution_id', $institution->id)
                ->exists();
            abort_unless($belongs, 422);
        }

        if ($request->hasFile('resume')) {
            $data['resume_path'] = $request->file('resume')->store("enquiries/{$institution->code}", 'public');
        }
        unset($data['resume']);

        $data['institution_id'] = $institution->id;
        $data['status'] = 'new';

        InternshipEnquiry::query()->create($data);

        return back()->with('success', 'Thank you — we have received your enquiry.');
    }
}
