@extends('layouts.admin')
@section('title','Convert Enquiry to Student')
@section('page_title','Convert Enquiry — '.$internship_enquiry->full_name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-soft">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Enquiry Details</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted pe-3">Name</td><td>{{ $internship_enquiry->full_name }}</td></tr>
                            <tr><td class="text-muted pe-3">Email</td><td>{{ $internship_enquiry->email ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">Phone</td><td>{{ $internship_enquiry->contact_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">Course</td><td>{{ $internship_enquiry->course?->name ?? $internship_enquiry->interested_course_text ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">City</td><td>{{ $internship_enquiry->city ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">Qualification</td><td>{{ $internship_enquiry->educational_qualification ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-1">Student Record Preview</h6>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted pe-3">First Name</td><td>{{ $internship_enquiry->first_name }}</td></tr>
                            <tr><td class="text-muted pe-3">Last Name</td><td>{{ $internship_enquiry->last_name }}</td></tr>
                            <tr><td class="text-muted pe-3">Email</td><td>{{ $internship_enquiry->email ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">Phone</td><td>{{ $internship_enquiry->contact_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted pe-3">Gender</td><td>{{ ucfirst($internship_enquiry->gender ?? '—') }}</td></tr>
                            <tr><td class="text-muted pe-3">City, State</td><td>{{ trim(($internship_enquiry->city ?? '').', '.($internship_enquiry->state ?? '')) }}</td></tr>
                        </table>
                    </div>
                </div>

                <form method="post" action="{{ route('admin.internship-enquiries.convert', $internship_enquiry) }}" id="convertForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="internship_batch_id">Internship Batch <span class="text-danger">*</span></label>
                            <select name="internship_batch_id" id="internship_batch_id" class="form-select" required>
                                <option value="">— Select Batch —</option>
                                @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ old('internship_batch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} — {{ $b->course?->name ?? 'N/A' }} ({{ $b->timing ?? 'No timing' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i>Convert to Student</button>
                        <a href="{{ route('admin.internship-enquiries.index') }}" class="btn btn-link">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#convertForm').on('submit', function(e){
    e.preventDefault();
    const form = $(this);
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:'Student enrolled successfully'}).then(()=>{
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
