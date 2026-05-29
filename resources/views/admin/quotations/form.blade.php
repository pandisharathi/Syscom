@extends('layouts.admin')
@section('title', isset($quotation) ? 'Edit Quotation' : 'Create Quotation')
@section('page_title', isset($quotation) ? 'Edit Quotation' : 'Create Quotation')

@section('content')
<form action="{{ isset($quotation) ? route('admin.quotations.update', $quotation->id) : route('admin.quotations.store') }}" method="POST">
    @csrf
    @if(isset($quotation)) @method('PUT') @endif
    
    <div class="row">
        <!-- Details -->
        <div class="col-md-4 mb-4">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h6 class="mb-3 text-primary">Quotation Details</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (isset($quotation) && $quotation->customer_id == $customer->id) ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" name="quotation_date" class="form-control" required value="{{ isset($quotation) ? $quotation->quotation_date->format('Y-m-d') : date('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ isset($quotation) && $quotation->expiry_date ? $quotation->expiry_date->format('Y-m-d') : date('Y-m-d', strtotime('+30 days')) }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea name="terms_conditions" class="form-control" rows="3">{{ $quotation->terms_conditions ?? '1. Quotation valid for 30 days.' }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $quotation->notes ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3 mt-4">
                        <label class="form-label">Authorized Signatory</label>
                        <input type="text" name="authorized_signatory" class="form-control" value="{{ $quotation->authorized_signatory ?? '' }}" placeholder="Name for signature">
                    </div>

                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="col-md-8 mb-4">
            <div class="card card-soft">
                <div class="card-body">
                    <h6 class="mb-3 text-primary">Products / Services</h6>
                    <table class="table table-sm" id="items-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Description</th>
                                <th width="100">Qty</th>
                                <th width="120">Price</th>
                                <th width="150">Total</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($quotation) && $quotation->items->count())
                                @foreach($quotation->items as $index => $item)
                                <tr>
                                    <td><input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm item-desc" required value="{{ $item->description }}"></td>
                                    <td><input type="number" step="0.01" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty" required value="{{ $item->quantity }}"></td>
                                    <td><input type="number" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm item-price" required value="{{ $item->unit_price }}"></td>
                                    <td><input type="text" class="form-control form-control-sm item-total" readonly value="{{ $item->total }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fa fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" required placeholder="Product Name"></td>
                                    <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty" required value="1"></td>
                                    <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm item-price" required value="0"></td>
                                    <td><input type="text" class="form-control form-control-sm item-total" readonly value="0.00"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fa fa-times"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="btn btn-sm btn-secondary" id="add-item"><i class="fa fa-plus"></i> Add Product</button>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle">Subtotal</th>
                                <th colspan="2"><input type="text" class="form-control form-control-sm" id="subtotal" readonly value="{{ $quotation->subtotal ?? '0.00' }}"></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle text-success">Discount (₹)</th>
                                <th colspan="2"><input type="number" step="0.01" name="discount" class="form-control form-control-sm text-success" id="discount" value="{{ $quotation->discount ?? '0.00' }}"></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle text-danger">Tax (₹)</th>
                                <th colspan="2"><input type="number" step="0.01" name="tax" class="form-control form-control-sm text-danger" id="tax" value="{{ $quotation->tax ?? '0.00' }}"></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end align-middle">Grand Total</th>
                                <th colspan="2"><input type="text" class="form-control form-control-sm bg-light fw-bold" id="grand_total" readonly value="{{ $quotation->total_amount ?? '0.00' }}"></th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save Draft Quotation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let itemIndex = {{ isset($quotation) ? $quotation->items->count() : 1 }};
    
    $('#add-item').on('click', function() {
        let html = `<tr>
            <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm item-desc" required placeholder="Product Name"></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" required value="1"></td>
            <td><input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm item-price" required value="0"></td>
            <td><input type="text" class="form-control form-control-sm item-total" readonly value="0.00"></td>
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

    $(document).on('input', '.item-qty, .item-price, #discount, #tax', function() {
        if($(this).hasClass('item-qty') || $(this).hasClass('item-price')){
            let tr = $(this).closest('tr');
            let qty = parseFloat(tr.find('.item-qty').val()) || 0;
            let price = parseFloat(tr.find('.item-price').val()) || 0;
            let total = qty * price;
            tr.find('.item-total').val(total.toFixed(2));
        }
        calculateTotal();
    });

    function calculateTotal() {
        let subtotal = 0;
        $('.item-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        $('#subtotal').val(subtotal.toFixed(2));

        let discount = parseFloat($('#discount').val()) || 0;
        let tax = parseFloat($('#tax').val()) || 0;
        
        let grandTotal = subtotal - discount + tax;
        $('#grand_total').val(grandTotal.toFixed(2));
    }
</script>
@endpush
