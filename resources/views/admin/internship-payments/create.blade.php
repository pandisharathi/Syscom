@extends('layouts.admin')
@section('title','Record Payment')
@section('page_title','Record Internship Payment')

@section('content')
<div class="card card-soft" style="max-width:700px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-payments.store') }}" id="payForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Student <span class="text-danger">*</span></label>
                    <select name="internship_student_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="">— Select —</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reference No.</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="Cheque/Transaction ID">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Save Payment</button>
                <a href="{{ route('admin.internship-payments.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#payForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        headers: {'Accept': 'application/json'},
        success: function(res){
            Swal.fire({icon:'success', title:'Payment recorded'}).then(()=>{
                window.location.href = '{{ route('admin.internship-payments.index') }}';
            });
        },
        error: function(xhr){
            const errors = xhr.responseJSON?.errors || {};
            let msg = '<div class="text-start">';
            Object.values(errors).flat().forEach(e => { msg += `<p class="mb-1">${e}</p>`; });
            msg += '</div>';
            Swal.fire({icon:'error', title:'Validation Error', html:msg});
        }
    });
});
</script>
@endpush
