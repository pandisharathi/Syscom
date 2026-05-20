@extends('layouts.admin')
@section('title','Internship Reports')
@section('page_title','Internship Reports')

@section('content')
<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select id="type" class="form-select">
                    <option value="active_students">Active Students</option>
                    <option value="completed_students">Completed Students</option>
                    <option value="enrollment">Enrollment by Batch</option>
                    <option value="course_wise">Course-wise Report</option>
                    <option value="batch_wise">Batch-wise Report</option>
                    <option value="present_details">Present Students Details</option>
                    <option value="absent_details">Absent Students Details</option>
                    <option value="attendance">Attendance Summary</option>
                    <option value="gender">Gender Distribution</option>
                    <option value="college">College Distribution</option>
                    <option value="enquiry">Enquiry Funnel</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Course</label>
                <select id="filterCourse" class="form-select">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Batch</label>
                <select id="filterBatch" class="form-select">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select id="filterStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="completed">Completed</option>
                    <option value="relieved">Relieved</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" id="filterFrom" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" id="filterTo" class="form-control">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" id="btnLoad"><i class="fa-solid fa-chart-simple me-1"></i>Load Report</button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-secondary w-100" id="btnExport"><i class="fa-solid fa-download me-1"></i>Export</button>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft">
    <div class="card-body">
        <div id="reportHeader" class="fw-semibold mb-2 text-muted"></div>
        <div class="table-responsive">
            <table class="table w-100" id="rep">
                <thead id="thead"><tr></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentType = 'active_students';
let currentData = [];

$('#btnLoad').on('click', loadReport);
$('#type').on('change', function(){ currentType = $(this).val(); loadReport(); });

function loadReport(){
    const params = new URLSearchParams({
        type: $('#type').val(),
        course_id: $('#filterCourse').val(),
        batch_id: $('#filterBatch').val(),
        status: $('#filterStatus').val(),
        from: $('#filterFrom').val(),
        to: $('#filterTo').val()
    });
    currentType = $('#type').val();

    fetch(`{{ route('admin.internship-reports.data') }}?${params}`, {headers:{'Accept':'application/json'}})
        .then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
        .then(res=>{
            currentData = res.data||[];
            renderTable(currentType, currentData);
        })
        .catch(err=>{
            console.error('Report fetch error:', err);
            Swal.fire({icon:'error',title:'Failed to load report',text:err.message});
        });
}

let reportDt = null;

function renderTable(type, data){
    const headerMap = {
        active_students: ['S.No','Name','Email','Phone','Batch','College','Status'],
        completed_students: ['S.No','Name','Email','Phone','Batch','College','Status'],
        enrollment: ['S.No','Batch','Course','Students','Capacity','Status'],
        course_wise: ['S.No','Course','Total Batches','Total Students','Status'],
        batch_wise: ['S.No','Batch','Course','Timing','Students','Status'],
        present_details: ['S.No','Student Name','Email','Phone','Batch','Course','Date'],
        absent_details: ['S.No','Student Name','Email','Phone','Batch','Course','Date'],
        attendance: ['S.No','Batch','Course','Date','Present','Absent','Total'],
        gender: ['S.No','Gender','Count'],
        college: ['S.No','College','Count'],
        enquiry: ['S.No','Status','Count']
    };

    const headers = headerMap[type] || ['Name','Value'];
    let theadHtml = '<tr>';
    headers.forEach(h => { theadHtml += `<th>${h}</th>`; });
    theadHtml += '</tr>';

    if(reportDt){ reportDt.destroy(); reportDt = null; }
    $('#rep').empty();
    $('#rep').append(`<thead id="thead">${theadHtml}</thead><tbody></tbody>`);
    $('#reportHeader').text($('#type option:selected').text());

    let tbodyHtml = '';
    data.forEach((row, i) => {
        tbodyHtml += '<tr>';
        tbodyHtml += `<td class="text-muted">${i+1}</td>`;
        if(type === 'active_students' || type === 'completed_students'){
            tbodyHtml += `<td>${row.full_name||'—'}</td><td>${row.email||'—'}</td><td>${row.phone||'—'}</td><td>${row.batch||'—'}</td><td>${row.college_name||'—'}</td><td><span class="badge bg-${row.status==='active'?'success':row.status==='completed'?'primary':row.status==='relieved'?'warning':'secondary'}">${row.status||'—'}</span></td>`;
        } else if(type === 'enrollment'){
            tbodyHtml += `<td>${row.batch||'—'}</td><td>${row.course||'—'}</td><td>${row.students||0}</td><td>${row.capacity||'—'}</td><td>${row.status||'—'}</td>`;
        } else if(type === 'course_wise'){
            tbodyHtml += `<td>${row.course||'—'}</td><td>${row.batches||0}</td><td>${row.students||0}</td><td>${row.status||'—'}</td>`;
        } else if(type === 'batch_wise'){
            tbodyHtml += `<td>${row.batch||'—'}</td><td>${row.course||'—'}</td><td>${row.timing||'—'}</td><td>${row.students||0}</td><td>${row.status||'—'}</td>`;
        } else if(type === 'present_details' || type === 'absent_details'){
            tbodyHtml += `<td>${row.student_name||'—'}</td><td>${row.email||'—'}</td><td>${row.phone||'—'}</td><td>${row.batch||'—'}</td><td>${row.course||'—'}</td><td>${row.date||'—'}</td>`;
        } else if(type === 'attendance'){
            tbodyHtml += `<td>${row.batch||'—'}</td><td>${row.course||'—'}</td><td>${row.date||'—'}</td><td>${row.present||0}</td><td>${row.absent||0}</td><td>${row.total||0}</td>`;
        } else {
            const keys = Object.keys(row);
            keys.forEach(k => { tbodyHtml += `<td>${row[k]??'—'}</td>`; });
        }
        tbodyHtml += '</tr>';
    });

    if(!data.length){
        tbodyHtml = '<tr><td colspan="10" class="text-center text-muted py-4">No data found</td></tr>';
        $('#rep tbody').html(tbodyHtml);
        return;
    }
    $('#rep tbody').html(tbodyHtml);

    try {
        reportDt = new DataTable('#rep',{
            dom:'Bfrtip',
            buttons:['copy','csv','excel','pdf','print'],
            responsive:true,
            pageLength:25,
            retrieve:true
        });
    } catch(e){
        console.error('DataTable init error:', e);
    }
}

$('#btnExport').on('click', function(){
    if($.fn.DataTable.isDataTable('#rep')){
        const api = $('#rep').DataTable();
        api.button('.buttons-excel').trigger();
    }
});

$('#btnLoad').trigger('click');
</script>
@endpush
