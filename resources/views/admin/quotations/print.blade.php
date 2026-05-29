<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; background: #fff; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .header .title { font-size: 32px; font-weight: bold; color: #2c3e50; margin: 0; }
        .header .company { text-align: right; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .info-row .billed-to, .info-row .details { width: 48%; }
        .details table { width: 100%; }
        .details table td { text-align: right; padding: 3px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th, table.items td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table.items th { background: #f8f9fa; font-weight: bold; color: #495057; }
        table.items td.num, table.items th.num { text-align: right; }
        .totals { display: flex; justify-content: flex-end; }
        .totals table { width: 300px; }
        .totals table td { padding: 8px; text-align: right; }
        .totals table tr.total td { font-size: 18px; font-weight: bold; border-top: 2px solid #333; }
        .footer { margin-top: 40px; font-size: 12px; color: #777; }
        .sign { margin-top: 50px; text-align: right; }
        .sign div { border-top: 1px solid #333; display: inline-block; padding-top: 5px; width: 200px; text-align: center; }
        @media print { .invoice-box { border: 0; box-shadow: none; padding: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <div>
                <h1 class="title">QUOTATION</h1>
                <p>Status: {{ ucfirst($quotation->status) }}</p>
            </div>
            <div class="company">
                <h2 style="margin:0;">{{ config('app.name') }}</h2>
                <p style="margin:5px 0 0 0; color:#555;">{{ $quotation->institution->name ?? '' }}</p>
            </div>
        </div>

        <div class="info-row">
            <div class="billed-to">
                <strong>Quotation To:</strong><br>
                {{ $quotation->customer->name }}<br>
                {{ $quotation->customer->address ?? '' }}<br>
                {{ $quotation->customer->email ? 'Email: ' . $quotation->customer->email . '<br>' : '' }}
                {{ $quotation->customer->phone ? 'Phone: ' . $quotation->customer->phone : '' }}
            </div>
            <div class="details">
                <table>
                    <tr><td><strong>Quotation No:</strong></td><td>{{ $quotation->quotation_number }}</td></tr>
                    <tr><td><strong>Date:</strong></td><td>{{ $quotation->quotation_date->format('d M, Y') }}</td></tr>
                    @if($quotation->expiry_date)
                    <tr><td><strong>Valid Until:</strong></td><td style="color:red;">{{ $quotation->expiry_date->format('d M, Y') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Description</th>
                    <th class="num" width="10%">Qty</th>
                    <th class="num" width="20%">Unit Price</th>
                    <th class="num" width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr><td>Subtotal:</td><td>₹{{ number_format($quotation->subtotal, 2) }}</td></tr>
                @if($quotation->discount > 0)
                <tr><td style="color:green;">Discount:</td><td style="color:green;">- ₹{{ number_format($quotation->discount, 2) }}</td></tr>
                @endif
                @if($quotation->tax > 0)
                <tr><td style="color:red;">Tax:</td><td style="color:red;">+ ₹{{ number_format($quotation->tax, 2) }}</td></tr>
                @endif
                <tr class="total"><td>Grand Total:</td><td>₹{{ number_format($quotation->total_amount, 2) }}</td></tr>
            </table>
        </div>

        @if($quotation->authorized_signatory)
        <div class="sign">
            <div>
                <strong>{{ $quotation->authorized_signatory }}</strong><br>
                Authorized Signatory
            </div>
        </div>
        @endif

        <div class="footer">
            @if($quotation->notes)
                <strong>Notes:</strong><br>
                {!! nl2br(e($quotation->notes)) !!}<br><br>
            @endif
            @if($quotation->terms_conditions)
                <strong>Terms & Conditions:</strong><br>
                {!! nl2br(e($quotation->terms_conditions)) !!}
            @endif
        </div>
    </div>
</body>
</html>
