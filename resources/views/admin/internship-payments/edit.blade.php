@extends('layouts.admin')
@section('title','Edit Payment')
@section('page_title','Edit Payment')

@section('content')
<div class="card card-soft" style="max-width:700px">
    <div class="card-body">
        <form method="post" action="{{ route('admin.internship-payments.update', $internship_payment) }}" id="payForm">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Student <span class="text-danger">*</span></label>
                    <select name="internship_student_id" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ $internship_payment->internship_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="{{ $internship_payment->amount }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="{{ $internship_payment->payment_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="">— Select —</option>
                        @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','cheque'=>'Cheque','online'=>'Online','other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}" {{ $internship_payment->payment_mode === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reference No.</label>
                    <input type="text" name="reference_no" class="form-control" value="{{ $internship_payment->reference_no }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $internship_payment->notes }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Update Payment</button>
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
            Swal.fire({icon:'success', title:'Payment updated'}).then(()=>{
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
