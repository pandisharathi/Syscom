@extends('layouts.admin')
@section('title', 'Customers Management')
@section('page_title', 'Customers')

@section('content')
<div class="card card-soft mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Customer List</h5>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fa fa-plus-circle me-1"></i> Add Customer
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover w-100" id="customers-table">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="modalTitle">Add Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerForm">
                <div class="modal-body">
                    <input type="hidden" id="customer_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" checked value="1">
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        ajax: '{{ route('admin.customers.data') }}',
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ]
    });

    const modal = new bootstrap.Modal(document.getElementById('customerModal'));

    function openAddModal() {
        $('#customerForm')[0].reset();
        $('#customer_id').val('');
        $('#modalTitle').text('Add Customer');
        modal.show();
    }

    $(document).on('click', '.edit-btn', function() {
        let row = $(this).data('row');
        $('#customer_id').val(row.id);
        $('#name').val(row.name);
        $('#email').val(row.email);
        $('#phone').val(row.phone);
        $('#address').val(row.address);
        $('#is_active').prop('checked', row.is_active);
        $('#modalTitle').text('Edit Customer');
        modal.show();
    });

    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#customer_id').val();
        let url = id ? `{{ url('admin/customers') }}/${id}` : '{{ route('admin.customers.store') }}';
        let method = id ? 'PUT' : 'POST';
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(res) {
                if (res.success) {
                    modal.hide();
                    Swal.fire('Success', res.message, 'success');
                    table.ajax.reload();
                }
            },
            error: function(err) {
                Swal.fire('Error', 'An error occurred while saving.', 'error');
            }
        });
    });

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
                    url: `{{ url('admin/customers') }}/${id}`,
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
