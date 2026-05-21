@extends('layouts.admin')
@section('title', 'Invoice Reports')
@section('page_title', 'Invoice Reports')

@section('content')
<div class="card card-soft mb-4">
    <div class="card-body">
        <h5 class="mb-3">Filter Reports</h5>
        <form id="filter-form" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" id="filter_type" class="form-select">
                    <option value="">All</option>
                    <option value="customer">Customer</option>
                    <option value="supplier">Supplier</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Customer</label>
                <select name="customer_id" id="filter_customer" class="form-select">
                    <option value="">All</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" id="filter_supplier" class="form-select">
                    <option value="">All</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" id="filter_status" class="form-select">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Completed/Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" id="filter_from" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" id="filter_to" class="form-control">
            </div>
            <div class="col-12 text-end">
                <button type="button" class="btn btn-secondary me-2" id="btn-reset">Reset</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-soft">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover w-100" id="reports-table">
                <thead class="bg-light">
                    <tr>
                        <th>Inv No</th>
                        <th>Date</th>
                        <th>Party</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table = $('#reports-table').DataTable({
        processing: true,
        serverSide: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm mb-3' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm mb-3' },
            { extend: 'print', className: 'btn btn-info btn-sm mb-3' }
        ],
        ajax: {
            url: '{{ route('admin.invoice-reports.data') }}',
            data: function (d) {
                d.type = $('#filter_type').val();
                d.customer_id = $('#filter_customer').val();
                d.supplier_id = $('#filter_supplier').val();
                d.status = $('#filter_status').val();
                d.from_date = $('#filter_from').val();
                d.to_date = $('#filter_to').val();
            }
        },
        columns: [
            {data: 'invoice_number', name: 'invoice_number'},
            {data: 'invoice_date', name: 'invoice_date'},
            {data: 'party', name: 'party'},
            {data: 'description', name: 'description'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'status', name: 'status', orderable: false, searchable: false}
        ]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#btn-reset').on('click', function() {
        $('#filter-form')[0].reset();
        table.ajax.reload();
    });
</script>
@endpush
