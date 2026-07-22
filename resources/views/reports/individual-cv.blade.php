<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Curriculum Vitae</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111827; }
        h1 { font-size: 22px; font-weight: bold; margin-bottom: 6px; }
        h2 { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        h3 { font-size: 10px; font-weight: bold; text-transform: uppercase;
             letter-spacing: 0.05em; color: #374151; margin: 0 0 5px; }
        p { margin: 0; }
        .cover { padding: 48px 32px; }
        .report-header {
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .report-header-logo-wrap { margin-bottom: 4px; }
        .report-header-logo {
            width: 54px;
            height: auto;
            display: inline-block;
        }
        .report-header-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.15px;
        }
        .report-header-subtitle {
            margin-top: 2px;
            font-size: 9.2px;
            color: #374151;
        }
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

    $logoDataUri = null;
    $logoPath = public_path('images/logo.png');
    if (is_file($logoPath) && is_readable($logoPath)) {
        $logoDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
    }

    $extra    = is_array($application->additional_info) ? $application->additional_info : [];
    $personal = data_get($extra, 'personal', []);
    $present  = data_get($extra, 'present_address', []);
    $perm     = data_get($extra, 'permanent_address', []);
    $edu      = data_get($extra, 'education', []);
    $job      = data_get($extra, 'job_experience', []);
    $choices  = data_get($extra, 'course_preferences', []);
@endphp

<div class="cover">
    <div class="report-header">
        <div class="report-header-logo-wrap">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="BIGM Logo" class="report-header-logo">
            @else
                <span style="font-size:12px;font-weight:bold;letter-spacing:1px;">BIGM</span>
            @endif
        </div>
        <p class="report-header-title">Bangladesh Institute of Governance and Management (BIGM)</p>
        <p class="report-header-subtitle">Curriculum Vitae (CV)</p>
    </div>

    {{-- Header row: basic info left, photo/signature right --}}
    <table class="layout">
        <tr>
            <td style="width: 65%; vertical-align: top; padding-right: 12px;">
                <table class="kv">
                    <tr><td class="k">Application ID</td><td>{{ $application->application_id ?? $application->ulid }}</td></tr>
                    <tr><td class="k">Name</td><td>{{ $toText($application->applicant_name) }}</td></tr>
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
            <td style="width: 35%; vertical-align: top; text-align: center; padding-top: 6px;">
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
                    $resultDisplay = $key === 'mphil_phd' ? 'N/A' : $formatEducationResult($row);
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $toText($examTitle) }}</td>
                    <td>{{ $toText($instituteOrBoard) }}</td>
                    <td>{{ $resultDisplay }}</td>
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

</div>{{-- .cover --}}
</body>
</html>

