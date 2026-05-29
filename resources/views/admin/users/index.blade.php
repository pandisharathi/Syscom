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
            let btns = '';
            @if(auth()->user()->hasPermission('users.manage'))
                @if(auth()->user()->isSuperAdmin())
                btns += `<button class="btn btn-sm btn-outline-warning btn-reset-pw me-1" data-id="${id}" title="Reset Password"><i class="fa-solid fa-key"></i></button>`;
                @endif
                btns += `<button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}" title="Delete"><i class="fa-solid fa-trash"></i></button>`;
            @endif
            return btns;
        }}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();

@if(auth()->user()->hasPermission('users.manage'))
const roleOptions = `<option value="">Select Role</option>@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach`;
@if(auth()->user()->isSuperAdmin())
const instOptions = `<option value="">Select Institution (Optional)</option>@foreach($institutions as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach`;
@endif

$('#btnAdd').on('click', ()=>{
    let html = `<input id="name" class="form-control mb-2" placeholder="Name"><input id="email" class="form-control mb-2" placeholder="Email"><input id="pw" type="password" class="form-control mb-2" placeholder="Password">
        <select id="role_id" class="form-select mb-2">${roleOptions}</select>`;
    @if(auth()->user()->isSuperAdmin())
    html += `<select id="institution_id" class="form-select mb-2">${instOptions}</select>`;
    @endif

    Swal.fire({
        title:'User', 
        html: html,
        showCancelButton:true,
        preConfirm:()=>({
            name:$('#name').val(),
            email:$('#email').val(),
            password:$('#pw').val(),
            role_id:$('#role_id').val(),
            institution_id:$('#institution_id').length ? $('#institution_id').val() : null,
            status:'active'
        })
    }).then(r=>{ if(!r.isConfirmed) return; fetch(`{{ route('admin.users.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(r.value)}).then(()=>{table.destroy();table=mk();}); });
});
$('#dt').on('click','.btn-del',function(){ const id=$(this).data('id'); fetch(`/admin/users/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{table.destroy();table=mk();}); });

$('#dt').on('click','.btn-reset-pw',function(){
    const id = $(this).data('id');
    Swal.fire({
        title: 'Reset Password',
        input: 'password',
        inputPlaceholder: 'Enter new password (min 8 chars)',
        showCancelButton: true,
        confirmButtonText: 'Reset Password',
        preConfirm: (pw) => {
            if(!pw || pw.length < 8) {
                Swal.showValidationMessage('Password must be at least 8 characters');
                return false;
            }
            return pw;
        }
    }).then(r => {
        if(!r.isConfirmed) return;
        fetch(`/admin/users/${id}/reset-password`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
            },
            body: JSON.stringify({password: r.value})
        }).then(res => res.json()).then(data => {
            if(data.message) Swal.fire('Success', data.message, 'success');
        }).catch(() => Swal.fire('Error', 'Failed to reset password', 'error'));
    });
});
@endif
</script>
@endpush
