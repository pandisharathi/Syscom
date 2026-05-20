<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate — {{ $internship_student->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        body { background: #f0f2f5; font-family: 'Georgia', serif; }
        .certificate-wrap {
            max-width: 900px; margin: 40px auto;
            background: #fff;
            border: 12px double #1e40af;
            padding: 50px 60px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
        }
        .certificate-wrap::before {
            content: '';
            position: absolute; inset: 10px;
            border: 2px solid #dbeafe;
            pointer-events: none;
        }
        .cert-title { font-size: 2.5rem; font-weight: 700; color: #1e40af; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; }
        .cert-subtitle { font-size: 1rem; color: #6b7280; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 30px; }
        .cert-student-name { font-size: 2.2rem; font-weight: 700; color: #1f2937; border-bottom: 2px solid #dbeafe; display: inline-block; padding-bottom: 6px; margin-bottom: 20px; }
        .cert-body { font-size: 1.05rem; color: #374151; line-height: 1.9; margin-bottom: 30px; }
        .cert-footer { display: flex; justify-content: space-between; margin-top: 50px; }
        .cert-footer div { text-align: center; }
        .cert-footer .line { width: 220px; border-top: 1px solid #9ca3af; margin-bottom: 6px; }
        .cert-footer .label { font-size: .85rem; color: #6b7280; }
        .cert-badge { font-size: 4rem; color: #f59e0b; position: absolute; top: 30px; right: 40px; }
        @media print {
            body { background: #fff; }
            .certificate-wrap { box-shadow: none; border-color: #000; margin: 10px auto; }
            .certificate-wrap::before { border-color: #ccc; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="text-center no-print mb-3 pt-3">
        <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
        <a href="{{ route('admin.internship-payments.students') }}" class="btn btn-link">Back</a>
    </div>

    <div class="certificate-wrap">
        <i class="fa-solid fa-award cert-badge"></i>
        <div class="text-center">
            <div class="cert-title">Certificate of Completion</div>
            <div class="cert-subtitle">{{ $internship_student->institution?->name ?? 'Training Institution' }}</div>
        </div>

        <div class="text-center cert-body">
            This is to certify that<br>
            <div class="cert-student-name">{{ $internship_student->full_name }}</div>
            has successfully completed the
            <strong>{{ $internship_student->batch?->course?->name ?? 'Internship' }}</strong>
            program conducted from
            <strong>{{ $internship_student->batch?->course?->start_date?->format('M d, Y') ?? '—' }}</strong>
            to
            <strong>{{ $internship_student->batch?->course?->end_date?->format('M d, Y') ?? '—' }}</strong>
            @if($internship_student->batch)
                <br>under <strong>{{ $internship_student->batch?->name }}</strong> batch.
            @endif
        </div>

        <div class="cert-footer">
            <div>
                <div class="line mx-auto"></div>
                <div class="label">Authorized Signature</div>
            </div>
            <div>
                <div class="line mx-auto"></div>
                <div class="label">Date</div>
            </div>
        </div>
    </div>
</body>
</html>