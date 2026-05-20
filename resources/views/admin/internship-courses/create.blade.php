@extends('layouts.admin')
@section('title','Create Internship Course')
@section('page_title','Create Internship Course')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-courses.store') }}" enctype="multipart/form-data" id="courseForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Course Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g. INT-WEB">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Course Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Web Development">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" class="form-control" placeholder="e.g. 3 months">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fees</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" name="fees" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Course Image</label>
                    <input type="file" name="course_image" class="form-control" accept="image/*" id="course_image">
                    <div class="mt-2" id="imagePreview" style="display:none">
                        <img src="" class="img-thumbnail" style="max-height:120px">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Course description..."></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Course</button>
                <a href="{{ route('admin.internship-courses.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#course_image').on('change', function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            $('#imagePreview img').attr('src', e.target.result);
            $('#imagePreview').show();
        }
        reader.readAsDataURL(file);
    }
});

$('#courseForm').on('submit', function(e){
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
                window.location.href = '{{ route('admin.internship-courses.index') }}';
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
