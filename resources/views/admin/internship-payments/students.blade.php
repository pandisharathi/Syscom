@extends('layouts.admin')
@section('title','Student Payments')
@section('page_title','Student Payments')

@section('content')
<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select class="form-select" id="filterCourse">
                    <option value="">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Batch</label>
                <select class="form-select" id="filterBatch">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" id="btnFilter"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                <button class="btn btn-outline-secondary" id="btnReset"><i class="fa-solid fa-undo me-1"></i>Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft"><div class="card-body">
<table class="table w-100" id="dt">
    <thead><tr><th>Name</th><th>Course</th><th>Batch</th><th>Total Paid</th><th>Fees</th><th>Status</th><th>Actions</th></tr></thead>
</table>
</div></div>

<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="payForm">
            @csrf
            <div class="modal-header"><h6 class="modal-title">Record Payment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="student_id" id="payStudentId">
                <div class="mb-3">
                    <label class="form-label">Student</label>
                    <p class="fw-semibold mb-0" id="payStudentName"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="">Select</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference No</label>
                    <input type="text" name="reference_no" class="form-control" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="paySubmit"><i class="fa-solid fa-check me-1"></i>Record Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reload(){ dt.ajax.reload(); }

const dt = new DataTable('#dt',{
    processing:true, serverSide:true,
    ajax:{
        url:'{{ route('admin.internship-payments.students-data') }}',
        data:function(d){
            d.course_id = $('#filterCourse').val();
            d.batch_id = $('#filterBatch').val();
        }
    },
    columns:[
        {data:null, render:r=>`<a href="/admin/internship-students/${r.id}" class="text-decoration-none fw-semibold">${r.full_name}</a><br><small class="text-muted">${r.email??''}</small>`},
        {data:'course'}, {data:'batch'},
        {data:'total_paid', render:v=>'$'+parseFloat(v).toFixed(2)},
        {data:'fees', render:v=>'$'+parseFloat(v).toFixed(2)},
        {data:'payment_status', render:d=>{
            const map={paid:'success',partial:'warning',pending:'secondary'};
            return `<span class="badge bg-${map[d]||'secondary'}">${d}</span>`;
        }},
        {data:null, orderable:false, searchable:false, render:(data,type,row)=>{
            const payBtn = `<button class="btn btn-sm btn-outline-success btn-pay" data-id="${row.id}" data-name="${row.full_name}"><i class="fa-solid fa-dollar-sign"></i></button>`;
            const certIcon = row.has_paid ? `<a class="btn btn-sm btn-outline-info" href="/admin/internship-payments/students/${row.id}/certificate" target="_blank"><i class="fa-solid fa-award"></i></a>` : '';
            return `<div class="d-flex gap-1">${payBtn} ${certIcon}</div>`;
        }}
    ],
    dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true,
    order:[[0,'desc']],
    columnDefs:[{responsivePriority:1,targets:[0,6]}]
});

$('#btnFilter').on('click', reload);
$('#btnReset').on('click',function(){
    $('#filterCourse').val('');
    $('#filterBatch').val('');
    reload();
});

$(document).on('click','.btn-pay',function(){
    $('#payStudentId').val($(this).data('id'));
    $('#payStudentName').text($(this).data('name'));
    $('#payForm')[0].reset();
    $('#payForm input[name="payment_date"]').val('{{ date("Y-m-d") }}');
    $('#payModal').modal('show');
});

$('#payForm').on('submit',function(e){
    e.preventDefault();
    const id = $('#payStudentId').val();
    const btn = $('#paySubmit');
    btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    $.ajax({
        url:`/admin/internship-payments/students/${id}/pay`,
        method:'POST',
        data:$(this).serialize(),
        success:function(res){
            $('#payModal').modal('hide');
            Swal.fire({icon:'success',title:'Done!',text:res.message,timer:1500,showConfirmButton:false});
            reload();
        },
        error:function(xhr){
            const msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : (xhr.responseJSON?.message || 'Error');
            Swal.fire({icon:'error',title:'Error',html:msg});
        },
        complete:function(){
            btn.prop('disabled',false).html('<i class="fa-solid fa-check me-1"></i>Record Payment');
        }
    });
});
</script>
@endpush