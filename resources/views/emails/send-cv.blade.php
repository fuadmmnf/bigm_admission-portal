<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Curriculum Vitae (CV)</title>
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
@endphp

<div class="wrapper">
    <div class="top-bar">
        <h1>Bangladesh Institute of Governance and Management (BIGM)</h1>
        <p>E-33, Sher-E-Bangla Nagar, Agargaon, Dhaka – 1207</p>
    </div>

    <div class="body-content">

        <p class="greeting">
            Dear {{ $application->applicant_name ?: 'Applicant' }},
        </p>

        <p>
            Thank you for applying to the
            <strong>{{ $exam?->name ?? 'BIGM Admission' }}</strong>.
        </p>

        <p>
            Attached is a PDF copy of your <strong>Curriculum Vitae (CV)</strong>,
            generated from the information you submitted through the BIGM Admission
            Portal. Please review the document and keep it for your records.
        </p>

        <h3 style="margin-top:24px;margin-bottom:12px;font-size:15px;color:#1e3a5f;">
            Applicant Information
        </h3>

        <table class="detail-table">
            <tr>
                <td>Applicant ID</td>
                <td>{{ $application->application_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Name</td>
                <td>{{ $application->applicant_name ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $application->applicant_email ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td>Admission</td>
                <td>{{ $exam?->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <p>
            For any queries regarding your application, please contact the BIGM
            Admission Office.
        </p>

        <p style="margin-top:24px;">
            Kind regards,
        </p>

        <p style="margin-top:8px;">
            <strong>BIGM Admission Office</strong><br>
            Bangladesh Institute of Governance and Management (BIGM)
        </p>

    </div>
    <div class="footer">
        This is an auto-generated notification from BIGM Admission Portal. Please do not reply to this email.
    </div>
</div>
</body>
</html>

