@extends('layouts.admin')
@section('title','Internship Batches')
@section('page_title','Internship Batches')

@section('content')
<a href="{{ route('admin.internship-batches.create') }}" class="btn btn-primary btn-sm rounded-pill mb-3"><i class="fa-solid fa-plus me-1"></i>New Batch</a>
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt">
    <thead><tr><th>Name</th><th>Course</th><th>Faculty</th><th>Students</th><th>Start</th><th>End</th><th>Timing</th><th>Status</th><th>Actions</th></tr></thead>
</table>
</div></div>
@endsection

@push('scripts')
<script>
function reload(){ table.destroy(); table = mk(); }
function mk(){
    return new DataTable('#dt',{
        processing:true, serverSide:true, ajax:'{{ route('admin.internship-batches.data') }}',
        columns:[
            {data:'name'}, {data:'course'}, {data:'faculty'},
            {data:'students_count', render:(d,meta,row)=>`${d??0}/${row.capacity??'—'}`},
            {data:'start_date'}, {data:'end_date'}, {data:'timing'},
            {data:'status', render:d=>`<span class="badge bg-${d==='active'?'success':d==='completed'?'primary':'secondary'}">${d}</span>`},
            {data:'id', orderable:false, searchable:false, render:id=>`
                <a class="btn btn-sm btn-outline-secondary" href="/admin/internship-batches/${id}/edit"><i class="fa-solid fa-pen"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
            `}
        ],
        dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
    });
}
let table = mk();

$('#dt').on('click','.btn-del',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete batch?', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33'}).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`/admin/internship-batches/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(reload);
    });
});
</script>
@endpush
