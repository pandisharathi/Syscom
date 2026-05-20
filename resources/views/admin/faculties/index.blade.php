@extends('layouts.admin')
@section('title','Faculty')
@section('page_title','Faculty')

@section('content')
<button class="btn btn-primary btn-sm rounded-pill mb-3" id="btnAdd"><i class="fa-solid fa-plus me-1"></i>Add faculty</button>
<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="dt"><thead><tr><th>Name</th><th>Qualification</th><th>Experience</th><th>Email</th><th>Phone</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function reload(){ table.destroy(); table = mk(); }
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.faculties.data') }}',
    columns:[
        {data:null, render:r=>r.first_name+' '+r.last_name},
        {data:'qualification'}, {data:'experience_years'}, {data:'email'}, {data:'phone'},
        {data:'status'},
        {data:'id', orderable:false, searchable:false, render:id=>`<button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();
$('#btnAdd').on('click', ()=>{
    Swal.fire({title:'Faculty', html:`<input id="fn" class="form-control mb-2" placeholder="First name"><input id="ln" class="form-control mb-2" placeholder="Last name"><input id="qual" class="form-control mb-2" placeholder="Qualification"><input id="exp" type="number" class="form-control mb-2" placeholder="Years exp"><input id="email" class="form-control mb-2" placeholder="Email"><input id="phone" class="form-control mb-2" placeholder="Phone">`,
        showCancelButton:true,
        preConfirm:()=>({first_name:$('#fn').val(),last_name:$('#ln').val(),qualification:$('#qual').val(),experience_years:$('#exp').val(),email:$('#email').val(),phone:$('#phone').val(),status:'active'})
    }).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`{{ route('admin.faculties.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(r.value)}).then(reload);
    });
});
$('#dt').on('click','.btn-del', function(){ const id=$(this).data('id'); Swal.fire({title:'Delete?',showCancelButton:true}).then(r=>{ if(!r.isConfirmed) return; fetch(`/admin/faculties/${id}`, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(reload); }); });
</script>
@endpush
