<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All Applicant CVs</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 0 0 8px; }
        h3 { font-size: 11px; margin: 0 0 6px; text-transform: uppercase; color: #374151; }
        p { margin: 0; }
        .meta { font-size: 10px; color: #4b5563; margin-bottom: 16px; }
        .cv { border: 1px solid #d1d5db; border-radius: 6px; padding: 12px; margin-bottom: 14px; }
        .page-break { page-break-after: always; }
        .row { width: 100%; clear: both; }
        .col { float: left; }
        .col-70 { width: 70%; }
        .col-30 { width: 30%; }
        .media-box { border: 1px solid #d1d5db; padding: 6px; margin-bottom: 8px; text-align: center; }
        .media-title { font-size: 9px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; }
        .photo { width: 110px; height: 120px; object-fit: cover; }
        .signature { width: 130px; height: 50px; object-fit: contain; }
        .kv { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .kv td { border: 1px solid #e5e7eb; padding: 4px 6px; vertical-align: top; }
        .k { width: 30%; background: #f9fafb; font-weight: 700; color: #374151; }
        .section { margin-top: 10px; }
        .edu { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .edu th, .edu td { border: 1px solid #e5e7eb; padding: 4px 5px; text-align: left; }
        .edu th { background: #f3f4f6; font-size: 10px; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
@php
    $toText = static fn ($value): string => blank($value) ? 'N/A' : (string) $value;
    $educationLabels = ['ssc' => 'SSC', 'hsc' => 'HSC', 'graduation' => 'Graduation', 'masters' => 'Masters'];
@endphp

<h1>All Applicant CVs</h1>
<p class="meta">
    Exam: {{ $exam->name }}
    &nbsp; | &nbsp;
    Applicants: {{ $applications->count() }}
    &nbsp; | &nbsp;
    Generated: {{ $generatedAt->format('d M Y h:i A') }}
</p>

@forelse ($applications as $application)
    @php
        $extra = is_array($application->additional_info) ? $application->additional_info : [];
        $personal = data_get($extra, 'personal', []);
        $present = data_get($extra, 'present_address', []);
        $permanent = data_get($extra, 'permanent_address', []);
        $education = data_get($extra, 'education', []);
        $job = data_get($extra, 'job_experience', []);
        $choices = data_get($extra, 'course_preferences', []);

        $addressText = static function (array $address): string {
            return implode(', ', array_filter([
                data_get($address, 'address_line'),
                data_get($address, 'post_office'),
                data_get($address, 'post_code'),
                data_get($address, 'upazila_name'),
                data_get($address, 'district_name'),
            ])) ?: 'N/A';
        };
    @endphp

    <section class="cv {{ $loop->last ? '' : 'page-break' }}">
        <div class="row">
            <div class="col col-70">
                <h2>{{ $toText($application->applicant_name) }}</h2>
                <p class="muted">Application ID: {{ $application->ulid }}</p>

                <table class="kv">
                    <tr><td class="k">Email</td><td>{{ $toText($application->applicant_email) }}</td></tr>
                    <tr><td class="k">Phone</td><td>{{ $toText($application->applicant_phone) }}</td></tr>
                    <tr><td class="k">ID Number</td><td>{{ $toText($application->applicant_id_number) }}</td></tr>
                    <tr><td class="k">Gender</td><td>{{ $toText($application->gender ?? data_get($personal, 'gender')) }}</td></tr>
                    <tr><td class="k">Date of Birth</td><td>{{ $toText(data_get($personal, 'date_of_birth')) }}</td></tr>
                    <tr><td class="k">Age</td><td>{{ $toText(data_get($personal, 'age_as_of_reference')) }}</td></tr>
                    <tr><td class="k">Status</td><td>{{ ucfirst($toText($application->status)) }} / {{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}</td></tr>
                </table>
            </div>
            <div class="col col-30">
                <div class="media-box">
                    <p class="media-title">Photo</p>
                    @if ($application->photo_data_uri)
                        <img src="{{ $application->photo_data_uri }}" alt="Applicant photo" class="photo">
                    @else
                        <p class="muted">N/A</p>
                    @endif
                </div>
                <div class="media-box">
                    <p class="media-title">Signature</p>
                    @if ($application->signature_data_uri)
                        <img src="{{ $application->signature_data_uri }}" alt="Applicant signature" class="signature">
                    @else
                        <p class="muted">N/A</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Family & Address</h3>
            <table class="kv">
                <tr><td class="k">Father's Name</td><td>{{ $toText(data_get($personal, 'father_name')) }}</td></tr>
                <tr><td class="k">Mother's Name</td><td>{{ $toText(data_get($personal, 'mother_name')) }}</td></tr>
                <tr><td class="k">Present Address</td><td>{{ $addressText($present) }}</td></tr>
                <tr><td class="k">Permanent Address</td><td>{{ $addressText($permanent) }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h3>Education</h3>
            <table class="edu">
                <thead>
                <tr>
                    <th>Level</th>
                    <th>Exam / Subject</th>
                    <th>Institute / Board</th>
                    <th>Result</th>
                    <th>Year</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($educationLabels as $key => $label)
                    @php
                        $row = data_get($education, $key, []);
                        $examTitle = data_get($row, 'examination');
                        if (in_array($key, ['graduation', 'masters'], true) && filled(data_get($row, 'subject'))) {
                            $examTitle = trim(($examTitle ?: 'N/A').' - '.data_get($row, 'subject'));
                        }
                        $instituteOrBoard = data_get($row, 'institution') ?: data_get($row, 'education_board');
                    @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $toText($examTitle) }}</td>
                        <td>{{ $toText($instituteOrBoard) }}</td>
                        <td>{{ $toText(data_get($row, 'result')) }} ({{ $toText(data_get($row, 'result_scale')) }})</td>
                        <td>{{ $toText(data_get($row, 'passing_year')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h3>Job Experience</h3>
            <table class="kv">
                <tr><td class="k">Total Experience (Years)</td><td>{{ $toText(data_get($job, 'total_years')) }}</td></tr>
                <tr><td class="k">Current Job</td><td>{{ $toText(data_get($job, 'current.designation')) }} @ {{ $toText(data_get($job, 'current.organization_name')) }}</td></tr>
                <tr><td class="k">Current Category</td><td>{{ $toText(data_get($job, 'current.job_category')) }}</td></tr>
                <tr><td class="k">Previous Job</td><td>{{ $toText(data_get($job, 'previous.designation')) }} @ {{ $toText(data_get($job, 'previous.organization_name')) }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h3>Course Preferences</h3>
            <table class="kv">
                <tr><td class="k">1st Choice</td><td>{{ $toText(data_get($choices, 'first_choice')) }}</td></tr>
                <tr><td class="k">2nd Choice</td><td>{{ $toText(data_get($choices, 'second_choice')) }}</td></tr>
                <tr><td class="k">3rd Choice</td><td>{{ $toText(data_get($choices, 'third_choice')) }}</td></tr>
                <tr><td class="k">4th Choice</td><td>{{ $toText(data_get($choices, 'fourth_choice')) }}</td></tr>
                <tr><td class="k">5th Choice</td><td>{{ $toText(data_get($choices, 'fifth_choice')) }}</td></tr>
                <tr><td class="k">6th Choice</td><td>{{ $toText(data_get($choices, 'sixth_choice')) }}</td></tr>
            </table>
        </div>
    </section>
@empty
    <p>No applications found for this exam.</p>
@endforelse
</body>
</html>

