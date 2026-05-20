@extends('layouts.admin')
@section('title','Batches')
@section('page_title','Batches')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <button class="btn btn-primary btn-sm rounded-pill" id="btnAdd"><i class="fa-solid fa-plus me-1"></i>New batch</button>
</div>
<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="dt"><thead><tr><th>Name</th><th>Course</th><th>Faculty</th><th>Start</th><th>End</th><th>Timing</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function reload(){ table.destroy(); table = mk(); }
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.batches.data') }}',
    columns:[
        {data:'name'}, {data:'course'}, {data:'faculty'},
        {data:'start_date'}, {data:'end_date'}, {data:'timing'},
        {data:'status'}, {data:'id', orderable:false, searchable:false, render:id=>`<button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();

$('#btnAdd').on('click', async ()=>{
    const courses = await fetch(`{{ route('admin.courses.data') }}?length=500`).then(r=>r.json()).then(x=>x.data||[]);
    const opts = courses.map(c=>`<option value="${c.id}">${c.code} — ${c.name}</option>`).join('');
    Swal.fire({
        title:'New batch',
        html:`<select id="course_id" class="form-select mb-2"><option value="">Course</option>${opts}</select>
              <input id="name" class="form-control mb-2" placeholder="Batch name (optional)">
              <input id="start_date" type="date" class="form-control mb-2">
              <input id="end_date" type="date" class="form-control mb-2">
              <input id="timing" class="form-control mb-2" placeholder="Timing">
              <input id="number_of_days" type="number" class="form-control mb-2" placeholder="Days">`,
        showCancelButton:true,
        preConfirm:()=>({
            course_id:$('#course_id').val(), name:$('#name').val(), start_date:$('#start_date').val(),
            end_date:$('#end_date').val(), timing:$('#timing').val(), number_of_days:$('#number_of_days').val(), status:'active'
        })
    }).then(res=>{
        if(!res.isConfirmed) return;
        fetch(`{{ route('admin.batches.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(res.value)}).then(reload);
    });
});

$('#dt').on('click','.btn-del', function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete?', showCancelButton:true}).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`/admin/batches/${id}`, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(reload);
    });
});
</script>
@endpush
