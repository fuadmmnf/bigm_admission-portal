<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admit Card – {{ $application->applicant_name }}</title>
    <style>
        @page {
            margin: 14mm 14mm 14mm 14mm;
            size: A4 portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Georgia, serif;
            font-size: 11.5pt;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        /* ── Outer border ─────────────────────────────────── */
        .outer-border {
            border: 2.5pt solid #1e3a5f;
            padding: 0;
        }

        /* ── Header ───────────────────────────────────────── */
        .header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 8pt 12pt 6pt;
            text-align: center;
        }
        .header-logo-row {
            margin-bottom: 3pt;
        }
        .logo-badge {
            display: inline-block;
            border: 1.5pt solid #84cc16;
            border-radius: 20pt;
            padding: 1pt 14pt;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 2pt;
            color: #ffffff;
        }
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin: 2pt 0 0;
            letter-spacing: 0.3pt;
        }
        .header .sub {
            font-size: 8.5pt;
            margin: 1pt 0 0;
            opacity: 0.85;
        }

        /* ── Notice band (viva/program only) ─────────────── */
        .notice-band {
            padding: 6pt 10pt;
            font-size: 9.5pt;
            border-bottom: 1pt solid #d1d5db;
        }
        .notice-band.viva {
            background: #fffbeb;
            border-left: 4pt solid #f59e0b;
        }
        .notice-band.program {
            background: #f0fdf4;
            border-left: 4pt solid #16a34a;
        }

        /* ── Admit card title tag ─────────────────────────── */
        .card-title-row {
            text-align: center;
            padding: 7pt 0 5pt;
            border-bottom: 1pt solid #d1d5db;
        }
        .card-title-tag {
            display: inline-block;
            border: 1pt solid #374151;
            padding: 2pt 20pt;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1pt;
        }

        /* ── Main details ─────────────────────────────────── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            padding: 8pt 10pt;
        }
        .details-cell {
            padding: 8pt 10pt;
            vertical-align: top;
            width: 68%;
        }
        .photo-cell {
            padding: 8pt 10pt 8pt 4pt;
            vertical-align: top;
            width: 32%;
            text-align: center;
        }
        .meta-line {
            font-size: 10.5pt;
            margin: 0 0 3.5pt;
        }
        .meta-label {
            font-weight: bold;
            display: inline;
        }

        /* ── Photo/signature boxes ────────────────────────── */
        .photo-box {
            width: 100pt;
            height: 126pt;
            border: 1pt solid #9ca3af;
            overflow: hidden;
            text-align: center;
            line-height: 126pt;
            font-size: 9pt;
            color: #6b7280;
            display: block;
            margin: 0 auto;
        }
        .photo-box img {
            width: 100pt;
            height: 126pt;
            object-fit: cover;
            display: block;
        }
        .sig-label {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 3pt;
            text-align: center;
        }
        .sig-box {
            width: 100pt;
            height: 36pt;
            border: 1pt solid #9ca3af;
            overflow: hidden;
            text-align: center;
            line-height: 36pt;
            font-size: 9pt;
            color: #6b7280;
            display: block;
            margin: 4pt auto 0;
        }
        .sig-box img {
            width: 100pt;
            height: 36pt;
            object-fit: contain;
            display: block;
        }

        /* ── Section header ───────────────────────────────── */
        .section-divider {
            border: none;
            border-top: 1pt solid #d1d5db;
            margin: 0;
        }

        /* ── Instructions ─────────────────────────────────── */
        .instructions-wrap {
            padding: 7pt 12pt 8pt;
        }
        .instructions-title {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin: 0 0 5pt;
        }
        .instructions-list {
            margin: 0;
            padding-left: 16pt;
            font-size: 9pt;
            color: #374151;
        }
        .instructions-list li {
            margin-bottom: 3pt;
        }

        /* ── Footer ───────────────────────────────────────── */
        .footer {
            background: #f3f4f6;
            border-top: 1pt solid #d1d5db;
            padding: 5pt 10pt;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
@php
    $exam        = $application->exam;
    $additional  = is_array($application->additional_info) ? $application->additional_info : [];
    $personal    = data_get($additional, 'personal', []);
    $uploads     = data_get($additional, 'uploads', []);
    $examMeta    = is_array($exam?->additional_info) ? $exam->additional_info : [];

    /* ── Images as base64 data URIs (safe for queued PDF generation) ── */
    $photoDataUri = null;
    $sigDataUri   = null;
    $photoPath    = data_get($uploads, 'applicant_photo');
    $sigPath      = data_get($uploads, 'signature');

    if ($photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)) {
        $raw = \Illuminate\Support\Facades\Storage::disk('public')->get($photoPath);
        $ext = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
        $mime = match($ext) { 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', default => 'image/jpeg' };
        $photoDataUri = 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    if ($sigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
        $raw = \Illuminate\Support\Facades\Storage::disk('public')->get($sigPath);
        $ext = strtolower(pathinfo($sigPath, PATHINFO_EXTENSION));
        $mime = match($ext) { 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', default => 'image/jpeg' };
        $sigDataUri = 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    /* ── Exam details ── */
    $sessionStartYear  = $exam?->start_date?->year ?? now()->year;
    $defaultSession    = $sessionStartYear . '-' . ($sessionStartYear + 1);
    $admissionSession  = data_get($examMeta, 'admission_session', $defaultSession);

    $examDateText  = data_get($examMeta, 'exam_date')
        ?? optional($exam?->start_date)->format('d M, Y (l)')
        ?? 'To be announced';
    $examTimeText  = data_get($examMeta, 'exam_time')
        ?? optional($exam?->start_date)->format('h.i A')
        ?? 'To be announced';
    $centerText    = data_get($examMeta, 'exam_center', 'BIGM Campus, E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka - 1207');
    $examTypeText  = data_get($examMeta, 'exam_type', 'Written');
    $durationText  = data_get($examMeta, 'exam_duration', '1.30 Hours');

    $instructions = data_get($examMeta, 'admit_card_instructions', [
        'This admit card applies to both the written examination and viva voce.',
        'Applicant must present this admit card while sitting for the exam.',
        'Applicant must be seated in the examination hall at least 15 minutes before the exam starts.',
        'Applicant must use black ink ball point pen/pencil (if required) on the answer script.',
        'Bring original academic certificates and necessary documents for the Viva Board.',
        'Invigilators will verify the photograph on this card against the attendance sheet.',
        'Books, smart watches, mobile phones, and gadgets are prohibited (basic calculator may be allowed).',
        'Any misconduct or unfair means will result in expulsion from the exam.',
        'Applicants guilty of copying or unfair means will be barred from future BIGM examinations.',
    ]);
    if (!is_array($instructions)) {
        $instructions = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$instructions) ?: []));
    }

    $noticeTitle     = match ($mailType) {
        'viva_eligibility' => 'Viva Eligibility Notice',
        'program_selection' => 'Program Selection Notice',
        default => 'Admit Card',
    };
    $selectedProgram = $application->selectedCategory?->name ?? 'Not assigned yet';
@endphp

<div class="outer-border">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo-row">
            <span class="logo-badge">BIGM</span>
        </div>
        <h1>Bangladesh Institute of Governance and Management</h1>
        <p class="sub">E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    {{-- Viva / Program notice band --}}
    @if ($mailType === 'viva_eligibility')
        <div class="notice-band viva">
            <strong>Viva Eligibility Notice:</strong>
            You are eligible for the viva examination for <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
            Please attend as per schedule and bring your required documents.
        </div>
    @elseif ($mailType === 'program_selection')
        <div class="notice-band program">
            <strong>Program Selection Notice:</strong>
            Congratulations — you have been selected for
            <strong>{{ $selectedProgram }}</strong>
            under <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
        </div>
    @endif

    {{-- Title tag --}}
    <div class="card-title-row">
        <span class="card-title-tag">{{ $noticeTitle }} – Admission Test {{ $admissionSession }}</span>
    </div>

    {{-- Details + Photo --}}
    <table class="main-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="details-cell">
                <p class="meta-line"><span class="meta-label">Applicant ID:</span> {{ $application->ulid }}</p>
                <p class="meta-line"><span class="meta-label">Name:</span> {{ $application->applicant_name ?: 'N/A' }}</p>
                <p class="meta-line"><span class="meta-label">Father's Name:</span> {{ data_get($personal, 'father_name', 'N/A') }}</p>
                <p class="meta-line"><span class="meta-label">Mother's Name:</span> {{ data_get($personal, 'mother_name', 'N/A') }}</p>
                <p class="meta-line"><span class="meta-label">Exam:</span> {{ $exam?->name ?? 'N/A' }}</p>
                <p class="meta-line"><span class="meta-label">Exam Date:</span> {{ $examDateText }}</p>
                <p class="meta-line"><span class="meta-label">Time:</span> {{ $examTimeText }}</p>
                <p class="meta-line"><span class="meta-label">Exam Type:</span> {{ $examTypeText }}</p>
                <p class="meta-line"><span class="meta-label">Duration:</span> {{ $durationText }}</p>
                <p class="meta-line"><span class="meta-label">Exam Center:</span> {{ $centerText }}</p>
            </td>
            <td class="photo-cell">
                <div class="photo-box">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="Photo">
                    @else
                        Photo<br>not available
                    @endif
                </div>
                <p class="sig-label">Signature</p>
                <div class="sig-box">
                    @if($sigDataUri)
                        <img src="{{ $sigDataUri }}" alt="Signature">
                    @else
                        <span style="font-size:8pt;color:#9ca3af;">N/A</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <hr class="section-divider">

    {{-- Instructions --}}
    <div class="instructions-wrap">
        <p class="instructions-title">General Instructions for Applicants</p>
        <ul class="instructions-list">
            @foreach ($instructions as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Auto-generated by BIGM Admission Portal &nbsp;·&nbsp; Do not alter this document
    </div>

</div>
</body>
</html>

