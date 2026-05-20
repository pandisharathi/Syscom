@extends('layouts.admin')
@section('title','Expenses')
@section('page_title','Expense list')

@section('content')
<a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm rounded-pill mb-3"><i class="fa-solid fa-plus me-1"></i>Add expense</a>
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt"><thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Amount</th><th>Tax</th><th>Total</th><th>Vendor</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.expenses.data') }}',
    columns:[
        {data:'date'},{data:'type'},{data:'title'},{data:'amount'},{data:'tax'},{data:'total_amount'},{data:'vendor'},{data:'payment_status'},
        {data:'id',orderable:false,searchable:false,render:id=>`<a class="btn btn-sm btn-outline-secondary" href="/admin/expenses/${id}/edit"><i class="fa-solid fa-pen"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
});
$('#dt').on('click','.btn-del',function(){ const id=$(this).data('id'); Swal.fire({showCancelButton:true}).then(r=>{ if(!r.isConfirmed) return; fetch(`/admin/expenses/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(()=>location.reload()); }); });
</script>
@endpush
