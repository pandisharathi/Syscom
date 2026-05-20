<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Certificate - {{ $cert->certificate_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700;900&display=swap');
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Montserrat', sans-serif;
        }

        .verify-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 1122px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .status-banner {
            background: #0d2142;
            color: #d4af37;
            padding: 15px;
            text-align: center;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            z-index: 100;
            position: relative;
        }

        .cert-container {
            position: relative;
            width: 100%;
            aspect-ratio: 1122 / 793;
            background-color: #ffffff;
            overflow: hidden;
        }

        .cert-bg { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fdfbf4 100%); 
            z-index: 0;
            display: flex; align-items: center; justify-content: center;
        }
        
        .watermark-overlay { width: 45%; height: 60%; opacity: 0.03; pointer-events: none; }

        .cert-border { position: absolute; top: 2.5%; left: 2.5%; right: 2.5%; bottom: 2.5%; border: 1px solid #d4af37; z-index: 10; }
        
        .svg-absolute { position: absolute; z-index: 1; }
        .corner-tl { top: 0; left: 0; width: 30%; height: 40%; }
        .corner-bl { bottom: 0; left: 0; width: 30%; height: 22%; z-index: 5; }
        .corner-br { bottom: 0; right: 0; width: 40%; height: 45%; z-index: 5; }
        .corner-tr { top: 4%; right: 4%; width: 20%; height: 20%; z-index: 5; }

        /* Premium Scalloped Badge Seal (Top Left) */
        .badge-seal {
            position: absolute;
            top: 2.5%;
            left: 5%;
            width: 16%;
            aspect-ratio: 200/260;
            z-index: 30;
        }
        
        .premium-seal-svg { width: 100%; height: 100%; filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3)); }

        .content { position: relative; width: 100%; height: 100%; z-index: 15; text-align: center; padding-top: 5%; }
        .logo-text { font-weight: 700; color: #0d2142; font-size: 2.8vw; letter-spacing: 2px; }

        .title-main { font-family: 'Playfair Display', serif; font-size: 6.5vw; color: #0d2142; font-weight: 700; letter-spacing: 4px; margin: 0; line-height: 1; }
        .title-sub { font-family: 'Montserrat', sans-serif; font-size: 2.2vw; color: #b8860b; font-weight: 600; letter-spacing: 0.6vw; border-top: 2px solid #d4af37; border-bottom: 2px solid #d4af37; padding: 0.4% 5%; display: inline-block; margin-top: 0.5%; }

        .certify-text { font-size: 1.4vw; color: #fff; background-color: #0d2142; display: inline-block; padding: 0.8% 6%; margin: 2% 0; font-weight: 600; letter-spacing: 2px; clip-path: polygon(8% 0, 100% 0, 92% 100%, 0 100%); }

        .student-name { font-family: 'Great Vibes', cursive; font-size: 7.5vw; color: #0d2142; margin: 0.5% 0; line-height: 1; }
        .desc-text { font-size: 1.5vw; color: #333; line-height: 1.6; padding: 0 10%; margin-top: 1%; }
        .desc-highlight { font-weight: 700; color: #0d2142; }

        .divider-wish-wrap { position: relative; margin: 2% auto; width: 100%; }
        
        .shield-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12vw;
            height: 14vw;
            z-index: -1;
            opacity: 0.9;
        }

        .divider-floral { width: 30%; height: auto; margin: 0 auto; position: relative; z-index: 10; }
        .wish-text { font-weight: 700; font-style: italic; font-size: 1.4vw; color: #0d2142; margin-top: 0.5%; position: relative; z-index: 10; }

        .bottom-signatures { position: absolute; bottom: 10%; width: 100%; padding: 0 10%; box-sizing: border-box; z-index: 25; display: flex; justify-content: space-between; align-items: flex-end; }
        .sig-cell { flex: 1; text-align: center; }
        .sig-block { width: 80%; margin: 0 auto; }
        .sig-name { font-family: 'Great Vibes', cursive; font-size: 3vw; color: #0d2142; margin-bottom: 0.2vw; }
        .sig-line { width: 100%; height: 1.5px; background-color: #d4af37; margin-bottom: 0.5vw; }
        .sig-title { font-size: 1.2vw; font-weight: 800; color: #b8860b; text-transform: uppercase; }
        .sig-org { font-size: 1vw; color: #666; }

        /* Emblem Complex Styling */
        .id-emblem-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 1vw; }
        .id-emblem-svg { width: 8vw; height: 10vw; filter: drop-shadow(0 0.4vw 0.8vw rgba(0,0,0,0.2)); }
        .cert-id-label { font-size: 1vw; font-weight: 700; color: #666; letter-spacing: 0.1vw; margin-top: 0.5vw; }
        .cert-id-value { font-size: 1.2vw; font-weight: 800; color: #c00; margin-top: 0.2vw; }

        .qr-area { position: absolute; right: 4%; bottom: 12%; text-align: center; width: 12%; z-index: 30; }
        .scan-text-mini { font-size: 1vw; font-weight: 700; color: #b8860b; margin-bottom: 0.5vw; }
        .qr-white-box { background: #fff; padding: 5%; border-radius: 12px; display: inline-block; border: 1px solid #ddd; }
        .qr-white-box svg { width: 100%; height: auto; }
        .verification-badge { background-color: #0d2142; color: #fff; font-size: 0.8vw; padding: 4% 10%; border-radius: 20px; margin-top: -10%; display: inline-block; border: 1px solid #fff; font-weight: 600; white-space: nowrap; }

        .website-section { position: absolute; bottom: 4.5%; left: 50%; transform: translateX(-50%); z-index: 40; text-align: center; width: 30%; }
        .website-text { color: #0d2142; font-size: 1.3vw; font-weight: 700; letter-spacing: 1.5px; display: flex; align-items: center; justify-content: center; gap: 1vw; }
        .arrow-icon { color: #d4af37; font-size: 1.6vw; font-weight: 900; }

        .details-footer { background: #f8f9fa; padding: 30px; border-top: 1px solid #eee; }
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center; }
        .detail-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .detail-value { font-weight: 700; color: #0d2142; font-size: 16px; }
    </style>
</head>
<body>
    @php $template = $cert->template; @endphp
    <div class="verify-card">
        <div class="status-banner">
            <i class="fa-solid fa-circle-check me-2"></i> Certificate Authenticity Verified
        </div>

        <div class="cert-container">
            <div class="cert-bg">
                <div class="watermark-overlay">
                    <svg viewBox="0 0 100 100">
                        <path d="M50,5 L54,40 L90,40 L60,60 L75,95 L50,75 L25,95 L40,60 L10,40 L46,40 Z" fill="#d4af37" />
                    </svg>
                </div>
            </div>
            <div class="cert-border"></div>
            
            <svg class="svg-absolute corner-tl" viewBox="0 0 400 400"><path d="M0,0 L350,0 C300,50 150,100 100,250 C50,350 0,400 0,400 Z" fill="#0d2142" /><path d="M0,0 L360,0 C310,60 160,110 110,260 C60,360 0,400 0,400" fill="none" stroke="#d4af37" stroke-width="4" /><line x1="25" y1="0" x2="25" y2="400" stroke="#d4af37" stroke-width="1.5" opacity="0.6" /></svg>
            <svg class="svg-absolute corner-bl" viewBox="0 0 340 180" preserveAspectRatio="none"><path d="M0,180 L0,40 Q80,100 160,140 T340,180 Z" fill="#d4af37" opacity="0.65" /></svg>
            <svg class="svg-absolute corner-br" viewBox="0 0 460 360"><path d="M460,360 L0,360 C120,310 320,200 370,100 C410,0 460,0 460,0 Z" fill="#0d2142" /><path d="M460,360 L10,360 C130,300 330,190 380,90 C420,0 460,0 460,0" fill="none" stroke="#d4af37" stroke-width="3.5" /></svg>
            <svg class="svg-absolute corner-tr" viewBox="0 0 200 200"><path d="M200,20 Q160,20 140,60 Q120,20 80,20" stroke="#d4af37" fill="none" stroke-width="2.5" /><path d="M140,60 Q140,100 180,120" stroke="#d4af37" fill="none" stroke-width="2.5" /><circle cx="140" cy="60" r="4" fill="#d4af37" /></svg>

            <!-- Premium Seal -->
            <div class="badge-seal">
                <svg viewBox="0 0 200 260" class="premium-seal-svg">
                    <g transform="translate(100, 160)">
                        <path d="M-35,0 L-50,80 L-25,65 L0,80 L0,0 Z" fill="#d4af37" />
                        <path d="M35,0 L50,80 L25,65 L0,80 L0,0 Z" fill="#d4af37" />
                    </g>
                    <circle cx="100" cy="78" r="72" fill="#d4af37" />
                    <circle cx="100" cy="78" r="68" fill="#0d2142" />
                    <g fill="none" stroke="#d4af37" stroke-width="1.5" transform="translate(100, 78)">
                        <path d="M-35,10 C-40,-15 -10,-35 0,-35 C10,-35 40,-15 35,10" opacity="0.5" />
                    </g>
                    <text x="100" y="65" font-family="Montserrat" font-size="11" font-weight="800" fill="#fff" text-anchor="middle">COMPLETION</text>
                    <text x="100" y="82" font-family="Montserrat" font-size="10" font-weight="700" fill="#d4af37" text-anchor="middle">CERTIFICATE</text>
                    <g fill="#d4af37" transform="translate(100, 95) scale(0.6)">
                        <path d="M-30,-5 L-27,0 L-22,0 L-26,4 L-24,9 L-30,6 L-36,9 L-34,4 L-38,0 L-33,0 Z" />
                        <path d="M-15,-5 L-12,0 L-7,0 L-11,4 L-9,9 L-15,6 L-21,9 L-19,4 L-23,0 L-18,0 Z" />
                        <path d="M0,-8 L4,0 L12,0 L6,6 L8,14 L0,9 L-8,14 L-6,6 L-12,0 L-4,0 Z" />
                        <path d="M15,-5 L18,0 L23,0 L19,4 L21,9 L15,6 L9,9 L11,4 L7,0 L12,0 Z" />
                        <path d="M30,-5 L33,0 L38,0 L34,4 L36,9 L30,6 L24,9 L26,4 L22,0 L27,0 Z" />
                    </g>
                    <path d="M100,118 L104,126 L112,126 L106,132 L108,140 L100,135 L92,140 L94,132 L88,126 L96,126 Z" fill="#d4af37" />
                </svg>
            </div>

            <div class="content">
                <div class="logo-wrap">
                    <span class="logo-text">SYSCOM INFOTECH</span>
                </div>
                <h1 class="title-main">CERTIFICATE</h1>
                <div class="title-sub">OF INTERNSHIP</div>
                <div class="certify-text"><span>THIS IS TO CERTIFY THAT</span></div>
                <div class="student-name">{{ $cert->student->full_name }}</div>
                <div class="desc-text">
                    has successfully completed the <span class="desc-highlight">"{{ $cert->internship_title ?: $cert->student->batch?->course?->name }}"</span><br>
                    organized by <strong>Syscom InfoTech</strong> during the period<br>
                    <span class="desc-highlight">{{ $cert->issue_date?->format('jS F Y') }} to {{ $cert->completion_date?->format('jS F Y') }}</span>.
                </div>
                
                <div class="divider-wish-wrap">
                    <!-- Overlapping Shield Watermark -->
                    <div class="shield-watermark">
                        <svg viewBox="0 0 100 120">
                            <path d="M50,115 C50,115 15,100 15,60 C15,20 50,5 50,5 C50,5 85,20 85,60 C85,100 50,115 50,115" fill="#fdfbf4" stroke="#d4af37" stroke-width="0.8" opacity="0.5" />
                            <g fill="#d4af37" opacity="0.8">
                                <circle cx="50" cy="15" r="1.5" />
                                <circle cx="40" cy="17" r="1.5" />
                                <circle cx="60" cy="17" r="1.5" />
                                <circle cx="32" cy="22" r="1.5" />
                                <circle cx="68" cy="22" r="1.5" />
                                <circle cx="26" cy="30" r="1.5" />
                                <circle cx="74" cy="30" r="1.5" />
                            </g>
                            <path d="M50,35 L58,58 L82,58 L62,72 L70,95 L50,80 L30,95 L38,72 L18,58 L42,58 Z" fill="#d4af37" opacity="0.7" />
                        </svg>
                    </div>

                    <svg class="divider-floral" viewBox="0 0 350 30">
                        <path d="M0,15 Q87,0 175,15 T350,15" fill="none" stroke="#d4af37" stroke-width="1.5" />
                        <path d="M175,8 L177.5,13 L183,13 L179,17 L180.5,22.5 L175,20 L169.5,22.5 L171,17 L167,13 L172.5,13 Z" fill="#d4af37" />
                    </svg>
                    
                    <div class="wish-text">We wish him/her all the best for a successful future.</div>
                </div>
            </div>

            <div class="bottom-signatures">
                <div class="sig-cell">
                    <div class="sig-block">
                        <div class="sig-name">N Thambirajan</div>
                        <div class="sig-line"></div>
                        <div class="sig-title">CEO</div>
                    </div>
                </div>
                <div class="sig-cell">
                    <div class="id-emblem-wrap" style="{{ (!$template || $template->show_certificate_id) ? '' : 'visibility: hidden;' }}">
                        <svg class="id-emblem-svg" viewBox="0 0 100 120">
                            <path d="M40,15 L42,10 L50,15 L58,10 L60,15 L50,18 Z" fill="#d4af37" />
                            <g fill="none" stroke="#d4af37" stroke-width="1.5">
                                <path d="M20,60 C15,30 40,20 50,20 C60,20 85,30 80,60 C75,90 50,100 50,100 C50,100 25,90 20,60" />
                                <circle cx="22" cy="50" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="25" cy="40" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="32" cy="32" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="42" cy="26" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="58" cy="26" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="68" cy="32" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="75" cy="40" r="1.5" fill="#d4af37" stroke="none" />
                                <circle cx="78" cy="50" r="1.5" fill="#d4af37" stroke="none" />
                            </g>
                            <path d="M50,35 L58,58 L82,58 L62,72 L70,95 L50,80 L30,95 L38,72 L18,58 L42,58 Z" fill="#d4af37" />
                        </svg>
                        <div class="cert-id-label">CERTIFICATE ID</div>
                        <div class="cert-id-value">{{ $cert->certificate_number }}</div>
                    </div>
                </div>
                <div class="sig-cell">
                    <div class="sig-block" style="{{ (!$template || $template->show_program_coordinator) ? '' : 'visibility: hidden;' }}">
                        <div class="sig-name">V. Santhosh</div>
                        <div class="sig-line"></div>
                        <div class="sig-title">Coordinator</div>
                    </div>
                </div>
            </div>

            <div class="qr-area">
                <div class="scan-text-mini">Scan to Verify</div>
                <div class="qr-white-box">{!! $qrSvg !!}</div>
                <div class="verification-badge">Verified Certificate</div>
            </div>

            <div class="website-section">
                <div class="website-text">
                    <span class="arrow-icon">&lt;</span>
                    www.syscominfotech.net
                    <span class="arrow-icon">&gt;</span>
                </div>
            </div>
        </div>

        <div class="details-footer">
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Student Name</div>
                    <div class="detail-value">{{ $cert->student->full_name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Course Title</div>
                    <div class="detail-value">{{ $cert->internship_title ?: $cert->student->batch?->course?->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Certificate ID</div>
                    <div class="detail-value text-danger">{{ $cert->certificate_number }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Issue Date</div>
                    <div class="detail-value">{{ $cert->issue_date?->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
