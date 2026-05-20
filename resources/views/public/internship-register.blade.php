<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Internship Registration — {{ $institution->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:860px;">
    <div class="text-center mb-4">
        <h3 class="fw-semibold">{{ $institution->name }}</h3>
        <p class="text-muted mb-0">Internship student registration</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <form method="post" action="{{ route('public.internship-register.store', $institution->code) }}">
                @csrf
                <h6 class="fw-semibold text-muted mb-3"><i class="fa-regular fa-user me-1"></i> Personal Details</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="form-label">First name <span class="text-danger">*</span></label><input name="first_name" class="form-control" required value="{{ old('first_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Last name <span class="text-danger">*</span></label><input name="last_name" class="form-control" required value="{{ old('last_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
                    <div class="col-md-6"><label class="form-label">Date of birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}"></div>
                    <div class="col-md-6"><label class="form-label">Gender</label>
                        <select name="gender" class="form-select"><option value="">—</option><option value="male" @selected(old('gender')=='male')>Male</option><option value="female" @selected(old('gender')=='female')>Female</option><option value="other" @selected(old('gender')=='other')>Other</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Educational qualification</label><input name="educational_qualification" class="form-control" value="{{ old('educational_qualification') }}"></div>
                    <div class="col-md-6"><label class="form-label">College / University</label><input name="college_name" class="form-control" value="{{ old('college_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Reg No</label><input name="reg_no" class="form-control" value="{{ old('reg_no') }}" placeholder="e.g. SIT001"></div>
                </div>

                <h6 class="fw-semibold text-muted mb-3 mt-4"><i class="fa-regular fa-address-book me-1"></i> Contact Details</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="form-label">Contact number</label><input type="tel" name="contact_number" class="form-control" value="{{ old('contact_number') }}"></div>
                    <div class="col-md-6"><label class="form-label">WhatsApp number</label><input type="tel" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number') }}"></div>
                    <div class="col-md-6"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city') }}"></div>
                    <div class="col-md-6"><label class="form-label">State</label><input name="state" class="form-control" value="{{ old('state') }}"></div>
                </div>

                <h6 class="fw-semibold text-muted mb-3 mt-4"><i class="fa-regular fa-clock me-1"></i> Course Preferences</h6>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Interested course</label>
                        <select name="internship_course_id" class="form-select">
                            <option value="">— Select —</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('internship_course_id') == $c->id)>{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Or type a course name</label><input name="interested_course_text" class="form-control" value="{{ old('interested_course_text') }}"></div>
                    <div class="col-md-6"><label class="form-label">Preferred timing</label><input name="preferred_timing" class="form-control" value="{{ old('preferred_timing') }}" placeholder="e.g. Morning 9-12"></div>
                    <div class="col-12"><label class="form-label">Message / Notes</label><textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea></div>
                </div>
                <button class="btn btn-primary mt-4 w-100 rounded-pill py-2"><i class="fa-regular fa-paper-plane me-1"></i>Submit Registration</button>
            </form>
        </div>
    </div>
    <p class="small text-muted text-center mt-3 mb-0">Powered by {{ config('app.name') }}</p>
</div>
</body>
</html>
