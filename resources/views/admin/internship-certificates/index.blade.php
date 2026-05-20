@extends('layouts.admin')
@section('title','Certificate List')
@section('page_title','Certificate List')

@section('content')
<div class="card card-soft"><div class="card-body">
<table class="table table-hover w-100" id="dt">
    <thead><tr>
        <th>Certificate No</th>
        <th>Student Name</th>
        <th>Course</th>
        <th>Duration</th>
        <th>Issue Date</th>
        <th>QR Status</th>
        <th>Actions</th>
    </tr></thead>
</table>
</div></div>
@endsection

@push('scripts')
<script>
const dt = new DataTable('#dt',{
    processing:true, 
    serverSide:true,
    ajax:{ url:'{{ route('admin.internship-certificates.data') }}' },
    columns:[
        {data:'certificate_number', className:'fw-bold text-danger'},
        {data:'student_name'},
        {data:'course'},
        {data:'duration'},
        {data:'issue_date'},
        {data:'qr_status', render:(d,t,row)=>{
            const cls = row.status === 'active' ? 'bg-success' : 'bg-danger';
            return `<span class="badge ${cls}">${d}</span>`;
        }},
        {data:null, orderable:false, searchable:false, render:(data,type,row)=>{
            return `<div class="d-flex gap-1">
                <a class="btn btn-sm btn-outline-info" href="/admin/internship-certificates/${row.id}" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                <a class="btn btn-sm btn-outline-success" href="/admin/internship-certificates/${row.id}/download" title="Download PDF"><i class="fa-solid fa-download"></i></a>
                <button class="btn btn-sm btn-outline-primary btn-email" data-id="${row.id}" title="Email Student"><i class="fa-solid fa-envelope"></i></button>
                <button class="btn btn-sm btn-outline-warning btn-regenerate" data-id="${row.id}" title="Regenerate"><i class="fa-solid fa-rotate"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-revoke" data-id="${row.id}" title="Revoke"><i class="fa-solid fa-ban"></i></button>
            </div>`;
        }}
    ],
    dom:'Bfrtip', 
    buttons:[
        { extend: 'excel', className: 'btn btn-sm btn-success', text: '<i class="fa-solid fa-file-excel me-1"></i>Excel' },
        { extend: 'csv', className: 'btn btn-sm btn-info', text: '<i class="fa-solid fa-file-csv me-1"></i>CSV' },
        { extend: 'pdf', className: 'btn btn-sm btn-danger', text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF' },
        { extend: 'print', className: 'btn btn-sm btn-primary', text: '<i class="fa-solid fa-print me-1"></i>Print' }
    ],
    responsive:true,
    order:[[0,'desc']],
    columnDefs:[{responsivePriority:1,targets:[0,1,6]}]
});

function reload(){ dt.ajax.reload(); }

$(document).on('click','.btn-email',function(){
    const id = $(this).data('id');
    const btn = $(this);
    Swal.fire({
        title:'Email Certificate?', text:'The certificate will be sent to the student\'s email address.', icon:'question',
        showCancelButton:true, confirmButtonText:'Yes, Send it!'
    }).then(r=>{
        if(r.isConfirmed){
            btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.post(`/admin/internship-certificates/${id}/email`,{},function(res){
                Swal.fire({icon:'success',title:'Sent!',text:res.message,timer:2000,showConfirmButton:false});
            }).fail(x=>Swal.fire({icon:'error',title:'Error',text:x.responseJSON?.message||'Error'}))
            .always(()=>btn.prop('disabled',false).html('<i class="fa-solid fa-envelope"></i>'));
        }
    });
});

$(document).on('click','.btn-regenerate',function(){
    const id = $(this).data('id');
    Swal.fire({
        title:'Regenerate Certificate?', text:'A new verification token will be created.', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#f59e0b', confirmButtonText:'Regenerate'
    }).then(r=>{
        if(r.isConfirmed){
            $.post('/admin/internship-certificates/'+id+'/regenerate',{_method:'PUT'},function(res){
                Swal.fire({icon:'success',title:'Regenerated!',timer:1500,showConfirmButton:false});
                reload();
            }).fail(x=>Swal.fire({icon:'error',title:'Error',text:x.responseJSON?.message||'Error'}));
        }
    });
});

$(document).on('click','.btn-revoke',function(){
    const id = $(this).data('id');
    Swal.fire({
        title:'Revoke Certificate?', text:'The certificate will become invalid.', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Revoke'
    }).then(r=>{
        if(r.isConfirmed){
            $.ajax({
                url:'/admin/internship-certificates/'+id+'/revoke',
                method:'PUT',
                success:function(){
                    Swal.fire({icon:'success',title:'Revoked!',timer:1500,showConfirmButton:false});
                    reload();
                },
                error:x=>Swal.fire({icon:'error',title:'Error',text:x.responseJSON?.message||'Error'})
            });
        }
    });
});
</script>
@endpush
