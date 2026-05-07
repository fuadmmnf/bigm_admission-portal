@extends('reports.layouts.report')

@section('title', 'Viva Sheet')
@section('report-subtitle', 'Viva Sheet')

@section('extra-styles')
<style>
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
        font-size: 7.5px;
        letter-spacing: 0.2pt;
        padding: 4pt 3pt;
    }
    .col-name { width: 132pt; }
    .col-exp { width: 44pt; text-align: center; }
    .col-work { width: 92pt; }
    .col-designation { width: 78pt; }
    .col-edu { width: 44pt; text-align: center; }
    .col-point { width: 50pt; text-align: center; }
    .col-mark { width: 52pt; text-align: center; }
    .name-line { font-weight: bold; }
    .serial-inline { margin-right: 4pt; }
    .app-id-line { font-size: 7.5pt; margin-top: 2pt; }
</style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
</div>

<div class="viva-top-sheet">
    <div class="viva-top-left">
        <span class="viva-label">Invigilator Name:</span><span class="viva-box"></span>
    </div>
    <div class="viva-top-right">
        <span class="viva-label">Invigilator Signature:</span><span class="viva-box"></span>
    </div>
</div>

<table class="report-table viva-table">
    <thead>
        <tr>
            <th class="col-name">Name</th>
            <th class="col-exp">Years of Exp.</th>
            <th class="col-work">Place of Work</th>
            <th class="col-designation">Designation</th>
            <th class="col-edu">SSC</th>
            <th class="col-edu">HSC</th>
            <th class="col-edu">Graduation</th>
            <th class="col-edu">Masters</th>
            <th class="col-point">Total Point</th>
            <th class="col-mark">Written Marks</th>
            <th class="col-mark">Viva Voce Marks</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            @php
                $jobExp = data_get($application->additional_info, 'job_experience', []);
                $education = data_get($application->additional_info, 'education', []);
                $formatEducation = function (string $level) use ($education): string {
                    $row = data_get($education, $level, []);
                    $result = data_get($row, 'result');
                    if ($result === null || $result === '') {
                        return '';
                    }

                    $scale = data_get($row, 'result_scale');
                    if ($scale !== null && $scale !== '' && strtolower((string) $scale) !== 'division') {
                        return $result.'/'.$scale;
                    }

                    return (string) $result;
                };

                $numericEducationPoints = collect(['ssc', 'hsc', 'graduation', 'masters'])
                    ->map(fn (string $level): mixed => data_get($education, $level.'.result'))
                    ->filter(fn (mixed $value): bool => is_numeric($value));
                $totalPoint = $numericEducationPoints->isNotEmpty()
                    ? number_format((float) $numericEducationPoints->sum(), 2)
                    : '';
            @endphp
            <tr>
                <td class="col-name">
                    <div class="name-line"><span class="serial-inline">{{ $index + 1 }}.</span>{{ $application->applicant_name }}</div>
                    <div class="app-id-line">App. ID: {{ $application->application_id ?? $application->ulid }}</div>
                </td>
                <td class="col-exp">{{ data_get($jobExp, 'total_years', '') }}</td>
                <td>{{ data_get($jobExp, 'current.organization_name', '') }}</td>
                <td>{{ data_get($jobExp, 'current.designation', '') }}</td>
                <td class="col-edu">{{ $formatEducation('ssc') }}</td>
                <td class="col-edu">{{ $formatEducation('hsc') }}</td>
                <td class="col-edu">{{ $formatEducation('graduation') }}</td>
                <td class="col-edu">{{ $formatEducation('masters') }}</td>
                <td class="col-point">{{ $totalPoint }}</td>
                <td class="col-mark">{{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : '' }}</td>
                <td class="col-mark">{{ $application->viva_exam_marks !== null ? number_format((float) $application->viva_exam_marks, 2) : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="empty-row">No viva applicants found for this exam.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
