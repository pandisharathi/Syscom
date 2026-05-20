@extends('layouts.admin')
@section('title','Reports hub')
@section('page_title','Reports')

@section('content')
<div class="row g-3">
    <div class="col-md-4"><div class="card card-soft h-100"><div class="card-body"><div class="fw-semibold">Attendance</div><p class="small text-muted mb-3">Daily/weekly/monthly and dimensional breakdowns.</p><a href="{{ route('admin.attendance-reports.index') }}" class="btn btn-sm btn-primary rounded-pill">Open</a></div></div></div>
    <div class="col-md-4"><div class="card card-soft h-100"><div class="card-body"><div class="fw-semibold">Internship</div><p class="small text-muted mb-3">Enquiries, enrolment and demographics.</p><a href="{{ route('admin.internship-reports.index') }}" class="btn btn-sm btn-primary rounded-pill">Open</a></div></div></div>
    <div class="col-md-4"><div class="card card-soft h-100"><div class="card-body"><div class="fw-semibold">Expenses</div><p class="small text-muted mb-3">Periodic spend and vendor/payment analytics.</p><a href="{{ route('admin.expense-reports.index') }}" class="btn btn-sm btn-primary rounded-pill">Open</a></div></div></div>
</div>
@endsection
