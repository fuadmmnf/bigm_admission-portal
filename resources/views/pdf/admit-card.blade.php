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

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .outer-border {
            border: 1.2pt solid #111827;
            padding: 0;
        }

        .header {
            padding: 12pt 14pt 9pt;
            text-align: center;
            border-bottom: 1pt solid #d1d5db;
        }

        .header-logo-row {
            margin-bottom: 4pt;
        }

        .header-logo {
            width: 54pt;
            height: auto;
            display: inline-block;
        }

        .header h1 {
            font-size: 12.5pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.15pt;
        }

        .header .sub {
            font-size: 8.5pt;
            margin: 2pt 0 0;
        }

        .notice-band {
            margin: 10pt 12pt 0;
            padding: 7pt 9pt;
            font-size: 9.2pt;
            border: 1pt solid #cbd5e1;
            background: #f8fafc;
        }

        .card-title-row {
            text-align: center;
            padding: 10pt 12pt 8pt;
        }

        .card-title-tag {
            display: inline-block;
            border: 1pt solid #111827;
            padding: 4pt 18pt;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.4pt;
        }

        .card-session {
            margin-top: 5pt;
            font-size: 9pt;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-cell {
            padding: 0 12pt 10pt 12pt;
            vertical-align: top;
            width: 70%;
        }

        .photo-cell {
            padding: 0 12pt 10pt 4pt;
            vertical-align: top;
            width: 30%;
            text-align: center;
        }

        .details-panel {
            border: none;
            padding: 8pt 10pt;
        }

        .details-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .details-grid td {
            padding: 3pt 0;
            vertical-align: top;
            font-size: 10.8pt;
        }

        .details-grid .meta-label {
            width: 96pt;
            font-weight: bold;
            white-space: nowrap;
            padding-right: 8pt;
        }

        .details-grid .meta-value {
            word-break: break-word;
        }

        .identity-panel {
            border: none;
            padding: 8pt 8pt 7pt;
            min-height: 202pt;
        }

        .photo-box {
            width: 112pt;
            height: 136pt;
            border: 1pt solid #9ca3af;
            overflow: hidden;
            text-align: center;
            line-height: 136pt;
            font-size: 9pt;
            color: #6b7280;
            display: block;
            margin: 0 auto;
        }

        .photo-box img {
            width: 112pt;
            height: 136pt;
            object-fit: cover;
            display: block;
        }

        .identity-label {
            font-size: 8.2pt;
            color: #374151;
            margin-top: 6pt;
            margin-bottom: 3pt;
            text-align: center;
            font-weight: bold;
        }

        .sig-box {
            width: 112pt;
            height: 34pt;
            border: 1pt solid #9ca3af;
            overflow: hidden;
            text-align: center;
            line-height: 34pt;
            font-size: 9pt;
            color: #6b7280;
            display: block;
            margin: 0 auto;
        }

        .sig-box img {
            width: 112pt;
            height: 34pt;
            object-fit: contain;
            display: block;
        }

        .section-divider {
            border: none;
            border-top: 1pt solid #d1d5db;
            margin: 0;
        }

        .instructions-wrap {
            padding: 7pt 12pt 8pt;
            position: relative;
            overflow: hidden;
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

    $photoDataUri = null;
    $sigDataUri   = null;
    $photoPath    = $normalizePublicPath(data_get($uploads, 'applicant_photo'));
    $sigPath      = $normalizePublicPath(data_get($uploads, 'signature'));

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

    $logoDataUri = null;
    $logoPath = public_path('images/logo.png');
    if (is_file($logoPath) && is_readable($logoPath)) {
        $logoDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
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
    $displayText = static fn (?string $value, string $fallback = ''): string => trim((string) $value) !== '' ? trim((string) $value) : $fallback;
    $applicantId = $application->application_id ?: $application->ulid;
@endphp

<div class="outer-border">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo-row">
            @if($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="BIGM Logo" class="header-logo">
            @else
                <span style="font-size:12pt;font-weight:bold;letter-spacing:1pt;">BIGM</span>
            @endif
        </div>
        <h1>Bangladesh Institute of Governance and Management</h1>
        <p class="sub">E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    {{-- Viva / Program notice band --}}
    @if ($mailType === 'viva_eligibility')
        <div class="notice-band">
            <strong>Viva Eligibility Notice:</strong>
            You are eligible for the viva examination for <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
            Please attend as per schedule and bring your required documents.
        </div>
    @elseif ($mailType === 'program_selection')
        <div class="notice-band">
            <strong>Program Selection Notice:</strong>
            Congratulations — you have been selected for
            <strong>{{ $selectedProgram }}</strong>
            under <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
        </div>
    @endif

    {{-- Title tag --}}
    <div class="card-title-row">
        <span class="card-title-tag">{{ $noticeTitle }}</span>
{{--        <div class="card-session">Admission Test {{ $admissionSession }}</div>--}}
    </div>

    {{-- Details + Photo --}}
    <table class="main-table">
        <tr>
            <td class="details-cell">
                <div class="details-panel">
                    <table class="details-grid">
                        <tr>
                            <td class="meta-label">Applicant ID</td>
                            <td class="meta-value">: {{ $applicantId }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Name</td>
                            <td class="meta-value">: {{ $displayText($application->applicant_name) }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Father's Name</td>
                            <td class="meta-value">: {{ $displayText(data_get($personal, 'father_name')) }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Mother's Name</td>
                            <td class="meta-value">: {{ $displayText(data_get($personal, 'mother_name')) }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Exam</td>
                            <td class="meta-value">: {{ $displayText($exam?->name) }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Exam Date</td>
                            <td class="meta-value">: {{ $examDateText }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Exam Time</td>
                            <td class="meta-value">: {{ $examTimeText }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Exam Type</td>
                            <td class="meta-value">: {{ $examTypeText }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Duration</td>
                            <td class="meta-value">: {{ $durationText }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Exam Center</td>
                            <td class="meta-value">: {{ $centerText }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="photo-cell">
                <div class="identity-panel">
                    <div class="photo-box">
                        @if($photoDataUri)
                            <img src="{{ $photoDataUri }}" alt="Photo">
                        @else
                            Photo
                        @endif
                    </div>
                    <p class="identity-label">Applicant Photo</p>
                    <div class="sig-box">
                        @if($sigDataUri)
                            <img src="{{ $sigDataUri }}" alt="Signature">
                        @else
                            Signature
                        @endif
                    </div>
                    <p class="identity-label">Applicant Signature</p>
                </div>
            </td>
        </tr>
    </table>

    <hr class="section-divider">

    {{-- Instructions --}}
    <div class="instructions-wrap">
        @if($logoDataUri)
            <div style="position:absolute; left:50%; top:66%; transform:translate(-50%,-50%); opacity:0.06; z-index:0;">
                <img src="{{ $logoDataUri }}" alt="BIGM Watermark" style="width:260pt; height:auto;">
            </div>
        @endif
        <p class="instructions-title">General Instructions for Applicants</p>
        <ul class="instructions-list" style="position:relative; z-index:1;">
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

