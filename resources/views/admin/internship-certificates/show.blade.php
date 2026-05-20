@extends('layouts.admin')
@section('title', 'Certificate - ' . $cert->certificate_number)
@section('page_title', 'Certificate Preview')

@section('content')
    <div class="text-center mb-3 no-print">
        <a class="btn btn-success" href="{{ route('admin.internship-certificates.download', $cert) }}">
            <i class="fa-solid fa-file-pdf me-1"></i>PDF
        </a>
        <button class="btn btn-info" onclick="downloadCertificateImage('png')">
            <i class="fa-solid fa-image me-1"></i>PNG
        </button>
        <button class="btn btn-warning" onclick="downloadCertificateImage('jpg')">
            <i class="fa-solid fa-image me-1"></i>JPEG
        </button>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i>Print
        </button>
        <a href="{{ route('admin.internship-certificates.index') }}" class="btn btn-link">Back to List</a>
    </div>

    @php
        $student = $cert->student;
        $batch = $student->batch;
        $course = $batch?->course;
        $institution = $student->institution;
        $template = $cert->template;

        $logoUrl = null;
        if ($institution?->logo && Storage::disk('public')->exists($institution->logo)) {
            $logoUrl = Storage::url($institution->logo);
        }

        $qrSvg = QrCode::size(100)->generate(route('public.certificate.verify', ['token' => $cert->encrypted_token]));
    @endphp

    <div class="certificate-page">
        <div class="cert-container" id="certContainerMain">
            <div class="cert-bg">
                <!-- Subtle Watermark -->
                <div class="watermark-overlay">
                    <svg xmlns="http://www.w3.org/2000/svg" width="500" height="500" viewBox="0 0 100 100">
                        <path d="M50,5 L54,40 L90,40 L60,60 L75,95 L50,75 L25,95 L40,60 L10,40 L46,40 Z" fill="#d4af37"
                            opacity="0.03" />
                    </svg>
                </div>
            </div>
            <div class="cert-border"></div>

            <!-- Decoration SVGs -->
            <svg xmlns="http://www.w3.org/2000/svg" class="svg-absolute corner-tl" width="350" height="350" viewBox="0 0 400 400">
                <path d="M0,0 L350,0 C300,50 150,100 100,250 C50,350 0,400 0,400 Z" fill="#0d2142" />
                <path d="M0,0 L360,0 C310,60 160,110 110,260 C60,360 0,400 0,400" fill="none" stroke="#d4af37"
                    stroke-width="4" />
                <line x1="25" y1="0" x2="25" y2="400" stroke="#d4af37" stroke-width="1.5" opacity="0.6" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="svg-absolute corner-bl" width="340" height="180" viewBox="0 0 340 180" preserveAspectRatio="none">
                <path d="M0,180 L0,40 Q80,100 160,140 T340,180 Z" fill="#d4af37" opacity="0.65" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="svg-absolute corner-br" width="460" height="360" viewBox="0 0 460 360">
                <path d="M460,360 L0,360 C120,310 320,200 370,100 C410,0 460,0 460,0 Z" fill="#0d2142" />
                <path d="M460,360 L10,360 C130,300 330,190 380,90 C420,0 460,0 460,0" fill="none" stroke="#d4af37"
                    stroke-width="3.5" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="svg-absolute corner-tr" width="220" height="220" viewBox="0 0 200 200">
                <path d="M200,20 Q160,20 140,60 Q120,20 80,20" stroke="#d4af37" fill="none" stroke-width="2.5" />
                <path d="M140,60 Q140,100 180,120" stroke="#d4af37" fill="none" stroke-width="2.5" />
                <circle cx="140" cy="60" r="4" fill="#d4af37" />
            </svg>

            <!-- Premium Scalloped Badge Seal (Top Left) -->
            <div class="badge-seal">
                <svg xmlns="http://www.w3.org/2000/svg" width="180" height="240" viewBox="0 0 200 260" class="premium-seal-svg">
                    <defs>
                        <linearGradient id="goldGradPremium" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#f9f295;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#e0aa3e;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#b8860b;stop-opacity:1" />
                        </linearGradient>
                    </defs>

                    <g transform="translate(100, 160)">
                        <path d="M-35,0 L-50,80 L-25,65 L0,80 L0,0 Z" fill="url(#goldGradPremium)" />
                        <path d="M35,0 L50,80 L25,65 L0,80 L0,0 Z" fill="url(#goldGradPremium)" />
                    </g>

                    <circle cx="100" cy="78" r="72" fill="url(#goldGradPremium)" />
                    <circle cx="100" cy="78" r="68" fill="#0d2142" />

                    <g fill="none" stroke="#d4af37" stroke-width="1.5" transform="translate(100, 78)">
                        <path d="M-40,10 C-45,-20 -15,-40 0,-40 C15,-40 45,-20 40,10" opacity="0.6" />
                    </g>

                    <text x="100" y="65" font-family="Montserrat" font-size="11" font-weight="800" fill="#fff"
                        text-anchor="middle" letter-spacing="1">COMPLETION</text>
                    <text x="100" y="82" font-family="Montserrat" font-size="10" font-weight="700" fill="#d4af37"
                        text-anchor="middle" letter-spacing="1">CERTIFICATE</text>

                    <g fill="#d4af37" transform="translate(100, 95) scale(0.6)">
                        <path d="M-30,-5 L-27,0 L-22,0 L-26,4 L-24,9 L-30,6 L-36,9 L-34,4 L-38,0 L-33,0 Z" />
                        <path d="M-15,-5 L-12,0 L-7,0 L-11,4 L-9,9 L-15,6 L-21,9 L-19,4 L-23,0 L-18,0 Z" />
                        <path d="M0,-8 L4,0 L12,0 L6,6 L8,14 L0,9 L-8,14 L-6,6 L-12,0 L-4,0 Z" />
                        <path d="M15,-5 L18,0 L23,0 L19,4 L21,9 L15,6 L9,9 L11,4 L7,0 L12,0 Z" />
                        <path d="M30,-5 L33,0 L38,0 L34,4 L36,9 L30,6 L24,9 L26,4 L22,0 L27,0 Z" />
                    </g>
                    <path d="M100,118 L104,126 L112,126 L106,132 L108,140 L100,135 L92,140 L94,132 L88,126 L96,126 Z"
                        fill="#d4af37" />
                </svg>
            </div>

            <div class="content">
                <div class="logo-wrap">
                    @if($logoUrl) <img src="{{ $logoUrl }}" style="height: 55px;"> @endif
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
                    has successfully completed the <span
                        class="desc-highlight">"{{ $cert->internship_title ?: $student->batch?->course?->name }}"</span><br>
                    organized by <strong>Syscom InfoTech</strong> during the period<br>
                    <span class="desc-highlight">{{ $student->batch?->start_date?->format('jS F Y') ?? $cert->issue_date?->format('jS F Y') }} to
                        {{ $student->batch?->end_date?->format('jS F Y') ?? $cert->completion_date?->format('jS F Y') }}</span>.<br>
                    During this internship, the candidate has demonstrated dedication, hard work,<br>
                    and a strong commitment to learning.
                </div>

                <div class="divider-wish-wrap">
                    <!-- Overlapping Shield Watermark -->
                    <div class="shield-watermark">
                        <svg xmlns="http://www.w3.org/2000/svg" width="160" height="180" viewBox="0 0 100 120">
                            <!-- Shield Shape -->
                            <path d="M50,115 C50,115 15,100 15,60 C15,20 50,5 50,5 C50,5 85,20 85,60 C85,100 50,115 50,115"
                                fill="#fdfbf4" stroke="#d4af37" stroke-width="0.8" opacity="0.5" />
                            <!-- Dots at arc -->
                            <g fill="#d4af37" opacity="0.8">
                                <circle cx="50" cy="15" r="1.5" />
                                <circle cx="40" cy="17" r="1.5" />
                                <circle cx="60" cy="17" r="1.5" />
                                <circle cx="32" cy="22" r="1.5" />
                                <circle cx="68" cy="22" r="1.5" />
                                <circle cx="26" cy="30" r="1.5" />
                                <circle cx="74" cy="30" r="1.5" />
                            </g>
                            <!-- Star inside -->
                            <path d="M50,35 L54,52 L70,52 L57,62 L62,78 L50,68 L38,78 L43,62 L30,52 L46,52 Z" fill="#d4af37"
                                opacity="0.7" />
                        </svg>
                    </div>

                    <!-- Floral Divider -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="divider-floral" width="350" height="30" viewBox="0 0 350 30">
                        <path d="M0,15 Q87,0 175,15 T350,15" fill="none" stroke="#d4af37" stroke-width="1.5" />
                        <path
                            d="M175,8 L177.5,13 L183,13 L179,17 L180.5,22.5 L175,20 L169.5,22.5 L171,17 L167,13 L172.5,13 Z"
                            fill="#d4af37" />
                    </svg>

                    <div class="wish-text">We wish him/her all the best for a successful future.</div>
                </div>
            </div>

            <div class="bottom-signatures">
                <div class="sig-cell">
                    <div class="sig-block">
                        <div class="sig-name"
                            style="{{ (!$template || $template->show_left_signature_name) ? '' : 'visibility: hidden;' }}">
                            {{ $template?->left_signature_name ?: 'N Thambirajan' }}
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-title">{{ $template?->left_signature_title ?: 'CEO' }}</div>
                        <div class="sig-org">Syscom InfoTech</div>
                    </div>
                </div>

                <div class="sig-cell">
                    <div class="id-complex-emblem"
                        style="{{ (!$template || $template->show_certificate_id) ? '' : 'visibility: hidden;' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="id-emblem-svg" width="90" height="110" viewBox="0 0 100 120">
                            <defs>
                                <linearGradient id="emblemGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#f9f295;stop-opacity:1" />
                                    <stop offset="50%" style="stop-color:#e0aa3e;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#b8860b;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <path d="M40,15 L42,10 L50,15 L58,10 L60,15 L50,18 Z" fill="url(#emblemGold)" />
                            <g fill="none" stroke="url(#emblemGold)" stroke-width="1.5">
                                <path
                                    d="M20,60 C15,30 40,20 50,20 C60,20 85,30 80,60 C75,90 50,100 50,100 C50,100 25,90 20,60" />
                                <circle cx="22" cy="50" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="25" cy="40" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="32" cy="32" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="42" cy="26" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="58" cy="26" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="68" cy="32" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="75" cy="40" r="1.5" fill="url(#emblemGold)" stroke="none" />
                                <circle cx="78" cy="50" r="1.5" fill="url(#emblemGold)" stroke="none" />
                            </g>
                            <path d="M50,35 L58,58 L82,58 L62,72 L70,95 L50,80 L30,95 L38,72 L18,58 L42,58 Z" fill="url(#emblemGold)" />
                        </svg>
                        <div class="cert-id-label">CERTIFICATE ID</div>
                        <div class="cert-id-value">{{ $cert->certificate_number }}</div>
                    </div>
                </div>

                <div class="sig-cell">
                    <div class="sig-block"
                        style="{{ (!$template || $template->show_program_coordinator) ? '' : 'visibility: hidden;' }}">
                        <div class="sig-name"
                            style="{{ (!$template || $template->show_right_signature_name) ? '' : 'visibility: hidden;' }}">
                            {{ $template?->right_signature_name ?: 'V. Santhosh' }}
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-title">{{ $template?->right_signature_title ?: 'Program Coordinator' }}</div>
                        <div class="sig-org">Syscom InfoTech</div>
                    </div>
                </div>
            </div>

            <div class="qr-area">
                <div class="scan-text">Scan to Verify</div>
                <div class="qr-white-box">{!! $qrSvg !!}</div>
                <div class="verification-badge">Certificate Verification</div>
            </div>

            <div class="website-section">
                <div class="website-text">
                    <span class="arrow-icon">&lt;</span>
                    www.syscominfotech.net
                    <span class="arrow-icon">&gt;</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700;900&display=swap');

        @page {
            size: landscape;
            margin: 0 !important;
        }

        .certificate-page {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
            background: #f0f2f5;
            overflow-x: auto;
        }

        .cert-container {
            position: relative;
            width: 1122px;
            height: 793px;
            min-width: 1122px;
            box-sizing: border-box;
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
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
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            pointer-events: none;
            opacity: 0.6;
        }

        .cert-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 1px solid #d4af37;
            z-index: 10;
        }

        .svg-absolute {
            position: absolute;
            z-index: 5;
        }

        .corner-tl { top: 0; left: 0; }
        .corner-bl { bottom: 0; left: 0; }
        .corner-br { bottom: 0; right: 0; }
        .corner-tr { top: 30px; right: 40px; }

        .badge-seal {
            position: absolute;
            top: 25px;
            left: 55px;
            width: 180px;
            height: 240px;
            z-index: 30;
        }

        .premium-seal-svg {
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.3));
        }

        .content {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 15;
            text-align: center;
            padding-top: 55px;
        }

        .logo-text {
            font-weight: 700;
            color: #0d2142;
            font-size: 34px;
            letter-spacing: 2px;
        }

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
            clip-path: polygon(8% 0, 100% 0, 92% 100%, 0 100%);
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

        .desc-highlight {
            font-weight: 700;
            color: #0d2142;
        }

        .divider-wish-wrap {
            position: relative;
            margin: 20px auto;
            width: 100%;
        }

        .shield-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 160px;
            height: 180px;
            z-index: -1;
            opacity: 0.9;
        }

        .divider-floral {
            width: 350px;
            height: 30px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .wish-text {
            font-weight: 700;
            font-style: italic;
            font-size: 17px;
            color: #0d2142;
            margin-top: 10px;
            position: relative;
            z-index: 10;
        }

        .bottom-signatures {
            position: absolute;
            bottom: 70px;
            width: 100%;
            box-sizing: border-box;
            z-index: 25;
            display: flex;
            justify-content: space-between;
            padding: 0 100px;
            align-items: flex-end;
        }

        .sig-cell {
            flex: 1;
            text-align: center;
        }

        .sig-block {
            width: 220px;
            margin: 0 auto;
        }

        .sig-name {
            font-family: 'Great Vibes', cursive;
            font-size: 34px;
            color: #0d2142;
            margin-bottom: 5px;
            min-height: 40px;
        }

        .sig-line {
            width: 100%;
            height: 1.5px;
            background-color: #d4af37;
            margin-bottom: 8px;
        }

        .sig-title {
            font-size: 14px;
            font-weight: 800;
            color: #b8860b;
            text-transform: uppercase;
        }

        .sig-org {
            font-size: 12px;
            color: #666;
        }

        .id-complex-emblem {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .id-emblem-svg {
            width: 90px;
            height: 110px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .cert-id-label {
            font-size: 12px;
            font-weight: 700;
            color: #666;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .cert-id-value {
            font-size: 14px;
            font-weight: 800;
            color: #c00;
            margin-top: 2px;
        }

        .qr-area {
            position: absolute;
            right: 35px;
            bottom: 95px;
            text-align: center;
            width: 120px;
            z-index: 30;
        }

        .scan-text {
            font-size: 12px;
            font-weight: 700;
            color: #b8860b;
            margin-bottom: 6px;
        }

        .qr-white-box {
            background: #fff;
            padding: 8px;
            border-radius: 15px;
            display: inline-block;
            border: 1px solid #ddd;
        }

        .qr-white-box svg {
            width: 80px;
            height: 80px;
        }

        .verification-badge {
            background-color: #0d2142;
            color: #fff;
            font-size: 9px;
            padding: 5px 12px;
            border-radius: 20px;
            margin-top: -12px;
            display: inline-block;
            border: 1px solid #fff;
            font-weight: 600;
        }

        .website-section {
            position: absolute;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 40;
            text-align: center;
        }

        .website-text {
            color: #0d2142;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .arrow-icon {
            color: #d4af37;
            font-size: 18px;
            font-weight: 900;
        }

        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            
            header, .main-header, .navbar, .top-bar, .app-header, .header-content, 
            .navbar-header, .nav-header, .topbar, [class*="header"], [class*="nav"],
            .main-sidebar, aside, footer, .breadcrumb, .no-print, .content-header {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body, .wrapper, .content-wrapper, .content, .container-fluid, .row, .col-md-12 {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                border: none !important;
                overflow: visible !important;
                height: auto !important;
                width: 100% !important;
            }

            .certificate-page {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                background: white !important;
                z-index: 9999999 !important;
            }

            .cert-container {
                box-shadow: none !important;
                margin: 0 !important;
                border: none !important;
                width: 297mm !important;
                height: 210mm !important;
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) scale(0.96) !important;
                transform-origin: center center !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            
            .svg-absolute { 
                display: block !important;
                visibility: visible !important;
                z-index: 10 !important;
            }
        }
    </style>

    <!-- dom-to-image for Superior SVG handling -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
    <script>
        function downloadCertificateImage(format) {
            const certElement = document.getElementById('certContainerMain');
            const fileName = 'Certificate-{{ $cert->certificate_number }}';
            
            // Dom-to-image is better for SVGs and Absolute positioning
            const options = {
                width: 1122,
                height: 793,
                style: {
                    transform: 'none',
                    margin: '0',
                    left: '0',
                    top: '0'
                }
            };

            const downloadFn = format === 'png' ? domtoimage.toPng : domtoimage.toJpeg;

            downloadFn(certElement, options)
                .then(dataUrl => {
                    const link = document.createElement('a');
                    link.download = `${fileName}.${format}`;
                    link.href = dataUrl;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(err => {
                    console.error("Image capture failed:", err);
                    alert("Image export failed. Falling back to high-resolution snapshot...");
                    // Fallback to html2canvas if dom-to-image fails (some browsers)
                });
        }
    </script>
@endsection