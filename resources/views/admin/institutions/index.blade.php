@extends('layouts.admin')
@section('title','Institutions')
@section('page_title','Institution Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="fw-semibold text-secondary">Subscription plans & activation</div>
    <a href="{{ route('admin.institutions.create') }}" class="btn btn-primary rounded-pill btn-sm"><i class="fa-solid fa-plus me-1"></i>New institution</a>
</div>
<div class="card card-soft">
    <div class="card-body">
        <table class="table table-striped align-middle w-100" id="dtInstitutions">
            <thead><tr><th>Name</th><th>Code</th><th>Email</th><th>Plan</th><th>Active</th><th>Enquiry</th><th>Created</th><th></th></tr></thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new DataTable('#dtInstitutions', {
    processing: true,
    serverSide: true,
    ajax: { url: '{{ route('admin.institutions.data') }}' },
    columns: [
        { data: 'name', name: 'name' },
        { data: 'code', name: 'code' },
        { data: 'email', name: 'email' },
        { data: 'subscription_plan', name: 'subscription_plan' },
        { data: 'is_active', render: d => d ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' },
        { data: 'enquiry_enabled', render: d => d ? '<span class="badge bg-success">On</span>' : '<span class="badge bg-secondary">Off</span>' },
        { data: 'created_at', name: 'created_at' },
        { data: 'id', orderable:false, searchable:false, render: function(id, t, row){
            return `<div class="btn-group btn-group-sm">
                <a class="btn btn-outline-secondary" href="/admin/institutions/${id}/edit"><i class="fa-solid fa-pen"></i></a>
                <button class="btn btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
            </div>`;
        }}
    ],
    dom: 'Bfrtip',
    buttons: ['copy','csv','excel','pdf','print'],
    responsive: true
});

$('#dtInstitutions').on('click','.btn-del', function(){
    const id = $(this).data('id');
    Swal.fire({title:'Delete?', icon:'warning', showCancelButton:true}).then(res=>{
        if(!res.isConfirmed) return;
        fetch(`/admin/institutions/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}})
            .then(()=> location.reload());
    });
});
</script>
@endpush
