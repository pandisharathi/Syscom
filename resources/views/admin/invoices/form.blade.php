@extends('layouts.admin')
@section('title', isset($invoice) ? 'Edit Invoice' : 'Create Invoice')
@section('page_title', isset($invoice) ? 'Edit Invoice' : 'Create Invoice')

@section('content')
<form action="{{ isset($invoice) ? route('admin.invoices.update', $invoice->id) : route('admin.invoices.store') }}" method="POST">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif
    
    <div class="row">
        <!-- Invoice Details -->
        <div class="col-md-4 mb-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h6 class="mb-3 text-primary">Invoice Details</h6>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="customer" {{ (isset($invoice) && $invoice->type == 'customer') ? 'selected' : '' }}>Customer</option>
                            <option value="supplier" {{ (isset($invoice) && $invoice->type == 'supplier') ? 'selected' : '' }}>Supplier</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customer_div">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (isset($invoice) && $invoice->customer_id == $customer->id) ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="supplier_div" style="display:none;">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (isset($invoice) && $invoice->supplier_id == $supplier->id) ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control" required value="{{ isset($invoice) ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (Goods/Services)</label>
                        <textarea name="description" class="form-control" rows="2">{{ $invoice->description ?? '' }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ (isset($invoice) && $invoice->status == 'pending') ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ (isset($invoice) && $invoice->status == 'paid') ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ (isset($invoice) && $invoice->status == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="col-md-8 mb-4">
            <div class="card card-soft">
                <div class="card-body">
                    <h6 class="mb-3 text-primary">Invoice Items</h6>
                    <table class="table" id="items-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Description</th>
                                <th width="120">Qty</th>
                                <th width="150">Price</th>
                                <th width="150">Total</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($invoice) && $invoice->items->count())
                                @foreach($invoice->items as $index => $item)
                                <tr>
                                    <td><input type="text" name="items[{{ $index }}][description]" class="form-control item-desc" required value="{{ $item->description }}"></td>
                                    <td><input type="number" step="0.01" name="items[{{ $index }}][quantity]" class="form-control item-qty" required value="{{ $item->quantity }}"></td>
                                    <td><input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control item-price" required value="{{ $item->unit_price }}"></td>
                                    <td><input type="text" class="form-control item-total" readonly value="{{ $item->total }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fa fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="items[0][description]" class="form-control item-desc" required></td>
                                    <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control item-qty" required value="1"></td>
                                    <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price" required value="0"></td>
                                    <td><input type="text" class="form-control item-total" readonly value="0.00"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fa fa-times"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="btn btn-sm btn-secondary" id="add-item"><i class="fa fa-plus"></i> Add Line Item</button>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle">Subtotal</th>
                                <th colspan="2"><input type="text" class="form-control" id="subtotal" readonly value="{{ $invoice->subtotal ?? '0.00' }}"></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle">Grand Total</th>
                                <th colspan="2"><input type="text" class="form-control bg-light fw-bold" id="grand_total" readonly value="{{ $invoice->total_amount ?? '0.00' }}"></th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mb-3 mt-4">
                        <label class="form-label">Authorized Signatory Name</label>
                        <input type="text" name="authorized_signatory" class="form-control" value="{{ $invoice->authorized_signatory ?? '' }}" placeholder="Enter name for digital sign mode in print">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Terms</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $invoice->notes ?? '' }}</textarea>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save Invoice</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function toggleType() {
        let type = $('#type').val();
        if (type === 'customer') {
            $('#customer_div').show();
            $('#customer_id').prop('required', true);
            $('#supplier_div').hide();
            $('#supplier_id').prop('required', false);
        } else {
            $('#customer_div').hide();
            $('#customer_id').prop('required', false);
            $('#supplier_div').show();
            $('#supplier_id').prop('required', true);
        }
    }

    $('#type').on('change', toggleType);
    toggleType(); // initial run

    let itemIndex = {{ isset($invoice) ? $invoice->items->count() : 1 }};
    
    $('#add-item').on('click', function() {
        let html = `<tr>
            <td><input type="text" name="items[${itemIndex}][description]" class="form-control item-desc" required></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][quantity]" class="form-control item-qty" required value="1"></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control item-price" required value="0"></td>
            <td><input type="text" class="form-control item-total" readonly value="0.00"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fa fa-times"></i></button></td>
        </tr>`;
        $('#items-table tbody').append(html);
        itemIndex++;
    });

    $(document).on('click', '.remove-item', function() {
        if ($('#items-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotal();
        }
    });

    $(document).on('input', '.item-qty, .item-price', function() {
        let tr = $(this).closest('tr');
        let qty = parseFloat(tr.find('.item-qty').val()) || 0;
        let price = parseFloat(tr.find('.item-price').val()) || 0;
        let total = qty * price;
        tr.find('.item-total').val(total.toFixed(2));
        calculateTotal();
    });

    function calculateTotal() {
        let subtotal = 0;
        $('.item-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        $('#subtotal').val(subtotal.toFixed(2));
        $('#grand_total').val(subtotal.toFixed(2));
    }
</script>
@endpush
