@extends('layouts.admin')
@section('title','Attendance reports')
@section('page_title','Attendance reports')

@section('content')
<div class="row g-2 mb-3">
    <div class="col-auto">
        <select id="type" class="form-select">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="student">Student-wise</option>
            <option value="batch">Batch-wise</option>
            <option value="faculty">Faculty-wise</option>
        </select>
    </div>
    <div class="col-auto">
        <input type="date" id="date" class="form-control" style="display:none;">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" type="button" id="btnLoad">Load</button>
    </div>
</div>

<div class="card card-soft"><div class="card-body">
<table class="table table-striped w-100" id="repTable"><thead id="thead"><tr></tr></thead><tbody></tbody></table>
</div></div>
@endsection

@push('scripts')
<script>
function thead(headers){ $('#thead').html('<tr>'+headers.map(h=>'<th>'+h+'</th>').join('')+'</tr>'); }

$('#type').on('change', function(){
    $('#date').toggle($(this).val()==='daily');
});

$('#btnLoad').on('click', function(){
    const type = $('#type').val();
    const qs = new URLSearchParams({type, date: $('#date').val()||''});
    fetch(`{{ route('admin.attendance-reports.data') }}?`+qs.toString()).then(r=>r.json()).then(res=>{
        const rows = res.data || [];
        if(type==='student'){ thead(['Student','Present','Absent','%']); }
        else if(type==='batch'){ thead(['Batch','Present','Absent','%']); }
        else if(type==='faculty'){ thead(['Faculty','Present','Absent','%']); }
        else { thead(['Date','Batch','Course','Present','Absent','%']); }

        const tbody = rows.map(r=>{
            if(type==='student') return `<tr><td>${r.student||''}</td><td>${r.present}</td><td>${r.absent}</td><td>${r.percent}</td></tr>`;
            if(type==='batch') return `<tr><td>${r.batch||''}</td><td>${r.present}</td><td>${r.absent}</td><td>${r.percent}</td></tr>`;
            if(type==='faculty') return `<tr><td>${r.faculty||''}</td><td>${r.present}</td><td>${r.absent}</td><td>${r.percent}</td></tr>`;
            return `<tr><td>${r.date}</td><td>${r.batch}</td><td>${r.course}</td><td>${r.present}</td><td>${r.absent}</td><td>${r.percent}</td></tr>`;
        }).join('');
        $('#repTable tbody').html(tbody);

        if($.fn.DataTable.isDataTable('#repTable')){ $('#repTable').DataTable().destroy(); }
        new DataTable('#repTable', {dom:'Bfrtip', buttons:['copy','csv','excel','pdf','print'], responsive:true});
    });
});
$('#type').trigger('change');
$('#btnLoad').trigger('click');
</script>
@endpush
