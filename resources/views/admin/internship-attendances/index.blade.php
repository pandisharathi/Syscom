@extends('layouts.admin')
@section('title','Internship Attendance')
@section('page_title','Internship Attendance')

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Filter by Batch</label>
        <select class="form-select" id="filterBatch">
            <option value="">All Batches</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">From Date</label>
        <input type="date" class="form-control" id="filterFrom">
    </div>
    <div class="col-md-3">
        <label class="form-label">To Date</label>
        <input type="date" class="form-control" id="filterTo">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100" id="btnMark"><i class="fa-solid fa-clipboard-check me-1"></i>Mark New</button>
    </div>
</div>

<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt">
    <thead><tr><th>Date</th><th>Batch</th><th>Course</th><th>Present</th><th>Absent</th><th>Actions</th></tr></thead>
</table>
</div></div>
@endsection

@push('scripts')
<script>
let batchData = [];
fetch(`{{ route('admin.internship-batches.data') }}?length=500`).then(r=>r.json()).then(res=>{
    batchData = res.data||[];
    const opts = batchData.map(b=>`<option value="${b.id}">${b.name}</option>`).join('');
    $('#filterBatch').append(opts);
});

$('#btnMark').on('click', function(){
    const batchId = $('#filterBatch').val();
    if(!batchId){ Swal.fire({icon:'warning', title:'Please select a batch first'}); return; }
    window.location.href = `/admin/internship-attendances/batch/${batchId}/mark`;
});

new DataTable('#dt',{
    processing:true, serverSide:true, ajax:{
        url:'{{ route('admin.internship-attendances.data') }}',
        data: function(d){
            d.batch_id = $('#filterBatch').val();
            d.from = $('#filterFrom').val();
            d.to = $('#filterTo').val();
        }
    },
    columns:[
        {data:'date'}, {data:'batch'}, {data:'course'},
        {data:'present_count'},
        {data:'absent_count'},
        {data:'internship_batch_id', orderable:false, searchable:false, render:id=>`<a class="btn btn-sm btn-outline-primary" href="/admin/internship-attendances/batch/${id}/mark"><i class="fa-solid fa-clipboard-check"></i> Mark</a>`}
    ],
    dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
});

$('#filterBatch, #filterFrom, #filterTo').on('change', function(){
    window.dt?.ajax?.reload();
});
</script>
@endpush
