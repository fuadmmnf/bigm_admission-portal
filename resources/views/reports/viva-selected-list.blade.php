<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Viva Selected List - {{ $exam->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .meta {
            margin: 4px 0;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
        }

        .col-sl {
            width: 42px;
            text-align: center;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Viva Selected Applicants</p>
        <p class="meta"><strong>Exam:</strong> {{ $exam->name }}</p>
        <p class="meta"><strong>Total Viva Selected:</strong> {{ $applications->count() }}</p>
        <p class="meta muted"><strong>Generated At:</strong> {{ $generatedAt->format('d M Y, h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-sl">SL</th>
                <th>Applicant ID</th>
                <th>Applicant Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Written Marks</th>
                <th>Viva Marks</th>
                <th>Selection Stage</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $index => $application)
                <tr>
                    <td class="col-sl">{{ $index + 1 }}</td>
                    <td>{{ $application->ulid }}</td>
                    <td>{{ $application->applicant_name }}</td>
                    <td>{{ $application->applicant_phone }}</td>
                    <td>{{ $application->applicant_email }}</td>
                    <td>{{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : 'N/A' }}</td>
                    <td>{{ $application->viva_exam_marks !== null ? number_format((float) $application->viva_exam_marks, 2) : 'N/A' }}</td>
                    <td>{{ str($application->selection_stage)->replace('_', ' ')->title() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;" class="muted">No viva-selected applicants found for this exam.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

