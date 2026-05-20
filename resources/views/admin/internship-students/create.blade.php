@extends('layouts.admin')
@section('title','Enroll Internship Student')
@section('page_title','Enroll Internship Student')

@section('content')
<div class="card card-soft" style="max-width:960px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-students.store') }}" enctype="multipart/form-data" id="studentForm">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Personal Information</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">— Select —</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Student Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" id="photo">
                    <div class="mt-2" id="photoPreview" style="display:none">
                        <img src="" class="img-thumbnail rounded-circle" style="width:80px;height:80px;object-fit:cover">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="col-12 mt-2">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Contact Information</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="student@example.com">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone">
                </div>
                <div class="col-md-3">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" placeholder="WhatsApp">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-12 mt-2">
                    <h6 class="fw-semibold text-muted border-bottom pb-2">Academic & Enrollment</h6>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Educational Qualification</label>
                    <input type="text" name="educational_qualification" class="form-control" placeholder="e.g. B.Sc Computer Science">
                </div>
                <div class="col-md-4">
                    <label class="form-label">College Name</label>
                    <input type="text" name="college_name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reg No</label>
                    <input type="text" name="reg_no" class="form-control" placeholder="e.g. REG-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Internship Batch <span class="text-danger">*</span></label>
                    <select name="internship_batch_id" class="form-select" required>
                        <option value="">— Select Batch —</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->course?->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="completed">Completed</option>
                        <option value="relieved">Relieved</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Enroll Student</button>
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
