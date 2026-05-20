@extends('layouts.admin')
@section('title','Internship Student Details')
@section('page_title','Internship Student Details')

@section('content')
<div class="card card-soft" style="max-width:960px">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                @if($internship_student->photo)
                    <img src="{{ $internship_student->photo_url }}" class="rounded-circle img-thumbnail" style="width:150px;height:150px;object-fit:cover">
                @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" style="width:150px;height:150px;">
                        <i class="fa-solid fa-user fa-4x text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-9">
                <table class="table table-borderless mb-0">
                    <tr><th style="width:160px" class="text-muted">Reg No</th><td>{{ $internship_student->reg_no ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Name</th><td><strong>{{ $internship_student->full_name }}</strong></td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ $internship_student->email ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Phone</th><td>{{ $internship_student->phone ?? '—' }}</td></tr>
                    <tr><th class="text-muted">WhatsApp</th><td>{{ $internship_student->whatsapp_number ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Gender</th><td>{{ ucfirst($internship_student->gender ?? '—') }}</td></tr>
                    <tr><th class="text-muted">Date of Birth</th><td>{{ $internship_student->date_of_birth?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Course</th><td>{{ $internship_student->batch?->course?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Batch</th><td>{{ $internship_student->batch?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Joining Date</th><td>{{ $internship_student->joining_date?->format('M d, Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Status</th><td><span class="badge bg-{{ $internship_student->status==='active'?'success':($internship_student->status==='completed'?'primary':($internship_student->status==='relieved'?'warning':'secondary')) }}">{{ $internship_student->status }}</span></td></tr>
                </table>
            </div>
            <div class="col-12">
                <hr>
                <div class="row">
                    <div class="col-md-3"><strong class="text-muted d-block">Qualification</strong>{{ $internship_student->educational_qualification ?? '—' }}</div>
                    <div class="col-md-3"><strong class="text-muted d-block">Department</strong>{{ $internship_student->department ?? '—' }}</div>
                    <div class="col-md-3"><strong class="text-muted d-block">College</strong>{{ $internship_student->college_name ?? '—' }}</div>
                    <div class="col-md-3"><strong class="text-muted d-block">City / State</strong>{{ $internship_student->city ?? '—' }}{{ $internship_student->state ? ', '.$internship_student->state : '' }}</div>
                    @if($internship_student->address)
                    <div class="col-12 mt-2"><strong class="text-muted d-block">Address</strong>{{ $internship_student->address }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.internship-students.edit', $internship_student) }}" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i>Edit</a>
            <a href="{{ route('admin.internship-students.index') }}" class="btn btn-link">Back to List</a>
        </div>
    </div>
</div>

<div class="card card-soft mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">Payment History</h6>
            <a href="{{ route('admin.internship-payments.create') }}?student_id={{ $internship_student->id }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Add Payment</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="tblPayments">
                <thead><tr><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Notes</th><th>Received By</th></tr></thead>
                <tbody id="payBody"></tbody>
            </table>
        </div>
        <div class="text-end mt-2 fw-semibold" id="totalPaid"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
fetch(`{{ route('admin.internship-payments.student-data', $internship_student) }}`)
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
        document.getElementById('payBody').innerHTML = html;
        document.getElementById('totalPaid').innerText = 'Total Paid: $' + total.toFixed(2);
    });
</script>
@endpush
