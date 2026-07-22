<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $noticeTitle ?? 'Admit Card' }}</title>
    <style>
        body {
            margin: 0; padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 14px;
        }
        .wrapper {
            max-width: 600px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .top-bar {
            background: #1e3a5f;
            padding: 16px 24px;
            color: #ffffff;
            text-align: center;
        }
        .top-bar h1 { margin: 0; font-size: 16px; font-weight: 700; }
        .top-bar p  { margin: 3px 0 0; font-size: 11px; opacity: 0.85; }
        .body-content { padding: 24px 28px; }
        .greeting { font-size: 15px; font-weight: 700; margin: 0 0 12px; }
        .notice-box {
            border-left: 4px solid #1e3a5f;
            background: #eff6ff;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 13px;
            line-height: 1.6;
        }
        .notice-box.viva    { border-color: #f59e0b; background: #fffbeb; }
        .notice-box.program { border-color: #16a34a; background: #f0fdf4; }
        .attachment-note {
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 13px;
            color: #374151;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
        }
        .detail-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
        .detail-table td:first-child { font-weight: bold; color: #374151; width: 40%; }
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 12px 24px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
@php
    $exam       = $application->exam;
    $additional = is_array($application->additional_info) ? $application->additional_info : [];
    $examMeta   = is_array($exam?->additional_info) ? $exam->additional_info : [];

     $examDateText  = data_get($examMeta, 'exam_date')
//        ?? optional($exam?->start_date)->format('d M, Y (l)')
        ?? '31 July, 2026 (Friday)'
        ?? 'To be announced';
    $examTimeText  = data_get($examMeta, 'exam_time')
//        ?? optional($exam?->start_date)->format('h.i A')
        ?? '10:00 AM'
        ?? 'To be announced';


    $centerText   = data_get($examMeta, 'exam_center', 'BIGM Campus, E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka - 1207');

    $noticeTitle = match ($mailType) {
        'viva_eligibility'  => 'Viva Eligibility Notice',
        'program_selection' => 'Program Selection Notice',
        default             => 'Admit Card',
    };

    $selectedProgram = $application->selectedCategory?->name ?? 'Not assigned yet';
@endphp

<div class="wrapper">
    <div class="top-bar">
        <h1>Bangladesh Institute of Governance and Management (BIGM)</h1>
        <p>E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    <div class="body-content">
        <p class="greeting">Dear {{ $application->applicant_name ?: 'Applicant' }},</p>

        @if ($mailType === 'viva_eligibility')
            <div class="notice-box viva">
                <strong>Viva Eligibility:</strong> Congratulations! You are eligible for the viva examination
                for <strong>{{ $exam?->name ?? 'the admission exam' }}</strong>.
                Please attend as per schedule and bring all required documents.
            </div>
        @elseif ($mailType === 'program_selection')
            <div class="notice-box program">
                <strong>Program Selection:</strong> Congratulations! You have been selected for
                <strong>{{ $selectedProgram }}</strong> under
                <strong>{{ $exam?->name ?? 'the admission exam' }}</strong>.
            </div>
        @else
            <div class="notice-box">
                Your <strong>Admit Card</strong> for
                <strong>{{ $exam?->name ?? 'the BIGM Admission Exam' }}</strong>
                has been issued and is attached to this email as a PDF.
                Please download, print, and bring it to the examination hall.
            </div>
        @endif

        <div class="attachment-note">
            📎 <strong>Attached:</strong> {{ $noticeTitle }} (PDF) — download and keep a printed copy.
        </div>

        <table class="detail-table">
            <tr><td>Applicant ID</td><td>{{ $application->ulid }}</td></tr>
            <tr><td>Name</td><td>{{ $application->applicant_name ?: 'N/A' }}</td></tr>
            <tr><td>Exam</td><td>{{ $exam?->name ?? 'N/A' }}</td></tr>
            @if ($mailType === 'admit_card')
            <tr><td>Exam Date</td><td>{{ $examDateText }}</td></tr>
            <tr><td>Time</td><td>{{ $examTimeText }}</td></tr>
            <tr><td>Exam Center</td><td>{{ $centerText }}</td></tr>
            @endif
        </table>

        <p style="font-size:13px;color:#374151;margin-top:16px;">
            For any queries, please contact the BIGM office directly.<br>
            Please do not reply to this automatically generated email.
        </p>
    </div>

    <div class="footer">
        This is an auto-generated notification from BIGM Admission Portal. Please do not reply to this email.
    </div>
</div>
</body>
</html>

