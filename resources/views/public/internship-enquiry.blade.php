<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Internship enquiry — {{ $institution->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:820px;">
    <div class="text-center mb-4">
        <h3 class="fw-semibold">{{ $institution->name }}</h3>
        <p class="text-muted mb-0">Internship enquiry form</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <form method="post" action="{{ route('public.internship-enquiry.store', $institution->code) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">First name</label><input name="first_name" class="form-control" required value="{{ old('first_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Last name</label><input name="last_name" class="form-control" required value="{{ old('last_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
                    <div class="col-md-6"><label class="form-label">Contact number</label><input name="contact_number" class="form-control" value="{{ old('contact_number') }}"></div>
                    <div class="col-md-6"><label class="form-label">WhatsApp</label><input name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number') }}"></div>
                    <div class="col-md-6"><label class="form-label">Gender</label>
                        <select name="gender" class="form-select"><option value="">—</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Qualification</label><input name="educational_qualification" class="form-control" value="{{ old('educational_qualification') }}"></div>
                    <div class="col-md-6"><label class="form-label">College</label><input name="college_name" class="form-control" value="{{ old('college_name') }}"></div>
                    <div class="col-md-6"><label class="form-label">Interested course</label>
                        <select name="internship_course_id" class="form-select">
                            <option value="">— Select —</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('internship_course_id') == $c->id)>{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Or type a course</label><input name="interested_course_text" class="form-control" value="{{ old('interested_course_text') }}"></div>
                    <div class="col-md-6"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city') }}"></div>
                    <div class="col-md-6"><label class="form-label">State</label><input name="state" class="form-control" value="{{ old('state') }}"></div>
                    <div class="col-12"><label class="form-label">Preferred timing</label><input name="preferred_timing" class="form-control" value="{{ old('preferred_timing') }}"></div>
                    <div class="col-12"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea></div>
                    <div class="col-12"><label class="form-label">Resume</label><input type="file" name="resume" class="form-control"></div>
                </div>
                <button class="btn btn-primary mt-3 w-100 rounded-pill py-2">Submit enquiry</button>
            </form>
        </div>
    </div>
    <p class="small text-muted text-center mt-3 mb-0">Powered by {{ config('app.name') }}</p>
</div>
</body>
</html>
