<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate - {{ $cert->certificate_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700;900&display=swap');
        
        @page {
            margin: 0px;
            size: 297mm 210mm;
        }
        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'Montserrat', Helvetica, Arial, sans-serif;
            background-color: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cert-container {
            position: relative;
            width: 1122px; 
            height: 793px;
            box-sizing: border-box;
            background-color: #ffffff;
            overflow: hidden;
        }

        .cert-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fdfbf4 100%);
            z-index: 0;
        }
        
        .watermark-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 500px;
            height: 500px;
            margin-top: -250px;
            margin-left: -250px;
            opacity: 0.03;
        }

        .cert-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 1.5px solid #d4af37;
            z-index: 10;
        }

        .svg-absolute { position: absolute; z-index: 20; }
        .corner-tl { top: 0; left: 0; width: 350px; height: 350px; }
        .corner-bl { bottom: 0; left: 0; width: 340px; height: 180px; }
        .corner-br { bottom: 0; right: 0; width: 460px; height: 360px; }
        .corner-tr { top: 30px; right: 40px; width: 220px; height: 220px; }

        .badge-seal {
            position: absolute;
            top: 25px;
            left: 55px;
            width: 180px;
            height: 240px;
            z-index: 30;
        }

        .content {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 15;
            text-align: center;
            padding-top: 55px;
        }

        .logo-wrap { margin-bottom: 10px; display: block; text-align: center; }
        .logo-wrap img { height: 55px; vertical-align: middle; }
        .logo-text { font-weight: 700; color: #0d2142; font-size: 34px; letter-spacing: 2px; vertical-align: middle; margin-left: 10px; }

        .title-main {
            font-family: 'Playfair Display', serif;
            font-size: 78px;
            color: #0d2142;
            font-weight: 700;
            letter-spacing: 4px;
            margin: 5px 0;
            line-height: 1;
        }
        .title-sub {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            color: #b8860b;
            font-weight: 600;
            letter-spacing: 8px;
            border-top: 2.5px solid #d4af37;
            border-bottom: 2.5px solid #d4af37;
            padding: 5px 60px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .certify-text {
            font-size: 16px;
            color: #fff;
            background-color: #0d2142;
            display: inline-block;
            padding: 10px 70px;
            margin: 25px 0;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 92px;
            color: #0d2142;
            margin: 5px 0;
            line-height: 1;
        }

        .desc-text {
            font-size: 18px;
            color: #333;
            line-height: 1.6;
            max-width: 900px;
            margin: 15px auto;
        }
        .desc-highlight { font-weight: 700; color: #0d2142; }

        .divider-wish-wrap { position: relative; margin: 20px auto; width: 100%; }
        
        .shield-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 160px;
            height: 180px;
            margin-top: -90px;
            margin-left: -80px;
            z-index: -1;
            opacity: 0.9;
        }

        .divider-floral { width: 350px; height: 30px; margin: 0 auto; position: relative; z-index: 10; }

        .wish-text {
            font-weight: 700;
            font-style: italic;
            font-size: 17px;
            color: #0d2142;
            margin-top: 10px;
            position: relative;
            z-index: 10;
        }

        .bottom-row {
            position: absolute;
            bottom: 75px;
            width: 100%;
            padding: 0 80px;
            box-sizing: border-box;
            z-index: 25;
            display: table;
        }
        
        .row-cell { display: table-cell; vertical-align: bottom; width: 33.33%; text-align: center; }
        .sig-block { width: 220px; margin: 0 auto; }
        
        .sig-name { font-family: 'Great Vibes', cursive; font-size: 34px; color: #0d2142; margin-bottom: 5px; min-height: 40px; }
        .sig-line { width: 100%; height: 1.5px; background-color: #d4af37; margin-bottom: 8px; }
        .sig-title { font-size: 14px; font-weight: 800; color: #b8860b; text-transform: uppercase; }
        .sig-org { font-size: 11px; color: #666; }

        .id-emblem-wrap { text-align: center; margin-bottom: 10px; }
        .id-emblem-svg { width: 85px; height: 105px; }
        .cert-id-label { font-size: 11px; font-weight: 700; color: #666; letter-spacing: 2px; margin-top: 5px; }
        .cert-id-value { font-size: 13px; font-weight: 800; color: #c00; margin-top: 2px; }

        .qr-section {
            position: absolute;
            right: 35px;
            bottom: 95px;
            text-align: center;
            width: 120px;
            z-index: 30;
        }
        .scan-text { font-size: 12px; font-weight: 700; color: #b8860b; margin-bottom: 5px; }
        .qr-box {
            background: #fff;
            padding: 8px;
            border-radius: 12px;
            display: inline-block;
            border: 1px solid #d4af37;
        }
        .qr-box svg { width: 75px; height: 75px; }
        .verify-pill {
            background-color: #0d2142;
            color: #fff;
            font-size: 9px;
            padding: 5px 10px;
            border-radius: 20px;
            margin-top: -12px;
            display: inline-block;
            border: 1px solid #fff;
        }

        .website-section {
            position: absolute;
            bottom: 35px;
            left: 50%;
            width: 100%;
            text-align: center;
            z-index: 40;
        }
        .website-text {
            color: #0d2142;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    @php
        $student = $cert->student;
        $batch = $student->batch;
        $course = $batch?->course;
        $institution = $student->institution;
        $template = $cert->template;

        $logoBase64 = null;
        if ($institution?->logo && Storage::disk('public')->exists($institution->logo)) {
            $logoBase64 = 'data:image/' . pathinfo($institution->logo, PATHINFO_EXTENSION) . ';base64,' . base64_encode(Storage::disk('public')->get($institution->logo));
        }
    @endphp

    <div class="cert-container">
        <div class="cert-bg">
            <div class="watermark-overlay">
                <svg xmlns="http://www.w3.org/2000/svg" width="500" height="500" viewBox="0 0 100 100">
                    <path d="M50,5 L54,40 L90,40 L60,60 L75,95 L50,75 L25,95 L40,60 L10,40 L46,40 Z" fill="#d4af37" fill-opacity="0.03" />
                </svg>
            </div>
        </div>
        <div class="cert-border"></div>
        
        <!-- Decoration SVGs - Simplified for PDF Engine -->
        <div class="svg-absolute corner-tl">
            <svg xmlns="http://www.w3.org/2000/svg" width="350" height="350" viewBox="0 0 400 400">
                <path d="M0,0 L350,0 C300,50 150,100 100,250 C50,350 0,400 0,400 Z" fill="#0d2142" />
                <path d="M0,0 L360,0 C310,60 160,110 110,260 C60,360 0,400 0,400" fill="none" stroke="#d4af37" stroke-width="4" />
            </svg>
        </div>
        <div class="svg-absolute corner-bl">
            <svg xmlns="http://www.w3.org/2000/svg" width="340" height="180" viewBox="0 0 340 180">
                <path d="M0,180 L0,40 Q80,100 160,140 T340,180 Z" fill="#d4af37" fill-opacity="0.65" />
            </svg>
        </div>
        <div class="svg-absolute corner-br">
            <svg xmlns="http://www.w3.org/2000/svg" width="460" height="360" viewBox="0 0 460 360">
                <path d="M460,360 L0,360 C120,310 320,200 370,100 C410,0 460,0 460,0 Z" fill="#0d2142" />
                <path d="M460,360 L10,360 C130,300 330,190 380,90 C420,0 460,0 460,0" fill="none" stroke="#d4af37" stroke-width="3.5" />
            </svg>
        </div>
        <div class="svg-absolute corner-tr">
            <svg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 200 200">
                <path d="M200,20 Q160,20 140,60 Q120,20 80,20" stroke="#d4af37" fill="none" stroke-width="2.5" />
                <path d="M140,60 Q140,100 180,120" stroke="#d4af37" fill="none" stroke-width="2.5" />
                <circle cx="140" cy="60" r="4" fill="#d4af37" />
            </svg>
        </div>

        <div class="badge-seal">
            <svg xmlns="http://www.w3.org/2000/svg" width="180" height="240" viewBox="0 0 200 260">
                <g transform="translate(100, 160)">
                    <path d="M-35,0 L-50,80 L-25,65 L0,80 L0,0 Z" fill="#d4af37" />
                    <path d="M35,0 L50,80 L25,65 L0,80 L0,0 Z" fill="#d4af37" />
                </g>
                <circle cx="100" cy="78" r="72" fill="#d4af37" />
                <circle cx="100" cy="78" r="68" fill="#0d2142" />
                <text x="100" y="65" font-family="Montserrat" font-size="11" font-weight="800" fill="#fff" text-anchor="middle">COMPLETION</text>
                <text x="100" y="82" font-family="Montserrat" font-size="10" font-weight="700" fill="#d4af37" text-anchor="middle">CERTIFICATE</text>
                <path d="M100,118 L104,126 L112,126 L106,132 L108,140 L100,135 L92,140 L94,132 L88,126 L96,126 Z" fill="#d4af37" />
            </svg>
        </div>

        <div class="content">
            <div class="logo-wrap">
                @if($logoBase64) <img src="{{ $logoBase64 }}"> @endif
                <span class="logo-text">SYSCOM INFOTECH</span>
            </div>
            <h1 class="title-main">CERTIFICATE</h1>
            <div class="title-sub">OF INTERNSHIP</div>
            <div class="certify-text"><span>THIS IS TO CERTIFY THAT</span></div>
            <div class="student-name">{{ $student->full_name }}</div>
            <div class="desc-text">
                @if(($template?->show_department ?? true) && $student->department)
                    of <span class="desc-highlight">{{ $student->department }}</span>
                @endif
                has successfully completed the <span class="desc-highlight">"{{ $cert->internship_title ?: $course?->name }}"</span><br>
                organized by <strong>Syscom InfoTech</strong> during the period<br>
                <span class="desc-highlight">{{ $student->batch?->start_date?->format('jS F Y') ?? $cert->issue_date?->format('jS F Y') }} to {{ $student->batch?->end_date?->format('jS F Y') ?? $cert->completion_date?->format('jS F Y') }}</span>.<br>
                During this internship, the candidate has demonstrated dedication, hard work,<br>
                and a strong commitment to learning.
            </div>

            <div class="divider-wish-wrap">
                <div class="shield-watermark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="160" height="180" viewBox="0 0 100 120">
                        <path d="M50,115 C50,115 15,100 15,60 C15,20 50,5 50,5 C50,5 85,20 85,60 C85,100 50,115 50,115" fill="#fdfbf4" stroke="#d4af37" stroke-width="0.8" />
                        <path d="M50,35 L58,58 L82,58 L62,72 L70,95 L50,80 L30,95 L38,72 L18,58 L42,58 Z" fill="#d4af37" fill-opacity="0.3" />
                    </svg>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="divider-floral" width="350" height="30" viewBox="0 0 350 30">
                    <path d="M0,15 Q87,0 175,15 T350,15" fill="none" stroke="#d4af37" stroke-width="1.5" />
                </svg>
                <div class="wish-text">We wish him/her all the best for a successful future.</div>
            </div>
        </div>

        <div class="bottom-row">
            <div class="row-cell">
                <div class="sig-block">
                    <div class="sig-name" style="{{ (!$template || $template->show_left_signature_name) ? '' : 'color: white;' }}">
                        {{ $template?->left_signature_name ?: 'N Thambirajan' }}
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-title">{{ $template?->left_signature_title ?: 'CEO' }}</div>
                    <div class="sig-org">Syscom InfoTech</div>
                </div>
            </div>
            <div class="row-cell">
                <div class="id-emblem-wrap" style="{{ (!$template || $template->show_certificate_id) ? '' : 'visibility: hidden;' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="id-emblem-svg" width="85" height="105" viewBox="0 0 100 120">
                        <path d="M40,15 L42,10 L50,15 L58,10 L60,15 L50,18 Z" fill="#d4af37" />
                        <path d="M50,35 L58,58 L82,58 L62,72 L70,95 L50,80 L30,95 L38,72 L18,58 L42,58 Z" fill="#d4af37" />
                    </svg>
                    <div class="cert-id-label">CERTIFICATE ID</div>
                    <div class="cert-id-value">{{ $cert->certificate_number }}</div>
                </div>
            </div>
            <div class="row-cell">
                <div class="sig-block" style="{{ (!$template || $template->show_program_coordinator) ? '' : 'color: white;' }}">
                    <div class="sig-name" style="{{ (!$template || $template->show_right_signature_name) ? '' : 'color: white;' }}">
                        {{ $template?->right_signature_name ?: 'V. Santhosh' }}
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-title">{{ $template?->right_signature_title ?: 'Program Coordinator' }}</div>
                    <div class="sig-org">Syscom InfoTech</div>
                </div>
            </div>
        </div>

        <div class="qr-section">
            <div class="scan-text">Scan to Verify</div>
            <div class="qr-box">{!! $qrSvg !!}</div>
            <div class="verify-pill">Certificate Verification</div>
        </div>

        <div class="website-section">
            <div class="website-text">www.syscominfotech.net</div>
        </div>
    </div>
</body>
</html>
