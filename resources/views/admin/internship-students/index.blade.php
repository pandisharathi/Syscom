@extends('layouts.admin')
@section('title','Internship Students')
@section('page_title','Internship Students')

@section('content')
<a href="{{ route('admin.internship-students.create') }}" class="btn btn-primary btn-sm rounded-pill mb-3"><i class="fa-solid fa-plus me-1"></i>Enroll Student</a>
<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt">
    <thead><tr><th>ID</th><th>Reg No</th><th>Photo</th><th>Name</th><th>Course</th><th>Batch</th><th>Contact</th><th>College</th><th>Status</th><th>Actions</th></tr></thead>
</table>
</div></div>

<div class="modal fade" id="payHistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Payment History</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <h6 class="fw-semibold mb-2" id="histStudentName"></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Notes</th><th>Received By</th></tr></thead>
                        <tbody id="histBody"></tbody>
                    </table>
                </div>
                <div class="text-end mt-2 fw-semibold" id="histTotal"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reload(){ table.destroy(); table = mk(); }
function mk(){
    return new DataTable('#dt',{
        processing:true, serverSide:true, ajax:'{{ route('admin.internship-students.data') }}',
        columns:[
            {data:'id'},
            {data:'reg_no'},
            {data:'photo_url', orderable:false, searchable:false, render:d=>d?`<img src="${d}" class="rounded" style="width:36px;height:36px;object-fit:cover">`:'<span class="text-muted">—</span>'},
            {data:null, render:r=>`<a href="/admin/internship-students/${r.id}" class="text-decoration-none fw-semibold">${r.first_name} ${r.last_name}</a>`},
            {data:'course'}, {data:'batch'},
            {data:null, render:r=>`<div>${r.email??''}</div><small class="text-muted">${r.phone??''}</small>`},
            {data:'college_name'}, {data:'status', render:d=>{
                const map={active:'success',inactive:'secondary',completed:'primary',relieved:'warning'};
                return `<span class="badge bg-${map[d]||'secondary'}">${d}</span>`;
            }},
            {data:null, orderable:false, searchable:false, render:r=>{
                const view = `<a class="btn btn-sm btn-outline-info" href="/admin/internship-students/${r.id}"><i class="fa-solid fa-eye"></i></a>`;
                const edit = `<a class="btn btn-sm btn-outline-secondary" href="/admin/internship-students/${r.id}/edit"><i class="fa-solid fa-pen"></i></a>`;
                const del = `<button class="btn btn-sm btn-outline-danger btn-del" data-id="${r.id}"><i class="fa-solid fa-trash"></i></button>`;

                let extra = '';
                if (r.payment_status === 'paid') {
                    extra = `<button class="btn btn-sm btn-outline-warning btn-hist" data-id="${r.id}" data-name="${r.full_name}"><i class="fa-solid fa-receipt"></i></button>`;
                    extra += `<a class="btn btn-sm btn-outline-success" href="/admin/internship-payments/students/${r.id}/certificate" target="_blank"><i class="fa-solid fa-award"></i></a>`;
                } else if (r.payment_status === 'partial') {
                    extra = `<button class="btn btn-sm btn-outline-warning btn-hist" data-id="${r.id}" data-name="${r.full_name}"><i class="fa-solid fa-receipt"></i></button>`;
                    extra += `<button class="btn btn-sm btn-outline-primary btn-pay" data-id="${r.id}" data-name="${r.full_name}"><i class="fa-solid fa-dollar-sign"></i></button>`;
                } else {
                    extra = `<button class="btn btn-sm btn-outline-primary btn-pay" data-id="${r.id}" data-name="${r.full_name}"><i class="fa-solid fa-dollar-sign"></i></button>`;
                }

                return `<div class="d-flex gap-1">${view}${edit}${del} ${extra}</div>`;
            }}
        ],
        dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true,
        columnDefs:[{responsivePriority:1,targets:[0,3,9]}]
    });
}
let table = mk();

$('#dt').on('click','.btn-del',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete student?', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33'}).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`/admin/internship-students/${id}`,{method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content')}}).then(reload);
    });
});

$('#dt').on('click','.btn-hist',function(){
    const id=$(this).data('id');
    const name=$(this).data('name');
    $('#histStudentName').text(name);
    $('#histBody').html('<tr><td colspan="6" class="text-center py-3"><span class="spinner-border spinner-border-sm me-1"></span>Loading...</td></tr>');
    $('#payHistModal').modal('show');

    fetch(`/admin/internship-payments/student/${id}`)
        .then(r=>r.json())
        .then(res=>{
            let html = '';
            let total = 0;
            (res.data||[]).forEach(p => {
                html += `<tr>
                    <td>${p.payment_date}</td>
                    <td>$${parseFloat(p.amount).toFixed(2)}</td>
                    <td><span class="badge bg-info">${p.payment_mode}</span></td>
                    <td class="small">${p.reference_no||'—'}</td>
                    <td class="small text-muted">${p.notes||'—'}</td>
                    <td class="small text-muted">${p.received_by||'—'}</td>
                </tr>`;
                total += parseFloat(p.amount);
            });
            if(!html) html = '<tr><td colspan="6" class="text-center text-muted py-3">No payments recorded</td></tr>';
            document.getElementById('histBody').innerHTML = html;
            document.getElementById('histTotal').innerText = 'Total Paid: $' + total.toFixed(2);
        });
});

$('#dt').on('click','.btn-pay',function(){
    const id=$(this).data('id');
    const name=$(this).data('name');
    Swal.fire({
        title:'Record Payment for ' + name,
        html:`
            <input type="hidden" id="payStudentId" value="${id}">
            <div class="mb-2 text-start"><label class="form-label">Amount</label><input type="number" step="0.01" min="0.01" id="payAmount" class="form-control" placeholder="Enter amount"></div>
            <div class="mb-2 text-start"><label class="form-label">Date</label><input type="date" id="payDate" class="form-control" value="{{ date('Y-m-d') }}"></div>
            <div class="mb-2 text-start"><label class="form-label">Mode</label><select id="payMode" class="form-select"><option value="">Select</option><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option><option value="online">Online</option><option value="other">Other</option></select></div>
            <div class="mb-2 text-start"><label class="form-label">Reference No</label><input type="text" id="payRef" class="form-control"></div>
            <div class="mb-2 text-start"><label class="form-label">Notes</label><textarea id="payNotes" class="form-control" rows="2"></textarea></div>
        `,
        showCancelButton:true, confirmButtonText:'Record Payment', confirmButtonColor:'#4f46e5',
        preConfirm:()=>{
            const amount = document.getElementById('payAmount').value;
            const date = document.getElementById('payDate').value;
            const mode = document.getElementById('payMode').value;
            if(!amount || parseFloat(amount)<=0){ Swal.showValidationMessage('Enter a valid amount'); return false; }
            if(!date){ Swal.showValidationMessage('Select a date'); return false; }
            if(!mode){ Swal.showValidationMessage('Select payment mode'); return false; }
            return $.ajax({
                url:`/admin/internship-payments/students/${id}/pay`,
                method:'POST',
                data:{amount,payment_date:date,payment_mode:mode,reference_no:document.getElementById('payRef').value,notes:document.getElementById('payNotes').value}
            });
        }
    }).then(r=>{
        if(r.isConfirmed){
            Swal.fire({icon:'success',title:'Done!',text:r.value?.message||'Payment recorded',timer:1500,showConfirmButton:false});
            reload();
        }
    });
});
</script>
@endpush
