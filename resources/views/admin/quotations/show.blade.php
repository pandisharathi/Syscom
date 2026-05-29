@extends('layouts.admin')
@section('title', 'Quotation Details - ' . $quotation->quotation_number)
@section('page_title', 'Quotation Details')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-end">
        @if($quotation->status === 'draft')
            <form action="{{ route('admin.quotations.status', $quotation->id) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="final">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Mark as Final</button>
            </form>
            <a href="{{ route('admin.quotations.edit', $quotation->id) }}" class="btn btn-outline-primary"><i class="fa fa-edit"></i> Edit</a>
        @endif

        @if($quotation->status === 'final')
            <form action="{{ route('admin.quotations.status', $quotation->id) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-success"><i class="fa fa-thumbs-up"></i> Approve</button>
            </form>
            <form action="{{ route('admin.quotations.status', $quotation->id) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="btn btn-danger"><i class="fa fa-thumbs-down"></i> Reject</button>
            </form>
        @endif

        @if($quotation->status === 'approved')
            <form action="{{ route('admin.quotations.convert', $quotation->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Convert this to an invoice?');"><i class="fa fa-file-invoice-dollar"></i> Convert to Invoice</button>
            </form>
        @endif

        <form action="{{ route('admin.quotations.email', $quotation->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info text-white"><i class="fa fa-envelope"></i> Email to Customer</button>
        </form>
        
        <form action="{{ route('admin.quotations.duplicate', $quotation->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary"><i class="fa fa-copy"></i> Duplicate</button>
        </form>

        <a href="{{ route('admin.quotations.print', $quotation->id) }}" target="_blank" class="btn btn-outline-dark"><i class="fa fa-print"></i> Print</a>
        <a href="{{ route('admin.quotations.pdf', $quotation->id) }}" class="btn btn-outline-danger"><i class="fa fa-file-pdf"></i> PDF</a>
    </div>
</div>

<div class="card card-soft">
    <div class="card-body p-5">
        <div class="row mb-5">
            <div class="col-sm-6">
                <h3 class="fw-bold mb-3">QUOTATION</h3>
                @php
                    $statusClass = [
                        'draft' => 'bg-secondary',
                        'final' => 'bg-primary',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'invoiced' => 'bg-info'
                    ][$quotation->status] ?? 'bg-secondary';
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ ucfirst($quotation->status) }}</span>
            </div>
            <div class="col-sm-6 text-sm-end">
                @if($quotation->institution)
                    <h4 class="mb-1">{{ $quotation->institution->name }}</h4>
                    @if($quotation->institution->address)
                        <div class="text-muted small mb-1">{!! nl2br(e($quotation->institution->address)) !!}</div>
                    @endif
                    <div class="text-muted small">
                        @if($quotation->institution->email)
                            Email: {{ $quotation->institution->email }}<br>
                        @endif
                        @if($quotation->institution->phone)
                            Phone: {{ $quotation->institution->phone }}
                        @endif
                    </div>
                @else
                    <h4 class="mb-0">{{ config('app.name') }}</h4>
                @endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="fw-bold mb-2">Quotation To:</h6>
                <div><strong>{{ $quotation->customer->name }}</strong></div>
                @if($quotation->customer->address)
                    <div>{{ $quotation->customer->address }}</div>
                @endif
                @if($quotation->customer->email)
                    <div>Email: {{ $quotation->customer->email }}</div>
                @endif
                @if($quotation->customer->phone)
                    <div>Phone: {{ $quotation->customer->phone }}</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="mb-1"><strong>Quotation No:</strong> {{ $quotation->quotation_number }}</div>
                <div class="mb-1"><strong>Date:</strong> {{ $quotation->quotation_date->format('d M, Y') }}</div>
                @if($quotation->expiry_date)
                    <div class="mb-1 text-danger"><strong>Valid Until:</strong> {{ $quotation->expiry_date->format('d M, Y') }}</div>
                @endif
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th width="5%">#</th>
                        <th>Description</th>
                        <th width="10%" class="text-end">Qty</th>
                        <th width="15%" class="text-end">Unit Price</th>
                        <th width="15%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-sm-7">
                @if($quotation->notes)
                    <h6 class="fw-bold mb-1">Notes:</h6>
                    <p class="text-muted small mb-3">{!! nl2br(e($quotation->notes)) !!}</p>
                @endif
                @if($quotation->terms_conditions)
                    <h6 class="fw-bold mb-1">Terms & Conditions:</h6>
                    <p class="text-muted small">{!! nl2br(e($quotation->terms_conditions)) !!}</p>
                @endif
            </div>
            <div class="col-sm-5 text-end">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th class="text-end">Subtotal:</th>
                        <td class="text-end w-25">₹{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                    <tr>
                        <th class="text-end text-success">Discount:</th>
                        <td class="text-end w-25 text-success">- ₹{{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quotation->tax > 0)
                    <tr>
                        <th class="text-end text-danger">Tax:</th>
                        <td class="text-end w-25 text-danger">+ ₹{{ number_format($quotation->tax, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <th class="text-end fs-5">Grand Total:</th>
                        <td class="text-end w-25 fs-5 fw-bold">₹{{ number_format($quotation->total_amount, 2) }}</td>
                    </tr>
                </table>
                @if($quotation->authorized_signatory)
                    <div class="mt-5 text-center float-end" style="width: 200px;">
                        <div class="border-bottom border-dark mb-2"></div>
                        <div class="fw-bold">{{ $quotation->authorized_signatory }}</div>
                        <div class="small text-muted">Authorized Signatory</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
