@extends('layouts.admin')
@section('title','Mark Internship Attendance')
@section('page_title','Mark Attendance — '.$internship_batch->name)

@section('content')
<div class="card card-soft" style="max-width:960px">
    <div class="card-body">
        <div class="row mb-3 text-muted small">
            <div class="col-md-4"><strong>Batch:</strong> {{ $internship_batch->name }}</div>
            <div class="col-md-4"><strong>Course:</strong> {{ $internship_batch->course?->name ?? 'N/A' }}</div>
            <div class="col-md-4"><strong>Timing:</strong> {{ $internship_batch->timing ?? 'N/A' }}</div>
        </div>

        <form method="post" action="{{ route('admin.internship-attendances.store', $internship_batch) }}" id="attForm">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="attendance_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="fw-semibold">Students</span>
                    <span class="small text-muted ms-2">Eligible: {{ $students->count() }}</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="small text-muted" id="countDisplay">
                        <span class="text-success fw-semibold" id="presentCount">{{ $students->count() }}</span> Present
                        &nbsp;·&nbsp;
                        <span class="text-danger fw-semibold" id="absentCount">0</span> Absent
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="allPresent"><i class="fa-solid fa-check"></i> All Present</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="allAbsent"><i class="fa-solid fa-xmark"></i> All Absent</button>
                </div>
            </div>
            <div class="table-responsive" style="max-height:450px;overflow-y:auto">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Contact</th>
                            <th style="min-width:140px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $i => $s)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>{{ $s->full_name }}</td>
                            <td class="small text-muted">{{ $s->phone ?? '—' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="status[{{ $s->id }}]" value="present" id="p{{ $s->id }}" checked autocomplete="off">
                                    <label class="btn btn-outline-success" for="p{{ $s->id }}"><i class="fa-solid fa-check me-1"></i>Present</label>
                                    <input type="radio" class="btn-check" name="status[{{ $s->id }}]" value="absent" id="a{{ $s->id }}" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="a{{ $s->id }}"><i class="fa-solid fa-xmark me-1"></i>Absent</label>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No eligible students found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Attendance</button>
                <a href="{{ route('admin.internship-attendances.index') }}" class="btn btn-link">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCounts(){
    let p = 0, a = 0;
    $('input[type="radio"]:checked').each(function(){
        if($(this).val() === 'present') p++; else a++;
    });
    $('#presentCount').text(p);
    $('#absentCount').text(a);
}

$(document).on('change', 'input[type="radio"]', updateCounts);

$('#allPresent').on('click', function(){
    $('input[type="radio"][value="present"]').prop('checked', true).trigger('change');
});

$('#allAbsent').on('click', function(){
    $('input[type="radio"][value="absent"]').prop('checked', true).trigger('change');
});

$('#attForm').on('submit', function(e){
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:'Attendance saved'}).then(()=>{
                window.location.href = '{{ route('admin.internship-attendances.index') }}';
            });
        },
        error: function(xhr){
            const msg = xhr.responseJSON?.message || 'An error occurred';
            Swal.fire({icon:'error', title:msg});
        }
    });
});
</script>
@endpush
