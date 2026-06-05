@extends('reports.layouts.report')

@section('title', 'Viva Sheet')
@section('report-subtitle', 'Viva Sheet')

@section('extra-styles')
    <style>
        @page {
            size: A4 landscape;
        }

        .viva-top-sheet {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .viva-top-left,
        .viva-top-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            font-size: 9px;
        }

        .viva-top-right {
            text-align: right;
        }

        .viva-label {
            display: inline-block;
            margin-right: 4pt;
            vertical-align: middle;
        }

        .viva-box {
            display: inline-block;
            width: 170pt;
            height: 18pt;
            border: 1px solid #111827;
            vertical-align: middle;
        }

        .report-table.viva-table th {
            font-size: 7px;
            letter-spacing: 0.2pt;
            padding: 4pt 2pt;
        }

        .report-table.viva-table {
            table-layout: fixed;
        }

        .report-table.viva-table td {
            padding: 3pt 2pt;
            font-size: 8px;
        }

        .report-table.viva-table th {
            word-break: break-word;
        }

        .col-name {
            width: 25%;
        }

        .col-exp {
            width: 6%;
            text-align: center;
        }

        .col-workplace {
            width: 16%;
        }

        .col-designation {
            width: 12%;
        }

        .col-edu {
            width: 6%;
            text-align: center;
            font-size: 7px;
            padding-left: 1pt !important;
            padding-right: 1pt !important;
        }

        .col-point {
            width: 5%;
            text-align: center;
        }

        .col-mark {
            width: 6%;
            text-align: center;
        }

        .name-line {
            font-weight: bold;
        }

        .serial-inline {
            margin-right: 4pt;
        }

        .app-id-line {
            font-size: 7pt;
            margin-top: 2pt;
        }
    </style>
@endsection

@section('content')
    <div class="report-meta">
        <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    </div>

    <div class="viva-top-sheet">
        <div class="viva-top-left">
            <span class="viva-label">Invigilator Name:</span><span class="viva-box" style="width: 270pt;"></span>
        </div>

        <div class="viva-top-right">
            <span class="viva-label">Signature:</span><span class="viva-box"></span>
        </div>
    </div>

    <div class="viva-top-sheet">
        <div class="viva-top-left">
            <span class="viva-label">Invigilator Name:</span><span class="viva-box" style="width: 270pt;"></span>
        </div>

        <div class="viva-top-right">
            <span class="viva-label">Signature:</span><span class="viva-box"></span>
        </div>
    </div>

    <div class="viva-top-sheet">
        <div class="viva-top-left">
            <span class="viva-label">Invigilator Name:</span><span class="viva-box" style="width: 270pt;"></span>
        </div>

        <div class="viva-top-right">
            <span class="viva-label">Signature:</span><span class="viva-box"></span>
        </div>
    </div>

    <table class="report-table viva-table">
        <thead>
        <tr>
            <th class="col-name">Name</th>
            <th class="col-workplace">Workplace</th>
            <th class="col-designation">Designation</th>
            <th class="col-exp">Total Exp.</th>
            <th class="col-edu">SSC</th>
            <th class="col-edu">HSC</th>
            <th class="col-edu">Grad.</th>
            <th class="col-edu">Mst.</th>
            <th class="col-point">Point</th>
            <th class="col-mark">Written</th>
            <th class="col-mark">Viva</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($applications as $index => $application)
            @php
                $jobExp = data_get($application->additional_info, 'job_experience', []);
                $education = data_get($application->additional_info, 'education', []);

                $isFirstDivision = static function (mixed $value): bool {
                    $text = strtolower(trim((string) $value));

                    return in_array($text, [
                        '1st',
                        '1st division',
                        'first',
                        'first division',
                    ], true);
                };

                $sscResult = data_get($education, 'ssc.result');
                $hscResult = data_get($education, 'hsc.result');
                $graduationResult = data_get($education, 'graduation.result');

                $sscPoint =
                    (is_numeric($sscResult) && (float) $sscResult > 4)
                    || $isFirstDivision($sscResult)
                        ? 3
                        : 0;

                $hscPoint =
                    (is_numeric($hscResult) && (float) $hscResult > 4)
                    || $isFirstDivision($hscResult)
                        ? 3
                        : 0;

                $graduationPoint =
                    is_numeric($graduationResult)
                    && (float) $graduationResult >= 3
                        ? 3
                        : 0;

                $mastersPresent =
                    trim((string) data_get($education, 'masters.result', '')) !== ''
                    || trim((string) data_get($education, 'masters.subject', '')) !== ''
                    || trim((string) data_get($education, 'masters.institution', '')) !== ''
                    || trim((string) data_get($education, 'mphil_phd.subject', '')) !== ''
                    || trim((string) data_get($education, 'mphil_phd.institution', '')) !== ''
                    || trim((string) data_get($education, 'mphil_phd.degree_completion', '')) !== ''
                    || trim((string) data_get($education, 'mphil_phd.completion_year', '')) !== '';

                $mastersPoint = $mastersPresent ? 2 : 0;

                $totalPointRaw =
                    $sscPoint
                    + $hscPoint
                    + $graduationPoint
                    + $mastersPoint;

                $totalPoint = (string) min(11, $totalPointRaw);
            @endphp

            <tr>
                <td class="col-name">
                    <div class="name-line">
                        <span class="serial-inline">{{ $index + 1 }}.</span>
                        {{ $application->applicant_name }}
                    </div>

                    <div class="app-id-line">
                        App. ID:
                        {{ $application->application_id ?? $application->ulid }}
                    </div>
                </td>



                <td class="col-workplace">
                    {{ data_get($jobExp, 'current.organization_name', '') }}
                </td>

                <td class="col-designation">
                    {{ data_get($jobExp, 'current.designation', '') }}
                </td>

                <td class="col-exp">
                    {{ data_get($jobExp, 'total_years', '') }}
                </td>

                <td class="col-edu">
                    {{ $sscPoint }}
                </td>

                <td class="col-edu">
                    {{ $hscPoint }}
                </td>

                <td class="col-edu">
                    {{ $graduationPoint }}
                </td>

                <td class="col-edu">
                    {{ $mastersPoint }}
                </td>

                <td class="col-point">
                    {{ $totalPoint }}
                </td>

                <td class="col-mark">
                    {{
                        $application->written_exam_marks !== null
                            ? number_format((float) $application->written_exam_marks, 2)
                            : ''
                    }}
                </td>

                <td class="col-mark">
                    {{
                        $application->viva_exam_marks !== null
                            ? number_format((float) $application->viva_exam_marks, 2)
                            : ''
                    }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="empty-row">
                    No viva applicants found for this exam.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
