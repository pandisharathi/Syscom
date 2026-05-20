@extends('layouts.admin')
@section('title','Enquiry Details')
@section('page_title','Enquiry — '.$internship_enquiry->full_name)

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card card-soft mb-3">
            <div class="card-body">
                <h6 class="fw-semibold text-muted mb-3">Enquiry Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:140px">Name</td><td>{{ $internship_enquiry->full_name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $internship_enquiry->email ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Contact</td><td>{{ $internship_enquiry->contact_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">WhatsApp</td><td>{{ $internship_enquiry->whatsapp_number ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Gender</td><td>{{ ucfirst($internship_enquiry->gender ?? '—') }}</td></tr>
                    <tr><td class="text-muted">Qualification</td><td>{{ $internship_enquiry->educational_qualification ?? '—' }}</td></tr>
                    <tr><td class="text-muted">College</td><td>{{ $internship_enquiry->college_name ?? '—' }}</td></tr>
                    <tr><td class="text-muted">City, State</td><td>{{ trim(($internship_enquiry->city ?? '').', '.($internship_enquiry->state ?? ''), ', ') ?: '—' }}</td></tr>
                    <tr><td class="text-muted">Course Interest</td><td>{{ $internship_enquiry->course?->name ?? $internship_enquiry->interested_course_text ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Preferred Timing</td><td>{{ $internship_enquiry->preferred_timing ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Message</td><td>{{ $internship_enquiry->message ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $internship_enquiry->status === 'new' ? 'primary' : ($internship_enquiry->status === 'contacted' ? 'warning' : ($internship_enquiry->status === 'interested' ? 'info' : ($internship_enquiry->status === 'enrolled' ? 'success' : 'secondary'))) }}">{{ $internship_enquiry->status }}</span></td></tr>
                    <tr><td class="text-muted">Received</td><td>{{ $internship_enquiry->created_at?->format('d M Y h:i A') }}</td></tr>
                    @if($internship_enquiry->resume_path)
                    <tr><td class="text-muted">Resume</td><td><a href="{{ asset('storage/'.$internship_enquiry->resume_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file me-1"></i>View Resume</a></td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-soft mb-3">
            <div class="card-body">
                <h6 class="fw-semibold text-muted mb-3">Update Status & Course</h6>
                <form id="updateForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['new','contacted','interested','enrolled','rejected'] as $s)
                            <option value="{{ $s }}" {{ $internship_enquiry->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="internship_course_id" class="form-select">
                            <option value="">— No course —</option>
                            @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ $internship_enquiry->internship_course_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-save me-1"></i>Update</button>
                </form>
            </div>
        </div>

        @if($internship_enquiry->status !== 'enrolled')
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="fw-semibold text-muted mb-3">Convert to Student</h6>
                <form id="convertForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Assign to Batch <span class="text-danger">*</span></label>
                        <select name="internship_batch_id" class="form-select" required>
                            <option value="">— Select Batch —</option>
                            @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} — {{ $b->course?->name ?? 'N/A' }} ({{ $b->timing ?? 'No timing' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-user-plus me-1"></i>Convert to Student</button>
                </form>
            </div>
        </div>
        @else
        <div class="card card-soft">
            <div class="card-body text-center text-muted py-4">
                <i class="fa-solid fa-check-circle text-success fa-2x mb-2"></i>
                <p class="mb-0">Already enrolled as a student</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#updateForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: '{{ route('admin.internship-enquiries.update', $internship_enquiry) }}',
        method: 'PUT',
        data: $(this).serialize(),
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:'Updated'}).then(()=> location.reload());
        },
        error: function(xhr){
            const msg = xhr.responseJSON?.message || 'Update failed';
            Swal.fire({icon:'error', title:msg});
        }
    });
});

$('#convertForm').on('submit', function(e){
    e.preventDefault();
    const form = $(this);
    $.ajax({
        url: '{{ route('admin.internship-enquiries.convert', $internship_enquiry) }}',
        method: 'POST',
        data: form.serialize(),
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:'Student enrolled'}).then(()=>{
                window.location.href = '{{ route('admin.internship-students.show', ['internship_student' => '__id__']) }}'.replace('__id__', res.student_id);
            });
        },
        error: function(xhr){
            const msg = xhr.responseJSON?.message || 'Conversion failed';
            Swal.fire({icon:'error', title:msg});
        }
    });
});
</script>
@endpush
