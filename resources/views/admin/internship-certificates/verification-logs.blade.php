@extends('layouts.admin')
@section('title','Verification Logs')
@section('page_title','Certificate Verification Logs')

@section('content')
<div class="card card-soft"><div class="card-body">
<table class="table table-hover w-100" id="dt">
    <thead><tr>
        <th>Certificate No</th>
        <th>Student Name</th>
        <th>IP Address</th>
        <th>Verified At</th>
    </tr></thead>
</table>
</div></div>
@endsection

@push('scripts')
<script>
new DataTable('#dt',{
    processing:true, 
    serverSide:true,
    ajax:{ url:'{{ route('admin.internship-certificates.verification-logs-data') }}' },
    columns:[
        {data:'certificate_number', className:'fw-bold text-danger'},
        {data:'student_name'},
        {data:'ip_address'},
        {data:'verified_at'}
    ],
    dom:'Bfrtip', 
    buttons:[
        { extend: 'excel', className: 'btn btn-sm btn-success', text: '<i class="fa-solid fa-file-excel me-1"></i>Excel' },
        { extend: 'csv', className: 'btn btn-sm btn-info', text: '<i class="fa-solid fa-file-csv me-1"></i>CSV' },
        { extend: 'pdf', className: 'btn btn-sm btn-danger', text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF' },
        { extend: 'print', className: 'btn btn-sm btn-primary', text: '<i class="fa-solid fa-print me-1"></i>Print' }
    ],
    responsive:true,
    order:[[3,'desc']]
});
</script>
@endpush
