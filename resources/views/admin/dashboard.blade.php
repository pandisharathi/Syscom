@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Students</div>
                <div class="display-6 fw-bold" id="cardStudents">—</div>
                <i class="fa-solid fa-user-graduate icon-bg"></i>
            </div>
        </div>
    </div>
    @if(auth()->user()->isSuperAdmin())
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#0ea5e9,#6366f1);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Institutions</div>
                <div class="display-6 fw-bold" id="cardInstitutions">—</div>
                <i class="fa-solid fa-building icon-bg"></i>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#10b981,#14b8a6);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Present (month)</div>
                <div class="display-6 fw-bold" id="cardPresent">—</div>
                <i class="fa-solid fa-check icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#f97316,#ef4444);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Expenses (month)</div>
                <div class="h3 fw-bold mt-2" id="cardExpense">—</div>
                <i class="fa-solid fa-wallet icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#06b6d4,#3b82f6);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Internship Students</div>
                <div class="display-6 fw-bold" id="cardIntStudents">—</div>
                <i class="fa-solid fa-briefcase icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#22c55e,#10b981);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Active Interns</div>
                <div class="display-6 fw-bold" id="cardIntActive">—</div>
                <i class="fa-solid fa-user-check icon-bg"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12"><hr class="my-1"><h6 class="fw-semibold text-muted mt-2"><i class="fa-solid fa-sack-dollar me-2"></i>Internship Payments</h6></div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#a855f7,#6366f1);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Today</div>
                <div class="h5 fw-bold mt-1" id="cardPayToday">—</div>
                <i class="fa-solid fa-calendar-day icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#8b5cf6,#6366f1);">
            <div class="card-body position-relative">
                <div class="small text-white-50">This Week</div>
                <div class="h5 fw-bold mt-1" id="cardPayWeek">—</div>
                <i class="fa-solid fa-calendar-week icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#7c3aed,#8b5cf6);">
            <div class="card-body position-relative">
                <div class="small text-white-50">This Month</div>
                <div class="h5 fw-bold mt-1" id="cardPayMonth">—</div>
                <i class="fa-solid fa-calendar-alt icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#6366f1,#a855f7);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Collected</div>
                <div class="h5 fw-bold mt-1" id="cardPayTotal">—</div>
                <i class="fa-solid fa-coins icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#4f46e5,#6366f1);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Total Fees</div>
                <div class="h5 fw-bold mt-1" id="cardPayTotalFees">—</div>
                <i class="fa-solid fa-file-invoice-dollar icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stat-card" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Pending</div>
                <div class="h5 fw-bold mt-1" id="cardPayPending">—</div>
                <i class="fa-solid fa-clock-rotate-left icon-bg"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12"><hr class="my-1"><h6 class="fw-semibold text-muted mt-2"><i class="fa-solid fa-clipboard-check me-2"></i>Internship Attendance</h6></div>
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#10b981,#14b8a6);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Present</div>
                <div class="display-6 fw-bold" id="cardIntPresent">—</div>
                <i class="fa-solid fa-user-check icon-bg"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="background: linear-gradient(135deg,#f97316,#ef4444);">
            <div class="card-body position-relative">
                <div class="small text-white-50">Absent</div>
                <div class="display-6 fw-bold" id="cardIntAbsent">—</div>
                <i class="fa-solid fa-user-times icon-bg"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-soft">
            <div class="card-body">
                <div class="fw-semibold mb-3">Internship enquiries by status</div>
                <canvas id="chartEnquiry" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold">Recent Internship Enquiries</div>
                    <a href="{{ route('admin.internship-enquiries.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="tblRecent">
                        <thead><tr><th>Name</th><th>Course</th><th>Status</th><th>Time</th><th></th></tr></thead>
                        <tbody id="recentBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-lg-12">
        <div class="card card-soft">
            <div class="card-body">
                <div class="fw-semibold mb-3">Expense trend (6 months)</div>
                <canvas id="chartExpense" height="180"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
fetch(`{{ route('admin.dashboard.charts') }}`)
    .then(r => r.json())
    .then(data => {
        document.getElementById('cardStudents').innerText = data.cards.students ?? 0;
        if (document.getElementById('cardInstitutions')) {
            document.getElementById('cardInstitutions').innerText = data.cards.institutions ?? 0;
        }
        document.getElementById('cardPresent').innerText = data.cards.present_this_month ?? 0;
        document.getElementById('cardExpense').innerText = (data.cards.expense_this_month ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardIntStudents').innerText = data.cards.internship_students ?? 0;
        document.getElementById('cardIntActive').innerText = data.cards.internship_active ?? 0;
        document.getElementById('cardPayToday').innerText = '₹' + (data.cards.payment_today ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardPayWeek').innerText = '₹' + (data.cards.payment_week ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardPayMonth').innerText = '₹' + (data.cards.payment_month ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardPayTotal').innerText = '₹' + (data.cards.payment_total ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardPayTotalFees').innerText = '₹' + (data.cards.payment_total_fees ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardPayPending').innerText = '₹' + (data.cards.payment_pending ?? 0).toLocaleString(undefined, {minimumFractionDigits:2});
        document.getElementById('cardIntPresent').innerText = data.cards.int_present ?? 0;
        document.getElementById('cardIntAbsent').innerText = data.cards.int_absent ?? 0;

        const labels = Object.keys(data.enquiry_by_status || {});
        const values = Object.values(data.enquiry_by_status || {});
        if(labels.length){
            new Chart(document.getElementById('chartEnquiry'), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: ['#6366f1','#22c55e','#f97316','#14b8a6','#ef4444'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        }

        const trend = data.expense_trend || [];
        if(trend.length){
            new Chart(document.getElementById('chartExpense'), {
            type: 'line',
            data: {
                labels: trend.map(x => x.ym),
                datasets: [{ label: 'Total', data: trend.map(x => parseFloat(x.total)), borderColor: '#6366f1', tension: .35, fill: true, backgroundColor: 'rgba(99,102,241,.12)' }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
        }

        const enquiries = data.recent_enquiries || [];
        const statusBadge = {'new':'primary','contacted':'warning','interested':'info','enrolled':'success','rejected':'danger'};
        let html = '';
        enquiries.forEach(e => {
            html += `<tr>
                <td><a href="/admin/internship-enquiries/${e.id}" class="text-decoration-none fw-medium">${e.name}</a></td>
                <td class="small text-muted">${e.course||'—'}</td>
                <td><span class="badge bg-${statusBadge[e.status]||'secondary'}">${e.status}</span></td>
                <td class="small text-muted">${e.created_at}</td>
                <td><a href="/admin/internship-enquiries/${e.id}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a></td>
            </tr>`;
        });
        if(!enquiries.length) html = '<tr><td colspan="5" class="text-center text-muted py-3">No enquiries yet</td></tr>';
        document.getElementById('recentBody').innerHTML = html;
    });
</script>
@endpush
