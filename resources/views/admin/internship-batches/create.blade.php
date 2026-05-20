@extends('layouts.admin')
@section('title','Create Internship Batch')
@section('page_title','Create Internship Batch')

@section('content')
<div class="card card-soft" style="max-width:900px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-batches.store') }}" id="batchForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Internship Course <span class="text-danger">*</span></label>
                    <select name="internship_course_id" class="form-select" required>
                        <option value="">— Select Course —</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batch Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Batch A - 2025">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Faculty</label>
                    <select name="faculty_id" class="form-select">
                        <option value="">— Select Faculty —</option>
                        @foreach($faculties as $f)
                            <option value="{{ $f->id }}">{{ $f->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batch Timing</label>
                    <input type="text" name="timing" class="form-control" placeholder="e.g. 10:00 AM - 1:00 PM">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number of Days</label>
                    <input type="number" name="number_of_days" class="form-control" min="1" placeholder="e.g. 90">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Maximum Students</label>
                    <input type="number" name="capacity" class="form-control" min="1" placeholder="e.g. 30">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Batch</button>
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
        data: $(this).serialize(),
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
