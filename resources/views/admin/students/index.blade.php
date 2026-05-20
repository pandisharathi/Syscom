@extends('layouts.admin')
@section('title','Students')
@section('page_title','Students')

@section('content')
<button class="btn btn-primary btn-sm rounded-pill mb-3" id="btnAdd"><i class="fa-solid fa-plus me-1"></i>Add student</button>
<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="dt"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Status</th><th></th></tr></thead></table>
</div></div>
@endsection

@push('scripts')
<script>
function reload(){ table.destroy(); table = mk(); }
function mk(){ return new DataTable('#dt',{processing:true,serverSide:true,ajax:'{{ route('admin.students.data') }}',
    columns:[
        {data:null, render:r=>r.first_name+' '+r.last_name}, {data:'email'}, {data:'phone'}, {data:'gender'}, {data:'status'},
        {data:'id', orderable:false, searchable:false, render:id=>`<button class="btn btn-sm btn-outline-secondary btn-batch" data-id="${id}">Batches</button>
            <button class="btn btn-sm btn-outline-danger btn-del" data-id="${id}"><i class="fa-solid fa-trash"></i></button>`}
    ], dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true}); }
let table = mk();
$('#btnAdd').on('click', ()=>{
    Swal.fire({title:'Student', html:`<input id="fn" class="form-control mb-2"><input id="ln" class="form-control mb-2"><input id="email" class="form-control mb-2"><input id="phone" class="form-control mb-2">`,
        showCancelButton:true, preConfirm:()=>({first_name:$('#fn').val(),last_name:$('#ln').val(),email:$('#email').val(),phone:$('#phone').val(),status:'active'})
    }).then(r=>{ if(!r.isConfirmed) return; fetch(`{{ route('admin.students.store') }}`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify(r.value)}).then(reload); });
});
$('#dt').on('click','.btn-del', function(){ const id=$(this).data('id'); Swal.fire({showCancelButton:true}).then(r=>{ if(!r.isConfirmed) return; fetch(`/admin/students/${id}`, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(reload); }); });
$('#dt').on('click','.btn-batch', async function(){
    const sid=$(this).data('id');
    const b = await fetch(`{{ route('admin.batches.data') }}?length=500`).then(r=>r.json());
    const opts = (b.data||[]).map(x=>`<option value="${x.id}">${x.name||('Batch #'+x.id)}</option>`).join('');
    Swal.fire({title:'Assign batches', html:`<select id="batches" class="form-select" multiple style="min-height:140px">${opts}</select>`,
        showCancelButton:true,
        preConfirm:()=>Array.from(document.getElementById('batches').selectedOptions).map(o=>parseInt(o.value,10))
    }).then(r=>{ if(!r.isConfirmed) return; fetch(`/admin/students/${sid}/sync-batches`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}, body:JSON.stringify({batch_ids:r.value})}).then(()=>Swal.fire({icon:'success', title:'Updated'})); });
});
</script>
@endpush
