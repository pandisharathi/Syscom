@extends('layouts.admin')
@section('title','Expense reports')
@section('page_title','Expense reports')

@section('content')
<div class="row g-2 mb-3">
    <div class="col-auto"><select id="type" class="form-select"><option value="monthly">Monthly (this month)</option><option value="weekly">Weekly</option><option value="yearly">Yearly</option><option value="daily">Daily</option><option value="vendor">Vendor-wise</option><option value="payment">Payment-wise</option><option value="pending">Pending</option></select></div>
    <div class="col-auto"><input type="date" id="date" class="form-control" style="display:none;"></div>
    <div class="col-auto"><button class="btn btn-primary" id="btn" type="button">Load</button></div>
</div>
<div class="card card-soft"><div class="card-body"><table class="table w-100" id="rep"><thead id="thead"><tr></tr></thead><tbody></tbody></table></div></div>
@endsection

@push('scripts')
<script>
$('#type').on('change', function(){ $('#date').toggle($(this).val()==='daily'); });
$('#btn').on('click', function(){
    const qs = new URLSearchParams({type:$('#type').val(), date:$('#date').val()||''});
    fetch(`{{ route('admin.expense-reports.data') }}?`+qs.toString()).then(r=>r.json()).then(res=>{
        const rows = res.data||[];
        const t = $('#type').val();
        if(t==='vendor'){ $('#thead').html('<tr><th>Vendor</th><th>Total</th></tr>'); $('#rep tbody').html(rows.map(r=>`<tr><td>${r.vendor}</td><td>${r.total}</td></tr>`).join('')); }
        else if(t==='payment'){ $('#thead').html('<tr><th>Method</th><th>Total</th></tr>'); $('#rep tbody').html(rows.map(r=>`<tr><td>${r.payment_method}</td><td>${r.total}</td></tr>`).join('')); }
        else if(t==='pending'){ $('#thead').html('<tr><th>Title</th><th>Date</th><th>Total</th></tr>'); $('#rep tbody').html(rows.map(r=>`<tr><td>${r.title}</td><td>${r.date}</td><td>${r.total}</td></tr>`).join('')); }
        else if(t==='yearly'){ $('#thead').html('<tr><th>Month</th><th>Total</th></tr>'); $('#rep tbody').html(rows.map(r=>`<tr><td>${r.m}</td><td>${r.total}</td></tr>`).join('')); }
        else { $('#thead').html('<tr><th>Period</th><th>Total</th></tr>'); $('#rep tbody').html(rows.map(r=>`<tr><td>${r.d||r.ym}</td><td>${r.total}</td></tr>`).join('')); }
        if($.fn.DataTable.isDataTable('#rep')) $('#rep').DataTable().destroy();
        new DataTable('#rep',{dom:'Bfrtip',buttons:['copy','csv','excel','pdf','print'],responsive:true});
    });
});
$('#btn').trigger('click');
</script>
@endpush
