@extends('layouts.admin')
@section('title','Edit expense')
@section('page_title','Edit expense')

@section('content')
<div class="card card-soft" style="max-width:900px"><div class="card-body">
<form method="post" action="{{ route('admin.expenses.update', $expense) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Expense type</label>
            <select name="expense_type_id" class="form-select" required id="et"></select>
        </div>
        <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Payment status</label>
            <select name="payment_status" class="form-select"><option value="paid" @selected($expense->payment_status==='paid')>Paid</option><option value="pending" @selected($expense->payment_status==='pending')>Pending</option></select>
        </div>
        <div class="col-12"><label class="form-label">Title</label><input name="title" value="{{ $expense->title }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" id="amt" value="{{ $expense->amount }}" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Tax</label><input type="number" step="0.01" name="tax" id="tax" value="{{ $expense->tax }}" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Total (auto)</label><input type="text" id="tot" class="form-control" readonly></div>
        <div class="col-md-6"><label class="form-label">Vendor</label><input name="vendor" value="{{ $expense->vendor }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Invoice #</label><input name="invoice_number" value="{{ $expense->invoice_number }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Payment method</label><input name="payment_method" value="{{ $expense->payment_method }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Reference #</label><input name="reference_number" value="{{ $expense->reference_number }}" class="form-control"></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ $expense->notes }}</textarea></div>
        <div class="col-12"><label class="form-label">Add attachments</label><input type="file" name="attachments[]" class="form-control" multiple></div>
    </div>
    @if($expense->attachments->count())
        <div class="mt-3"><div class="fw-semibold mb-2">Existing files</div>
            <ul>@foreach($expense->attachments as $a)<li><a href="{{ route('admin.expenses.attachments.download', [$expense, $a]) }}">{{ $a->original_name }}</a></li>@endforeach</ul>
        </div>
    @endif
    <button class="btn btn-primary mt-3">Update</button>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-link mt-3">Back</a>
</form>
</div></div>
@endsection

@push('scripts')
<script>
fetch(`{{ route('admin.expense-types.data') }}?length=500`).then(r=>r.json()).then(res=>{
    const opts=(res.data||[]).map(x=>`<option value="${x.id}">${x.code} — ${x.name}</option>`).join('');
    $('#et').html(opts).val('{{ $expense->expense_type_id }}');
});
function calc(){ const a=parseFloat($('#amt').val()||0), t=parseFloat($('#tax').val()||0); $('#tot').val((a+t).toFixed(2)); }
$('#amt,#tax').on('input', calc); calc();
</script>
@endpush
