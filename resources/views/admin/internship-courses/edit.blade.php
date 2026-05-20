@extends('layouts.admin')
@section('title','Edit Internship Course')
@section('page_title','Edit Internship Course')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-courses.update', $internship_course) }}" enctype="multipart/form-data" id="courseForm">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Course Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required value="{{ $internship_course->code }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Course Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ $internship_course->name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" class="form-control" value="{{ $internship_course->duration }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fees</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" name="fees" class="form-control" value="{{ $internship_course->fees }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $internship_course->start_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $internship_course->end_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Course Image</label>
                    <input type="file" name="course_image" class="form-control" accept="image/*" id="course_image">
                    @if($internship_course->course_image)
                        <div class="mt-2">
                            <img src="{{ Storage::url($internship_course->course_image) }}" class="img-thumbnail" style="max-height:120px">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        </div>
                    @endif
                    <div class="mt-2" id="imagePreview" style="display:none">
                        <img src="" class="img-thumbnail" style="max-height:120px">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected($internship_course->status==='active')>Active</option>
                        <option value="inactive" @selected($internship_course->status==='inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $internship_course->description }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Update Course</button>
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
