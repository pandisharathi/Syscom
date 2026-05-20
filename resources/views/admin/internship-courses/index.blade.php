@extends('layouts.admin')
@section('title','Internship Courses')
@section('page_title','Internship Courses')

@section('content')
<a href="{{ route('admin.internship-courses.create') }}" class="btn btn-primary btn-sm rounded-pill mb-3"><i class="fa-solid fa-plus me-1"></i>Add Course</a>
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt">
    <thead><tr><th>Code</th><th>Name</th><th>Duration</th><th>Fees</th><th>Start</th><th>End</th><th>Status</th><th>Actions</th></tr></thead>
</table>
</div></div>
@endsection

@push('scripts')
<script>
function mk(){
    return new DataTable('#dt',{
        processing:true, serverSide:true, ajax:'{{ route('admin.internship-courses.data') }}',
        columns:[
            {data:'code'}, {data:'name'}, {data:'duration'},
            {data:'fees', render:v=>v?parseFloat(v).toFixed(2):'0.00'},
            {data:'start_date'}, {data:'end_date'},
            {data:'status', render:d=>`<span class="badge bg-${d==='active'?'success':'secondary'}">${d}</span>`},
            {data:'id', orderable:false, searchable:false, render:id=>`
                <a class="btn btn-sm btn-outline-info" href="/admin/internship-courses/${id}"><i class="fa-solid fa-eye"></i></a>
                <a class="btn btn-sm btn-outline-secondary" href="/admin/internship-courses/${id}/edit"><i class="fa-solid fa-pen"></i></a>
                <button class="btn btn-sm btn-outline-success btn-toggle" data-id="${id}"><i class="fa-solid fa-toggle-on"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
            `}
        ],
        dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
    });
}
let table = mk();

$('#dt').on('click','.btn-del',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete course?', text:"This will also remove related batches.", icon:'warning', showCancelButton:true, confirmButtonColor:'#d33'}).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`/admin/internship-courses/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{table.destroy();table=mk();});
    });
});

$('#dt').on('click','.btn-toggle',function(){
    const id=$(this).data('id');
    fetch(`/admin/internship-courses/${id}/toggle-status`,{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{table.destroy();table=mk();});
});
</script>
@endpush
