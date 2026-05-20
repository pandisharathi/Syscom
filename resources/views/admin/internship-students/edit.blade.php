@extends('layouts.admin')
@section('title','Edit Internship Student')
@section('page_title','Edit Internship Student')

@section('content')
<div class="card card-soft" style="max-width:960px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-students.update', $internship_student) }}" enctype="multipart/form-data" id="studentForm">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Personal Information</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reg No</label>
                    <input type="text" name="reg_no" class="form-control" value="{{ $internship_student->reg_no }}" placeholder="e.g. REG-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" required value="{{ $internship_student->first_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" required value="{{ $internship_student->last_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">— Select —</option>
                        <option value="male" @selected($internship_student->gender==='male')>Male</option>
                        <option value="female" @selected($internship_student->gender==='female')>Female</option>
                        <option value="other" @selected($internship_student->gender==='other')>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ $internship_student->date_of_birth?->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Student Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" id="photo">
                    @if($internship_student->photo)
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="{{ $internship_student->photo_url }}" class="rounded-circle" style="width:60px;height:60px;object-fit:cover">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo">
                                <label class="form-check-label" for="remove_photo">Remove</label>
                            </div>
                        </div>
                    @endif
                    <div class="mt-2" id="photoPreview" style="display:none">
                        <img src="" class="img-thumbnail rounded-circle" style="width:80px;height:80px;object-fit:cover">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="{{ $internship_student->joining_date?->format('Y-m-d') }}">
                </div>

                <div class="col-12 mt-2">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Contact Information</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ $internship_student->email }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ $internship_student->phone }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="{{ $internship_student->whatsapp_number }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ $internship_student->city }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ $internship_student->state }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ $internship_student->pincode }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ $internship_student->address }}</textarea>
                </div>

                <div class="col-12 mt-2">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Academic & Enrollment</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Educational Qualification</label>
                    <input type="text" name="educational_qualification" class="form-control" value="{{ $internship_student->educational_qualification }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="{{ $internship_student->department }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">College Name</label>
                    <input type="text" name="college_name" class="form-control" value="{{ $internship_student->college_name }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Internship Batch <span class="text-danger">*</span></label>
                    <select name="internship_batch_id" class="form-select" required>
                        <option value="">— Select Batch —</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" @selected($internship_student->internship_batch_id == $b->id)>{{ $b->name }} ({{ $b->course?->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected($internship_student->status==='active')>Active</option>
                        <option value="inactive" @selected($internship_student->status==='inactive')>Inactive</option>
                        <option value="completed" @selected($internship_student->status==='completed')>Completed</option>
                        <option value="relieved" @selected($internship_student->status==='relieved')>Relieved</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Update Student</button>
                <a href="{{ route('admin.internship-students.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#photo').on('change', function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            $('#photoPreview img').attr('src', e.target.result);
            $('#photoPreview').show();
        }
        reader.readAsDataURL(file);
    }
});

$('#studentForm').on('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:res.message}).then(()=>{
                window.location.href = '{{ route('admin.internship-students.index') }}';
            });
        },
        error: function(xhr){
            const errors = xhr.responseJSON?.errors || {};
            let msg = '<div class="text-start">';
            Object.values(errors).flat().forEach(e => { msg += `<p class="mb-1">${e}</p>`; });
            msg += '</div>';
            Swal.fire({icon:'error', title:'Validation Error', html:msg});
        }
    });
});
</script>
@endpush
