@extends('layouts.admin')
@section('title','Attendance')
@section('page_title','Attendance sessions')

@section('content')
<div class="alert alert-info small">Mark attendance per batch and date. Duplicate dates are blocked.</div>
<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="dt"><thead><tr><th>Date</th><th>Batch</th><th>Course</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.attendances.data') }}',
    columns:[
        {data:'date', name:'attendance_date'},
        {data:'batch'}, {data:'course'},
        {data:'batch_id', orderable:false, searchable:false, render:id=>`<a class="btn btn-sm btn-primary" href="/admin/attendances/batch/${id}/mark">Mark / View students</a>`}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true
});
</script>
@endpush
