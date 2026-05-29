@extends('layouts.admin')
@section('title', 'Quotations')
@section('page_title', 'Quotations')

@section('content')
<div class="card card-soft mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Quotation List</h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="me-2">
                        <select id="status-filter" class="form-select form-select-sm" style="width:auto;">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="final">Final</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="invoiced">Invoiced</option>
                        </select>
                    </div>
                    <div class="me-2">
                        <input type="date" id="start-date" class="form-control form-control-sm" placeholder="Start Date"/>
                    </div>
                    <div class="me-2">
                        <input type="date" id="end-date" class="form-control form-control-sm" placeholder="End Date"/>
                    </div>
                    <button id="filter-btn" class="btn btn-sm btn-outline-primary">Filter</button>
                </div>
            <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle me-1"></i> Create Quotation
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover w-100" id="quotations-table">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>QT No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table = $('#quotations-table').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        ajax: {
            url: '{{ route('admin.quotations.data') }}',
            data: function (d) {
                d.status = $('#status-filter').val();
                d.start_date = $('#start-date').val();
                d.end_date = $('#end-date').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'quotation_number', name: 'quotation_number'},
            {data: 'quotation_date', name: 'quotation_date'},
            {data: 'customer', name: 'customer'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ]
    });

    $('#filter-btn').on('click', function () { table.ajax.reload(); });

    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('admin/quotations') }}/${id}`,
                    type: 'DELETE',
                    success: function(res) {
                        Swal.fire('Deleted!', res.message, 'success');
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endpush
