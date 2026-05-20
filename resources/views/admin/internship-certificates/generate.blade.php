@extends('layouts.admin')
@section('title','Generate Certificates')
@section('page_title','Generate Certificates')

@section('content')
<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select class="form-select" id="filterCourse">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select class="form-select" id="filterBatch">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" id="btnFilter"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                <button class="btn btn-outline-secondary" id="btnReset"><i class="fa-solid fa-undo me-1"></i>Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Certificate Template</label>
                <select class="form-select" id="certTemplate">
                    <option value="">Default Design</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="issueDate" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Completion Date</label>
                <input type="date" class="form-control" id="completionDate">
            </div>
            <div class="col-md-3">
                <label class="form-label">Internship Title (Optional)</label>
                <input type="text" class="form-control" id="internshipTitle" placeholder="e.g. Full Stack Developer">
            </div>
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button class="btn btn-success" id="btnGenerateSelected" disabled><i class="fa-solid fa-award me-1"></i>Generate</button>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft"><div class="card-body">
<div class="mb-2">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="selectAll">
        <label class="form-check-label fw-semibold" for="selectAll">Select All</label>
        <span class="text-muted small ms-2" id="selectedCount">0 selected</span>
    </div>
</div>
<div class="table-responsive">
<table class="table" id="studentsTable">
    <thead><tr><th></th><th>Student</th><th>Course</th><th>Batch</th><th>Certificate</th></tr></thead>
    <tbody id="studentsBody">
        <tr><td colspan="5" class="text-center text-muted py-4">Select filters and click Filter</td></tr>
    </tbody>
</table>
</div>
</div></div>
@endsection

@push('scripts')
<script>
let selectedStudents = new Set();

function renderStudents(data){
    const tbody = $('#studentsBody');
    if(!data.length){
        tbody.html('<tr><td colspan="5" class="text-center text-muted py-4">No students found</td></tr>');
        return;
    }
    tbody.html(data.map(s=>{
        const checked = selectedStudents.has(s.id)?'checked':'';
        const disabled = s.has_certificate?'disabled':'';
        return `<tr>
            <td><input type="checkbox" class="form-check-input student-check" value="${s.id}" ${checked} ${disabled}></td>
            <td>${s.full_name}<br><small class="text-muted">${s.email||''}</small></td>
            <td>${s.course}</td>
            <td>${s.batch}</td>
            <td>${s.has_certificate?'<span class="badge bg-success">Generated</span>':'<span class="badge bg-secondary">Pending</span>'}</td>
        </tr>`;
    }).join(''));
    updateSelectedCount();
    updateGenerateBtn();
}

function updateSelectedCount(){
    $('#selectedCount').text(selectedStudents.size+' selected');
}

function updateGenerateBtn(){
    $('#btnGenerateSelected').prop('disabled', selectedStudents.size === 0);
}

function reload(){
    const courseId = $('#filterCourse').val();
    const batchId = $('#filterBatch').val();
    $.get('{{ route('admin.internship-certificates.generate-students') }}',{course_id:courseId,batch_id:batchId},function(res){
        renderStudents(res.data);
    });
}

$('#btnFilter').on('click', reload);
$('#btnReset').on('click',function(){
    $('#filterCourse').val('');
    $('#filterBatch').val('');
    reload();
});

$(document).on('change','.student-check',function(){
    const val = parseInt($(this).val());
    if($(this).is(':checked')) selectedStudents.add(val);
    else selectedStudents.delete(val);
    updateSelectedCount();
    updateGenerateBtn();
});

$('#selectAll').on('change',function(){
    const checked = $(this).is(':checked');
    $('.student-check:not(:disabled)').each(function(){
        $(this).prop('checked',checked);
        const val = parseInt($(this).val());
        if(checked) selectedStudents.add(val);
        else selectedStudents.delete(val);
    });
    updateSelectedCount();
    updateGenerateBtn();
});

$('#btnGenerateSelected').on('click',function(){
    const studentIds = Array.from(selectedStudents);
    if(!studentIds.length){ Swal.fire({icon:'warning',title:'Select students'}); return; }
    if(!$('#issueDate').val()){ Swal.fire({icon:'warning',title:'Select issue date'}); return; }

    const btn = $(this);
    btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span>Generating...');

    $.ajax({
        url:'{{ route('admin.internship-certificates.generate-store') }}',
        method:'POST',
        data:{
            student_ids: studentIds,
            certificate_template_id: $('#certTemplate').val() || '',
            issue_date: $('#issueDate').val(),
            completion_date: $('#completionDate').val() || '',
            internship_title: $('#internshipTitle').val() || '',
        },
        success:function(res){
            Swal.fire({icon:'success',title:'Done!',text:res.message,timer:2000,showConfirmButton:false});
            selectedStudents.clear();
            reload();
        },
        error:function(xhr){
            Swal.fire({icon:'error',title:'Error',text:xhr.responseJSON?.message||'Error'});
        },
        complete:function(){ btn.prop('disabled',false).html('<i class="fa-solid fa-award me-1"></i>Generate'); }
    });
});
</script>
@endpush
