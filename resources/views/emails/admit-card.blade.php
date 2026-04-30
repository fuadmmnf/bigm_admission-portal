<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: "Times New Roman", Georgia, serif;
            color: #111827;
            font-size: 14px;
        }
        .wrapper {
            max-width: 680px;
            margin: 24px auto;
            background: #ffffff;
        }
        .top-bar {
            background: #1e3a5f;
            padding: 14px 24px;
            color: #ffffff;
            text-align: center;
        }
        .top-bar h1 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .top-bar p {
            margin: 3px 0 0;
            font-size: 12px;
            opacity: 0.85;
        }
        .card {
            border: 1px solid #d1d5db;
            margin: 20px 24px;
            padding: 20px;
        }
        .card-header {
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .logo-text {
            display: inline-block;
            border: 2px solid #84cc16;
            border-radius: 50px;
            padding: 3px 18px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .card-header h2 {
            margin: 4px 0 0;
            font-size: 16px;
            font-weight: 700;
        }
        .card-header p {
            margin: 2px 0 0;
            font-size: 13px;
            color: #374151;
        }
        .admit-tag {
            text-align: center;
            margin: 10px 0;
        }
        .admit-tag span {
            display: inline-block;
            border: 1px solid #374151;
            padding: 3px 24px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .details-row {
            display: table;
            width: 100%;
        }
        .details-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
            padding-right: 16px;
        }
        .details-right {
            display: table-cell;
            vertical-align: top;
            width: 35%;
        }
        .meta-line {
            font-size: 13px;
            line-height: 1.5;
            margin: 2px 0;
        }
        .meta-line strong {
            font-weight: 700;
        }
        .photo-box {
            width: 120px;
            height: 150px;
            border: 1px solid #d1d5db;
            overflow: hidden;
            background: #f9fafb;
            text-align: center;
            vertical-align: middle;
            display: table-cell;
            font-size: 12px;
            color: #6b7280;
        }
        .photo-box img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            display: block;
        }
        .sig-box {
            width: 120px;
            height: 40px;
            border: 1px solid #d1d5db;
            overflow: hidden;
            background: #f9fafb;
            margin-top: 8px;
            text-align: center;
            vertical-align: middle;
            display: table-cell;
            font-size: 12px;
            color: #6b7280;
        }
        .sig-box img {
            width: 120px;
            height: 40px;
            object-fit: contain;
            display: block;
        }
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 16px 0;
        }
        .instructions-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
            margin: 0 0 10px;
        }
        .instructions-list {
            margin: 0;
            padding-left: 20px;
            font-size: 12px;
            line-height: 1.5;
            color: #374151;
        }
        .instructions-list li {
            margin-bottom: 4px;
        }
        .footer {
            background: #f9fafb;
            text-align: center;
            padding: 12px 24px;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
@php
    $mailType = $mailType ?? 'admit_card';
    $exam = $application->exam;
    $additional = is_array($application->additional_info) ? $application->additional_info : [];
    $personal = data_get($additional, 'personal', []);
    $uploads = data_get($additional, 'uploads', []);
    $examMeta = is_array($exam?->additional_info) ? $exam->additional_info : [];

    $photoPath = data_get($uploads, 'applicant_photo');
    $signaturePath = data_get($uploads, 'signature');
    $photoUrl = $photoPath ? asset('storage/' . $photoPath) : null;
    $signatureUrl = $signaturePath ? asset('storage/' . $signaturePath) : null;

    $sessionStartYear = $exam?->start_date?->year ?? now()->year;
    $defaultSession = $sessionStartYear . '-' . ($sessionStartYear + 1);

    $examDateText = data_get($examMeta, 'exam_date')
        ?? optional($exam?->start_date)->format('d M, Y (l)')
        ?? 'To be announced';

    $examTimeText = data_get($examMeta, 'exam_time')
        ?? optional($exam?->start_date)->format('h.i A')
        ?? 'To be announced';

    $centerText = data_get($examMeta, 'exam_center', 'BIGM Campus, E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka - 1207');
    $examTypeText = data_get($examMeta, 'exam_type', 'Written');
    $durationText = data_get($examMeta, 'exam_duration', '1.30 Hours');
    $admissionSession = data_get($examMeta, 'admission_session', $defaultSession);

    $instructions = data_get($examMeta, 'admit_card_instructions', [
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

    $noticeTitle = match ($mailType) {
        'viva_eligibility' => 'Viva Eligibility Notice',
        'program_selection' => 'Program Selection Notice',
        default => 'Admit Card',
    };

    $selectedProgram = $application->selectedCategory?->name ?? 'Not assigned yet';
@endphp

<div class="wrapper">
    <div class="top-bar">
        <h1>Bangladesh Institute of Governance and Management (BIGM)</h1>
        <p>E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    <div class="card">
        @if ($mailType === 'viva_eligibility')
            <div style="border:1px solid #f59e0b;background:#fffbeb;padding:10px 12px;margin-bottom:12px;font-size:13px;line-height:1.5;">
                <strong>{{ $noticeTitle }}:</strong>
                You are eligible for the viva examination for <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
                Please attend as per schedule and bring your required documents.
            </div>
        @elseif ($mailType === 'program_selection')
            <div style="border:1px solid #16a34a;background:#f0fdf4;padding:10px 12px;margin-bottom:12px;font-size:13px;line-height:1.5;">
                <strong>{{ $noticeTitle }}:</strong>
                Congratulations. You have been selected for program <strong>{{ $selectedProgram }}</strong>
                under <strong>{{ $exam?->name ?? 'this exam' }}</strong>.
            </div>
        @endif

        <div class="card-header">
            <div class="logo-text">BIGM</div>
            <h2>Bangladesh Institute of Governance and Management</h2>
            <p>E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
        </div>

        <div class="admit-tag">
            <span>{{ $noticeTitle }} &nbsp;–&nbsp; Admission Test {{ $admissionSession }}</span>
        </div>

        <br>

        <div class="details-row">
            <div class="details-left">
                <p class="meta-line"><strong>Applicant ID:</strong> {{ $application->ulid }}</p>
                <p class="meta-line"><strong>Name:</strong> {{ $application->applicant_name ?: 'N/A' }}</p>
                <p class="meta-line"><strong>Father's Name:</strong> {{ data_get($personal, 'father_name', 'N/A') }}</p>
                <p class="meta-line"><strong>Mother's Name:</strong> {{ data_get($personal, 'mother_name', 'N/A') }}</p>
                <p class="meta-line"><strong>Exam Center:</strong> {{ $centerText }}</p>
                <p class="meta-line"><strong>Exam Date:</strong> {{ $examDateText }}</p>
                <p class="meta-line"><strong>Time:</strong> {{ $examTimeText }}</p>
                <p class="meta-line"><strong>Exam Type:</strong> {{ $examTypeText }}</p>
                <p class="meta-line"><strong>Duration:</strong> {{ $durationText }}</p>
            </div>
            <div class="details-right">
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <div class="photo-box">
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Photo">
                                @else
                                    Photo<br>not<br>available
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sig-box" style="display: block;">
                                @if($signatureUrl)
                                    <img src="{{ $signatureUrl }}" alt="Signature">
                                @else
                                    <span style="font-size:11px;color:#9ca3af;line-height:40px;">No signature</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <hr class="divider">

        <p class="instructions-title">General Instructions for Applicants</p>
        <ul class="instructions-list">
            @foreach ($instructions as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    </div>

    <div class="footer">
        This is an auto-generated admit card from BIGM Admission Portal. Please do not reply to this email.<br>
        For queries, contact the BIGM office.
    </div>
</div>
</body>
</html>


