<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card - {{ $application->applicant_name }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            background: #f3f4f6;
            font-family: "Times New Roman", Georgia, serif;
            color: #111827;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 14px auto;
            background: #ffffff;
            padding: 18mm 14mm;
            box-sizing: border-box;
            position: relative;
        }

        .print-controls {
            max-width: 210mm;
            margin: 12px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .button {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 14px;
            background: #ffffff;
            color: #111827;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .button.primary {
            background: #111827;
            border-color: #111827;
            color: #ffffff;
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
        }

        .logo-mark {
            width: 260px;
            height: auto;
            margin: 0 auto 6px;
            display: block;
        }

        .title {
            font-size: 17px;
            line-height: 1.2;
            margin: 0;
            font-weight: 700;
        }

        .subtitle {
            font-size: 12px;
            margin-top: 2px;
        }

        .admit-title {
            text-align: center;
            margin-top: 12px;
        }

        .admit-title h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .admit-title p {
            margin: 1px 0 0;
            font-size: 15px;
            font-weight: 700;
        }

        .details {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-top: 16px;
        }

        .left {
            flex: 1;
            padding-right: 10px;
        }

        .right {
            width: 56mm;
            margin-top: 12px;
        }

        .meta-row {
            font-size: 12px;
            line-height: 1.35;
            margin: 0 0 2px;
        }

        .meta-row strong {
            font-weight: 700;
        }

        .photo-box {
            width: 56mm;
            height: 66mm;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 15px;
            color: #6b7280;
        }

        .photo-box img,
        .signature-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .signature-box {
            margin-top: 10px;
            width: 56mm;
            height: 20mm;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 15px;
            color: #6b7280;
        }

        .instructions {
            margin-top: 26px;
            position: relative;
            overflow: hidden;
            min-height: 95mm;
        }

        .instructions .watermark {
            position: absolute;
            left: 50%;
            top: 45%;
            transform: translate(-50%, -50%);
            color: rgba(107, 114, 128, 0.15);
            font-size: 170px;
            font-weight: 700;
            letter-spacing: 5px;
            pointer-events: none;
            user-select: none;
            z-index: 0;
        }

        .instructions-content {
            position: relative;
            z-index: 1;
        }

        .instructions-title {
            margin: 0 0 10px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-decoration: underline;
        }

        .instructions-list {
            margin: 0;
            padding-left: 28px;
            font-size: 11px;
            line-height: 1.3;
        }

        .instructions-list li {
            margin: 2px 0;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: #ffffff;
            }

            .print-controls {
                display: none;
            }

            .sheet {
                margin: 0;
                width: 210mm;
                min-height: 297mm;
                box-shadow: none;
                page-break-after: always;
            }
        }

        @media (max-width: 900px) {
            .sheet {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 16px;
            }

            .details {
                flex-direction: column;
            }

            .right,
            .photo-box,
            .signature-box {
                width: 100%;
            }

            .photo-box {
                height: 330px;
            }

            .meta-row,
            .instructions-title,
            .instructions-list,
            .admit-title h2,
            .admit-title p,
            .title,
            .subtitle {
                font-size: clamp(13px, 2.5vw, 18px);
            }
        }
    </style>
</head>
<body>
@php
    $exam = $application->exam;
    $additional = is_array($application->additional_info) ? $application->additional_info : [];
    $personal = data_get($additional, 'personal', []);
    $uploads = data_get($additional, 'uploads', []);
    $examMeta = is_array($exam?->additional_info) ? $exam->additional_info : [];

    $photoPath = data_get($uploads, 'applicant_photo');
    $signaturePath = data_get($uploads, 'signature');
    $normalizePublicPath = static function (?string $path): ?string {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim((string) $path, '/');
        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        return $normalized;
    };

    $photoPath = $normalizePublicPath($photoPath);
    $signaturePath = $normalizePublicPath($signaturePath);

    $photoUrl = $photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)
        ? route('public-media.show', ['path' => $photoPath])
        : null;
    $signatureUrl = $signaturePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($signaturePath)
        ? route('public-media.show', ['path' => $signaturePath])
        : null;
    $logoUrl = asset('images/logo.png');

    $sessionStartYear = $exam?->start_date?->year ?? now()->year;
    $defaultSession = $sessionStartYear.'-'.($sessionStartYear + 1);

     $examDateText  = data_get($examMeta, 'exam_date')
//        ?? optional($exam?->start_date)->format('d M, Y (l)')
        ?? '31 July, 2026 (Friday)'
        ?? 'To be announced';
    $examTimeText  = data_get($examMeta, 'exam_time')
//        ?? optional($exam?->start_date)->format('h.i A')
        ?? '10:00 AM'
        ?? 'To be announced';

    $centerText = data_get($examMeta, 'exam_center', 'BIGM Campus, E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka - 1207');
    $examTypeText = data_get($examMeta, 'exam_type', 'MCQ & Written');
    $durationText = data_get($examMeta, 'exam_duration', '1.30 Hours');

    $instructions = data_get($examMeta, 'admit_card_instructions', [
        'Candidates finaly selected for admission must submit all required documents (including the Migration
Certificate), a completed University of Dhaka registration form, and the registration fee of BDT 3,000 to
BIGM by 25 August 2026 (4:00 PM); late submission with a late fee of BDT 12,000 will be accepted until
14 September 2026.',
        'This admit card will be applicable for both written examination and viva voce.',
        'Applicant must show this admit card while sitting for exam.',
        'Applicant must sit in the examination hall at least 15 minutes before the exam starts.',
        'Applicant must use black ink ball point pen/pencil (if needed) for writing on the answer script.',
        'In addition to his/her academic certificates, applicants must bring original copies of necessary documents to submit before the Viva Board.',
        'Invigilators in the examination hall will verify the photograph of the applicant affixed in the attendance sheet with that of the admit card.',
        'Applicant is prohibited from bringing books, smart watch, mobile phone, gadgets (only basic calculator may be allowed).',
        'Applicant will be expelled from appearing the exam if general instructions are not followed or if found guilty for misconduct or adoption of any kind of unfair means.',
        'Applicant found guilty of copying, adopting of any unfair means will be barred from applying to sit for any exam/test to be organized by this institute in the future.',
    ]);

    if (!is_array($instructions)) {
        $instructions = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $instructions) ?: []));
    }

    $backUrl = $exam ? route('admin.exams.show', $exam) : route('admin-dashboard');
@endphp

<div class="print-controls">
    <a href="{{ $backUrl }}" class="button">Back to Applicants</a>
    <button type="button" onclick="window.print()" class="button primary">Print Admit Card</button>
</div>

<section class="sheet">
    <header class="header">
        <img src="{{ $logoUrl }}" alt="BIGM Logo" class="logo-mark">
        <h1 class="title">Bangladesh Institute of Governance and Management</h1>
        <div class="subtitle">E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka - 1207</div>

        <div class="admit-title">
            <h1>{{ $displayText($exam?->name) }}</h1>
            <h2>Admit Card</h2>
            <p>Admission test {{ data_get($examMeta, 'admission_session', $defaultSession) }}</p>
        </div>
    </header>

    <div class="details">
        <div class="left">
            <p class="meta-row"><strong>Applicant ID:</strong> {{ $application->application_id ?? $application->ulid }}
            </p>
            <p class="meta-row"><strong>Name:</strong> {{ $application->applicant_name ?: 'N/A' }}</p>
            <p class="meta-row"><strong>Email:</strong> {{ $application->applicant_email ?: 'N/A' }}</p>
{{--            <p class="meta-row"><strong>Father's Name:</strong> {{ data_get($personal, 'father_name', 'N/A') }}</p>--}}
{{--            <p class="meta-row"><strong>Mother's Name:</strong> {{ data_get($personal, 'mother_name', 'N/A') }}</p>--}}
            <p class="meta-row"><strong>Exam Center:</strong> {{ $centerText }}</p>
            <p class="meta-row"><strong>Exam Date & Time:</strong> {{ $examDateText }}</p>
            <p class="meta-row"><strong>Time:</strong> {{ $examTimeText }}</p>
            <p class="meta-row"><strong>Exam Type:</strong> {{ $examTypeText }}</p>
            <p class="meta-row"><strong>Duration:</strong> {{ $durationText }}</p>
        </div>

        <div class="right">
            <div class="photo-box">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Applicant Photo">
                @else
                    Photo not uploaded
                @endif
            </div>
            <div class="signature-box">
                @if($signatureUrl)
                    <img src="{{ $signatureUrl }}" alt="Applicant Signature">
                @else
                    Signature not uploaded
                @endif
            </div>
        </div>
    </div>

    <section class="instructions">
        <div class="watermark" style="opacity:0.08; font-size:0; line-height:0;">
            <img src="{{ $logoUrl }}" alt="BIGM Watermark" style="width:70%; height:auto;">
        </div>

        <div class="instructions-content">
            <h3 class="instructions-title">General instructions for applicants</h3>
            <ul class="instructions-list">
                @foreach ($instructions as $instruction)
                    <li>{{ $instruction }}</li>
                @endforeach
            </ul>
        </div>
    </section>
</section>
</body>
</html>





