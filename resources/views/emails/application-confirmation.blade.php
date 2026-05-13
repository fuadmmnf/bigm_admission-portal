<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPA Admission Application Confirmation</title>
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
        .success-box {
            border-left: 4px solid #16a34a;
            background: #f0fdf4;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 13px;
            line-height: 1.6;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
        }
        .detail-table td { padding: 7px 8px; border-bottom: 1px solid #f3f4f6; }
        .detail-table td:first-child { font-weight: bold; color: #374151; width: 40%; }
        .info-text {
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
            margin: 16px 0;
        }
        .contact-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
        }
        .signature {
            margin-top: 24px;
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
        }
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
    $exam          = $application->exam;
    $examName      = $exam?->name ?? 'MPA Admission Exam';
    $applicantName = $application->applicant_name ?: 'Applicant';
    $applicationId = $application->application_id ?? $application->ulid;
    $registrationDate = $application->updated_at
        ? $application->updated_at->format('d M Y')
        : now()->format('d M Y');
@endphp

<div class="wrapper">
    <div class="top-bar">
        <h1>Bangladesh Institute of Governance and Management (BIGM)</h1>
        <p>E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    <div class="body-content">
        <p class="greeting">Dear {{ $applicantName }},</p>

        <p class="info-text">
            Thank you for submitting your application for the <strong>{{ $examName }}</strong>
            at Bangladesh Institute of Governance and Management (BIGM).
        </p>

        <div class="success-box">
            We are pleased to confirm that we have successfully received your application with the following details:
        </div>

        <table class="detail-table">
            <tr>
                <td>Application ID</td>
                <td><strong>{{ $applicationId }}</strong></td>
            </tr>
            <tr>
                <td>Registration Date</td>
                <td>{{ $registrationDate }}</td>
            </tr>
        </table>

        <p class="info-text">
            Your application is currently under review. We will notify you regarding the schedule
            and further details of the written examination.
        </p>

        <div class="contact-box">
            If you have any queries, please feel free to contact the Admissions Office at
            <a href="mailto:admission@bigm.edu.bd" style="color:#1e3a5f;">admission@bigm.edu.bd</a>
            or call us at <strong>01716170855</strong> or <strong>01977303415</strong>.
        </div>

        <div class="signature">
            On behalf of,<br>
            <strong>Admission Office</strong><br>
            Bangladesh Institute of Governance and Management (BIGM)
        </div>
    </div>

    <div class="footer">
        This is an auto-generated notification from BIGM Admission Portal. Please do not reply to this email.
    </div>
</div>
</body>
</html>

