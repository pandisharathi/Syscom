@extends('layouts.admin')
@section('title','Expense types')
@section('page_title','Expense types')

@section('content')
<button class="btn btn-primary btn-sm rounded-pill mb-3" id="btnAdd"><i class="fa-solid fa-plus me-1"></i>Add type</button>
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt"><thead><tr><th>Code</th><th>Name</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.expense-types.data') }}',
    columns:[{data:'code'},{data:'name'},{data:'status'},{data:'id',orderable:false,searchable:false,render:id=>
        `<button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}],
    dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();
$('#btnAdd').on('click',()=>{ Swal.fire({title:'Expense type', html:`<input id="code" class="form-control mb-2"><input id="name" class="form-control mb-2">`, showCancelButton:true,
    preConfirm:()=>({code:$('#code').val(), name:$('#name').val(), status:'active'})
}).then(r=>{ if(!r.isConfirmed) return; fetch(`{{ route('admin.expense-types.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(r.value)}).then(()=>{table.destroy();table=mk();}); }); });
$('#dt').on('click','.btn-del',function(){ const id=$(this).data('id'); fetch(`/admin/expense-types/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>{table.destroy();table=mk();}); });
</script>
@endpush
