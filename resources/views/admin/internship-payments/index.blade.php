@extends('layouts.admin')
@section('title','Internship Payments')
@section('page_title','Internship Payments')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-soft bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <i class="fa-solid fa-file-invoice-dollar fa-3x opacity-50"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-white-50">Total Expected Fees</h6>
                    <h2 class="card-title mb-0">₹{{ number_format($totalFees, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <i class="fa-solid fa-money-bill-trend-up fa-3x opacity-50"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-white-50">Collected Fees</h6>
                    <h2 class="card-title mb-0">₹{{ number_format($totalCollected, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft bg-warning text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <i class="fa-solid fa-clock-rotate-left fa-3x opacity-50"></i>
                </div>
                <div>
                    <h6 class="card-subtitle text-dark-50">Pending Fees</h6>
                    <h2 class="card-title mb-0">₹{{ number_format($totalPending, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Payment Records</h5>
            <a href="{{ route('admin.internship-payments.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>Record Payment
            </a>
        </div>
        <table class="table w-100" id="dt">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new DataTable('#dt',{
    processing:true, serverSide:true, ajax:'{{ route('admin.internship-payments.data') }}',
    columns:[
        {data:'payment_date'}, {data:'student'},
        {data:'amount', render:v=>'$'+parseFloat(v).toFixed(2)},
        {data:'payment_mode'}, {data:'reference_no'},
        {data:null, orderable:false, searchable:false, render:(data,type,row)=>{
            const id = row.id;
            return `<div class="d-flex gap-1">
                <a class="btn btn-sm btn-outline-primary" href="/admin/internship-payments/${id}/edit"><i class="fa-solid fa-pen"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
            </div>`;
        }}
    ],
    dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true,
    order:[[0,'desc']]
});

$(document).on('click','.btn-del',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete payment?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef4444'}).then(r=>{
        if(!r.isConfirmed) return;
        $.ajax({url:`/admin/internship-payments/${id}`,method:'DELETE',headers:{'Accept':'application/json'},success:()=>{$('#dt').DataTable().ajax.reload();}});
    });
});
</script>
@endpush
