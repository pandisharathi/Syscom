@extends('layouts.admin')
@section('title','Add expense')
@section('page_title','Add expense')

@section('content')
<div class="card card-soft" style="max-width:900px"><div class="card-body">
<form method="post" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" id="expForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Expense type</label>
            <select name="expense_type_id" class="form-select" required></select>
        </div>
        <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
        <div class="col-md-4"><label class="form-label">Payment status</label>
            <select name="payment_status" class="form-select"><option value="paid">Paid</option><option value="pending">Pending</option></select>
        </div>
        <div class="col-12"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" id="amt" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Tax</label><input type="number" step="0.01" name="tax" id="tax" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Total (auto)</label><input type="text" id="tot" class="form-control" readonly></div>
        <div class="col-md-6"><label class="form-label">Vendor</label><input name="vendor" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Invoice #</label><input name="invoice_number" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Payment method</label><input name="payment_method" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Reference #</label><input name="reference_number" class="form-control"></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        <div class="col-12"><label class="form-label">Attachments</label><input type="file" name="attachments[]" class="form-control" multiple></div>
    </div>
    <button class="btn btn-primary mt-3">Save</button>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-link mt-3">Cancel</a>
</form>
</div></div>
@endsection

@push('scripts')
<script>
fetch(`{{ route('admin.expense-types.data') }}?length=500`).then(r=>r.json()).then(res=>{
    const opts=(res.data||[]).map(x=>`<option value="${x.id}">${x.code} — ${x.name}</option>`).join('');
    $('select[name=expense_type_id]').html(opts);
});
function calc(){ const a=parseFloat($('#amt').val()||0), t=parseFloat($('#tax').val()||0); $('#tot').val((a+t).toFixed(2)); }
$('#amt,#tax').on('input', calc); calc();
</script>
@endpush
