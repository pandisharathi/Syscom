<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Quotation from {{ config('app.name') }}</h2>
    </div>

    <p>Dear {{ $quotation->customer->name }},</p>

    <p>Please find attached the quotation <strong>{{ $quotation->quotation_number }}</strong> generated on {{ $quotation->quotation_date->format('d M, Y') }}.</p>

    <div style="background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Total Amount:</strong> ₹{{ number_format($quotation->total_amount, 2) }}</p>
        <p style="margin: 5px 0 0 0;"><strong>Valid Until:</strong> {{ $quotation->expiry_date ? $quotation->expiry_date->format('d M, Y') : 'N/A' }}</p>
    </div>

    <p>If you have any questions or concerns regarding this quotation, please do not hesitate to contact us.</p>

    <p>Thank you for doing business with us!</p>

    <p style="margin-top: 30px; font-size: 0.9em; color: #6c757d;">
        Best regards,<br>
        <strong>{{ config('app.name') }}</strong>
    </p>
</body>
</html>
