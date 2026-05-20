@extends('layouts.admin')
@section('title','Edit Internship Batch')
@section('page_title','Edit Internship Batch')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-batches.update', $internship_batch) }}" id="batchForm">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Internship Course <span class="text-danger">*</span></label>
                    <select name="internship_course_id" class="form-select" required>
                        <option value="">— Select Course —</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @selected($internship_batch->internship_course_id == $c->id)>{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batch Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $internship_batch->name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Faculty</label>
                    <select name="faculty_id" class="form-select">
                        <option value="">— Select Faculty —</option>
                        @foreach($faculties as $f)
                            <option value="{{ $f->id }}" @selected($internship_batch->faculty_id == $f->id)>{{ $f->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batch Timing</label>
                    <input type="text" name="timing" class="form-control" value="{{ $internship_batch->timing }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="{{ $internship_batch->start_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $internship_batch->end_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number of Days</label>
                    <input type="number" name="number_of_days" class="form-control" min="1" value="{{ $internship_batch->number_of_days }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Maximum Students</label>
                    <input type="number" name="capacity" class="form-control" min="1" value="{{ $internship_batch->capacity }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected($internship_batch->status==='active')>Active</option>
                        <option value="inactive" @selected($internship_batch->status==='inactive')>Inactive</option>
                        <option value="completed" @selected($internship_batch->status==='completed')>Completed</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Update Batch</button>
                <a href="{{ route('admin.internship-batches.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#batchForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:res.message}).then(()=>{
                window.location.href = '{{ route('admin.internship-batches.index') }}';
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
