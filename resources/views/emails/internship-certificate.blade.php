<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #0d2142; color: #fff; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Internship Completion</h2>
        </div>
        <div class="content">
            <p>Dear {{ $cert->student->full_name }},</p>
            <p>Congratulations! We are pleased to inform you that your internship completion certificate has been generated.</p>
            <p><strong>Certificate Details:</strong></p>
            <ul>
                <li>Certificate No: {{ $cert->certificate_number }}</li>
                <li>Course: {{ $cert->student->batch?->course?->name }}</li>
                <li>Issue Date: {{ $cert->issue_date?->format('d M Y') }}</li>
            </ul>
            <p>Please find the attached PDF copy of your certificate.</p>
            <p>Best Regards,<br>{{ $cert->student->institution->name }}</p>
        </div>
        <div class="footer">
            This is an automated email. Please do not reply.
        </div>
    </div>
</body>
</html>
