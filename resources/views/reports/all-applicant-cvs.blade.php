<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All Applicant CVs</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111827; }
        h1 { font-size: 22px; font-weight: bold; margin-bottom: 6px; }
        h2 { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        h3 { font-size: 10px; font-weight: bold; text-transform: uppercase;
             letter-spacing: 0.05em; color: #374151; margin: 0 0 5px; }
        p { margin: 0; }
        .cover { padding: 48px 32px; page-break-after: always; }
        .cover-meta { margin-top: 12px; font-size: 11px; color: #4b5563; line-height: 1.8; }
        .cover-meta strong { color: #111827; }
        .cover-divider { border: none; border-top: 2px solid #6366f1; margin: 16px 0; width: 80px; }
        .applicant { padding: 18px 24px; page-break-after: always; }
        .section { margin-top: 12px; }
        .section-title { background: #f3f4f6; border-left: 3px solid #6366f1;
                         padding: 4px 8px; margin-bottom: 6px; }
        table.layout { width: 100%; border-collapse: collapse; }
        table.kv { width: 100%; border-collapse: collapse; margin-top: 0; }
        table.kv td { border: 1px solid #e5e7eb; padding: 4px 7px; vertical-align: top; }
        table.kv td.k { width: 34%; background: #f9fafb; font-weight: bold; color: #374151; }
        table.edu { width: 100%; border-collapse: collapse; }
        table.edu th, table.edu td { border: 1px solid #e5e7eb; padding: 4px 5px; text-align: left; font-size: 10px; }
        table.edu th { background: #f3f4f6; font-weight: bold; }
        .muted { color: #9ca3af; }
        .badge { display: inline; padding: 2px 6px; background: #ede9fe; color: #4f46e5;
                 border-radius: 9999px; font-size: 9.5px; font-weight: bold; }
        img.photo { width: 110px; height: 130px; }
        img.signature { width: 140px; height: 55px; }
        .media-label { font-size: 9px; text-transform: uppercase; color: #6b7280;
                        margin-bottom: 3px; text-align: center; }
        .media-frame { border: 1px solid #d1d5db; padding: 5px; text-align: center; margin-bottom: 6px; }
        .no-media { font-size: 10px; color: #9ca3af; padding: 12px 0; }
    </style>
</head>
<body>
@php
    $toText = static fn ($value): string => blank($value) ? 'N/A' : (string) $value;
    $educationLabels = ['ssc' => 'SSC', 'hsc' => 'HSC', 'graduation' => 'Graduation', 'masters' => 'Masters', 'mphil_phd' => 'MPhil / PhD'];

    $addressText = static function (array $addr): string {
        return implode(', ', array_filter([
            data_get($addr, 'address_line'),
            data_get($addr, 'post_office'),
            data_get($addr, 'post_code'),
            data_get($addr, 'upazila_name'),
            data_get($addr, 'district_name'),
        ])) ?: 'N/A';
    };

    $formatEducationResult = static function (array $row) use ($toText): string {
        $resultType = data_get($row, 'result_type');
        if ($resultType === 'division' || strcasecmp((string) data_get($row, 'result_scale'), 'Division') === 0) {
            return $toText(data_get($row, 'division') ?: data_get($row, 'result'));
        }

        $result = data_get($row, 'result');
        $scale = data_get($row, 'result_scale');
        if (blank($result) && blank($scale)) {
            return 'N/A';
        }

        return sprintf('%s (%s)', $toText($result), $toText($scale));
    };
@endphp

{{-- ── Cover Page ── --}}
<div class="cover">
    <h1>All Applicant CVs</h1>
    <hr class="cover-divider">
    <div class="cover-meta">
        <p><strong>Exam:</strong> {{ $exam->name }}</p>
        <p><strong>Total Applicants:</strong> {{ $applications->count() }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt->format('d M Y, h:i A') }}</p>
        @if ($exam->start_date || $exam->end_date)
            <p><strong>Application Window:</strong>
                {{ optional($exam->start_date)->format('d M Y') ?? 'N/A' }} –
                {{ optional($exam->end_date)->format('d M Y') ?? 'N/A' }}
            </p>
        @endif
    </div>
</div>

{{-- ── Per-Applicant CV Pages ── --}}
@forelse ($applications as $application)
    @php
        $extra    = is_array($application->additional_info) ? $application->additional_info : [];
        $personal = data_get($extra, 'personal', []);
        $present  = data_get($extra, 'present_address', []);
        $perm     = data_get($extra, 'permanent_address', []);
        $edu      = data_get($extra, 'education', []);
        $job      = data_get($extra, 'job_experience', []);
        $choices  = data_get($extra, 'course_preferences', []);
    @endphp

    <div class="applicant">

        {{-- Header row: basic info left, photo/signature right --}}
        <table class="layout">
            <tr>
                <td style="width: 65%; vertical-align: top; padding-right: 12px;">
                    <h2>{{ $toText($application->applicant_name) }}</h2>
                    <p class="muted" style="font-size:9.5px; margin-bottom:8px;">
                        Application ID: {{ $application->ulid }}
                    </p>
                    <table class="kv">
                        <tr><td class="k">Email</td><td>{{ $toText($application->applicant_email) }}</td></tr>
                        <tr><td class="k">Phone</td><td>{{ $toText($application->applicant_phone) }}</td></tr>
                        <tr><td class="k">NID / Passport</td><td>{{ $toText($application->applicant_nid) }}</td></tr>
                        <tr><td class="k">Gender</td><td>{{ $toText($application->gender ?? data_get($personal, 'gender')) }}</td></tr>
                        <tr><td class="k">Date of Birth</td><td>{{ $toText(data_get($personal, 'date_of_birth')) }}</td></tr>
                        <tr><td class="k">Age</td><td>{{ $toText(data_get($personal, 'age_as_of_reference')) }}</td></tr>
                        <tr>
                            <td class="k">Stage</td>
                            <td><span class="badge">{{ str($application->selection_stage ?? 'paid')->replace('_', ' ')->title() }}</span></td>
                        </tr>
                        <tr><td class="k">Written Exam Marks</td><td>{{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : 'N/A' }}</td></tr>
                        <tr><td class="k">Viva Exam Marks</td><td>{{ $application->viva_exam_marks !== null ? number_format((float) $application->viva_exam_marks, 2) : 'N/A' }}</td></tr>
                        <tr><td class="k">Selected Program / Course</td><td>{{ $toText($application->selectedCategory?->name) }}</td></tr>
                    </table>
                </td>
                <td style="width: 35%; vertical-align: top; text-align: center;">
                    <div class="media-frame">
                        <p class="media-label">Photo</p>
                        @if ($application->photo_data_uri)
                            <img src="{{ $application->photo_data_uri }}" alt="Photo" class="photo">
                        @else
                            <p class="no-media">N/A</p>
                        @endif
                    </div>
                    <div class="media-frame">
                        <p class="media-label">Signature</p>
                        @if ($application->signature_data_uri)
                            <img src="{{ $application->signature_data_uri }}" alt="Signature" class="signature">
                        @else
                            <p class="no-media">N/A</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Family & Address --}}
        <div class="section">
            <div class="section-title"><h3>Family &amp; Address</h3></div>
            <table class="kv">
                <tr><td class="k">Father's Name</td><td>{{ $toText(data_get($personal, 'father_name')) }}</td></tr>
                <tr><td class="k">Mother's Name</td><td>{{ $toText(data_get($personal, 'mother_name')) }}</td></tr>
                <tr><td class="k">Present Address</td><td>{{ $addressText($present) }}</td></tr>
                <tr><td class="k">Permanent Address</td><td>{{ $addressText($perm) }}</td></tr>
            </table>
        </div>

        {{-- Education --}}
        <div class="section">
            <div class="section-title"><h3>Education</h3></div>
            <table class="edu">
                <thead>
                <tr>
                    <th style="width:14%">Level</th>
                    <th>Exam / Subject</th>
                    <th>Institute / Board</th>
                    <th style="width:18%">Result</th>
                    <th style="width:10%">Year</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($educationLabels as $key => $label)
                    @php
                        $row = data_get($edu, $key, []);
                        $examTitle = data_get($row, 'examination');
                        if (in_array($key, ['graduation', 'masters', 'mphil_phd']) && filled(data_get($row, 'subject'))) {
                            $examTitle = trim(($examTitle ?: 'N/A').' – '.data_get($row, 'subject'));
                        }
                        $instituteOrBoard = data_get($row, 'institution') ?: data_get($row, 'education_board');
                    @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $toText($examTitle) }}</td>
                        <td>{{ $toText($instituteOrBoard) }}</td>
                        <td>{{ $formatEducationResult($row) }}</td>
                        <td>{{ $toText(data_get($row, 'passing_year')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Job Experience --}}
        <div class="section">
            <div class="section-title"><h3>Job Experience</h3></div>
            <table class="kv">
                <tr><td class="k">Total Experience</td><td>{{ $toText(data_get($job, 'total_years')) }} years</td></tr>
                <tr>
                    <td class="k">Current Position</td>
                    <td>{{ $toText(data_get($job, 'current.designation')) }}
                        @ {{ $toText(data_get($job, 'current.organization_name')) }}
                        ({{ $toText(data_get($job, 'current.job_category')) }})
                    </td>
                </tr>
                <tr>
                    <td class="k">Previous Position</td>
                    <td>{{ $toText(data_get($job, 'previous.designation')) }}
                        @ {{ $toText(data_get($job, 'previous.organization_name')) }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Course Preferences --}}
        <div class="section">
            <div class="section-title"><h3>Course Preferences</h3></div>
            <table class="kv">
                <tr><td class="k" style="width:18%">1st Choice</td><td>{{ $toText(data_get($choices, 'first_choice')) }}</td>
                    <td class="k" style="width:18%">2nd Choice</td><td>{{ $toText(data_get($choices, 'second_choice')) }}</td></tr>
                <tr><td class="k">3rd Choice</td><td>{{ $toText(data_get($choices, 'third_choice')) }}</td>
                    <td class="k">4th Choice</td><td>{{ $toText(data_get($choices, 'fourth_choice')) }}</td></tr>
                <tr><td class="k">5th Choice</td><td>{{ $toText(data_get($choices, 'fifth_choice')) }}</td>
                    <td class="k">6th Choice</td><td>{{ $toText(data_get($choices, 'sixth_choice')) }}</td></tr>
            </table>
        </div>

    </div>{{-- .applicant --}}
@empty
    <div style="padding: 32px;">
        <p>No applications found for this exam.</p>
    </div>
@endforelse
</body>
</html>

