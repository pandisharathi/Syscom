@extends('layouts.admin')
@section('title','Courses')
@section('page_title','Courses')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <button class="btn btn-primary btn-sm rounded-pill" id="btnAddCourse"><i class="fa-solid fa-plus me-1"></i>Add course</button>
</div>
<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="dtCourses"><thead><tr><th>Code</th><th>Name</th><th>Duration</th><th>Fees</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function dt(){
    return new DataTable('#dtCourses', {
        processing:true, serverSide:true, ajax:'{{ route('admin.courses.data') }}',
        columns:[
            {data:'code', name:'code'}, {data:'name', name:'name'}, {data:'duration'}, {data:'fees'},
            {data:'status', render:d=>'<span class="badge bg-'+(d==='active'?'success':'secondary')+'">'+d+'</span>'},
            {data:'id', orderable:false, searchable:false, render:id=>`<button class="btn btn-sm btn-outline-secondary btn-edit" data-id="${id}"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}
        ],
        dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
    });
}
let table = dt();

$('#btnAddCourse').on('click', ()=>{
    Swal.fire({
        title:'New course', html:
            `<input id="c_code" class="form-control mb-2" placeholder="Code">
             <input id="c_name" class="form-control mb-2" placeholder="Name">
             <input id="c_duration" class="form-control mb-2" placeholder="Duration">
             <input id="c_fees" type="number" step="0.01" class="form-control mb-2" placeholder="Fees">
             <textarea id="c_desc" class="form-control mb-2" placeholder="Description"></textarea>`,
        showCancelButton:true,
        preConfirm:()=>({
            code:$('#c_code').val(), name:$('#c_name').val(), duration:$('#c_duration').val(),
            fees:$('#c_fees').val(), description:$('#c_desc').val(), status:'active'
        })
    }).then(res=>{
        if(!res.isConfirmed) return;
        fetch(`{{ route('admin.courses.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(res.value)})
            .then(r=>r.json()).then(()=>{ table.destroy(); table = dt(); });
    });
});

$('#dtCourses').on('click','.btn-del', function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete?',icon:'warning',showCancelButton:true}).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`/admin/courses/${id}`, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{ table.destroy(); table = dt(); });
    });
});
</script>
@endpush
