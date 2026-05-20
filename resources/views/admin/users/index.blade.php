@extends('layouts.admin')
@section('title','Users')
@section('page_title','Users')

@section('content')
@if(auth()->user()->hasPermission('users.manage'))
<button class="btn btn-primary btn-sm rounded-pill mb-3" id="btnAdd"><i class="fa-solid fa-plus me-1"></i>Add user</button>
@endif
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Institution</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.users.data') }}',
    columns:[
        {data:'name'},{data:'email'},{data:'role'},{data:'institution'},{data:'status'},
        {data:'id', orderable:false, searchable:false, render:(id)=>{
            @if(auth()->user()->hasPermission('users.manage'))
            return `<button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`;
            @else
            return '';
            @endif
        }}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();

@if(auth()->user()->hasPermission('users.manage'))
const roleOptions = `@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach`;
$('#btnAdd').on('click', ()=>{
    Swal.fire({title:'User', html:`<input id="name" class="form-control mb-2" placeholder="Name"><input id="email" class="form-control mb-2" placeholder="Email"><input id="pw" type="password" class="form-control mb-2" placeholder="Password">
        <select id="role_id" class="form-select mb-2">${roleOptions}</select>` ,
        showCancelButton:true,
        preConfirm:()=>({name:$('#name').val(),email:$('#email').val(),password:$('#pw').val(),role_id:$('#role_id').val(),status:'active'})
    }).then(r=>{ if(!r.isConfirmed) return; fetch(`{{ route('admin.users.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(r.value)}).then(()=>{table.destroy();table=mk();}); });
});
$('#dt').on('click','.btn-del',function(){ const id=$(this).data('id'); fetch(`/admin/users/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{table.destroy();table=mk();}); });
@endif
</script>
@endpush
