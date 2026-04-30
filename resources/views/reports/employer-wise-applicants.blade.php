<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employer Wise Applicants - {{ $exam->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h2>Employer Wise Report (Placeholder)</h2>
    <p><strong>Exam:</strong> {{ $exam->name }}</p>
    <p><strong>Total Paid Applicants:</strong> {{ $applications->count() }}</p>
    <p class="muted"><strong>Generated At:</strong> {{ $generatedAt->format('d M Y, h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Applicant ID</th>
                <th>Applicant Name</th>
                <th>Employer</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $index => $application)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $application->ulid }}</td>
                    <td>{{ $application->applicant_name }}</td>
                    <td>{{ data_get($application->additional_info, 'job_experience.current.organization_name', 'N/A') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No paid applicants found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

