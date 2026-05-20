@extends('layouts.admin')
@section('title','Certificate Templates')
@section('page_title','Certificate Templates')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.internship-certificates.templates.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>New Template</a>
</div>
<div class="card card-soft"><div class="card-body">
<div class="table-responsive">
<table class="table">
    <thead><tr><th>Name</th><th>Primary</th><th>Secondary</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($templates as $t)
        <tr>
            <td class="fw-semibold">{{ $t->name }}</td>
            <td><span class="badge" style="background:{{ $t->primary_color }}">{{ $t->primary_color }}</span></td>
            <td><span class="badge" style="background:{{ $t->secondary_color }}">{{ $t->secondary_color }}</span></td>
            <td>{!! $t->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td>
                <a href="{{ route('admin.internship-certificates.templates.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-edit"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete-template" data-id="{{ $t->id }}"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No templates yet</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div></div>
@endsection

@push('scripts')
<script>
$(document).on('click','.btn-delete-template',function(){
    const id = $(this).data('id');
    Swal.fire({
        title:'Delete template?', text:'This cannot be undone', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
    }).then(r=>{
        if(r.isConfirmed){
            $.ajax({
                url:'/admin/internship-certificates/templates/'+id,
                method:'DELETE',
                success:function(){
                    Swal.fire({icon:'success',title:'Deleted',timer:1500,showConfirmButton:false});
                    location.reload();
                },
                error:function(xhr){
                    Swal.fire({icon:'error',title:'Error',text:xhr.responseJSON?.message||'Error'});
                }
            });
        }
    });
});
</script>
@endpush
