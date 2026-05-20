@extends('layouts.admin')
@section('title','Internship enquiries')
@section('page_title','Internship enquiries')

@section('content')
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt"><thead><tr><th>Received</th><th>Name</th><th>Email</th><th>Course</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.internship-enquiries.data') }}',
    columns:[
        {data:'created_at'}, {data:'name'}, {data:'email'}, {data:'course'}, {data:'status'},
        {data:null, orderable:false, searchable:false, render:(data,type,row)=>{
            const id = row.id;
            const status = row.status;
            return `<div class="d-flex flex-wrap gap-1">
                <a class="btn btn-sm btn-outline-info" href="/admin/internship-enquiries/${id}"><i class="fa-solid fa-eye"></i></a>
                <select class="form-select form-select-sm status-select" data-id="${id}" style="width:auto">
                    ${['new','contacted','interested','enrolled','rejected'].map(s=>`<option value="${s}" ${s===status?'selected':''}>${s}</option>`).join('')}
                </select>
                <a class="btn btn-sm btn-outline-primary" href="/admin/internship-enquiries/${id}/convert"><i class="fa-solid fa-user-plus"></i></a>
                ${row.resume_url ? `<a class="btn btn-sm btn-outline-secondary" href="${row.resume_url}" target="_blank"><i class="fa-solid fa-file"></i></a>`:''}</div>`;
        }}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();

$('#dt').on('change','.status-select', function(){
    const id=$(this).data('id'); const status=$(this).val();
    fetch(`/admin/internship-enquiries/${id}`, {method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify({status})});
});

</script>
@endpush
