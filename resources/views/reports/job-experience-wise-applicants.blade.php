<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Experience Wise Applicants - {{ $exam->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; }
        .logo { width: 200px; height: auto; display: block; margin-bottom: 10px; }
        .photo { width: 34px; height: 40px; border: 1px solid #d1d5db; object-fit: cover; display: block; margin: 0 auto; }
        .photo-placeholder { width: 34px; height: 40px; border: 1px solid #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #9ca3af; margin: 0 auto; }
        .photo-with-id { text-align: center; }
        .photo-app-id { margin-top: 3px; font-size: 9px; font-weight: 600; color: #1f2937; }
    </style>
</head>
<body>
    @php
        $logoDataUri = null;
        $logoPath = public_path('images/logo.png');
        if (is_file($logoPath) && is_readable($logoPath)) {
            $logoDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }
    @endphp

    @if($logoDataUri)
        <img src="{{ $logoDataUri }}" alt="BIGM Logo" class="logo">
    @endif
    <h2>Total Job-Experience Wise Report (Placeholder)</h2>
    <p><strong>Exam:</strong> {{ $exam->name }}</p>
    <p><strong>Total Paid Applicants:</strong> {{ $applications->count() }}</p>
    <p class="muted"><strong>Generated At:</strong> {{ $generatedAt->format('d M Y, h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Photo / App. ID</th>
                <th>Applicant Name</th>
                <th>Total Experience (Years)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $index => $application)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="photo-with-id">
                        @if($application->photo_data_uri)
                            <img src="{{ $application->photo_data_uri }}" alt="Photo" class="photo">
                        @else
                            <div class="photo-placeholder">N/A</div>
                        @endif
                        <div class="photo-app-id">{{ $application->application_id ?? $application->ulid }}</div>
                    </td>
                    <td>{{ $application->applicant_name }}</td>
                    <td>{{ data_get($application->additional_info, 'job_experience.total_years', 'N/A') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No paid applicants found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

