@extends('reports.layouts.report')

@section('title', 'Choice List Wise Report')
@section('report-subtitle', 'Choice List Report – All Paid Applicants')
@section('paper-orientation', 'landscape')

@section('extra-styles')
<style>
    .col-choice { width: 36pt; text-align: center; font-size: 7.5pt; }
    .col-marks  { width: 34pt; text-align: center; }
    .col-total  { width: 36pt; text-align: center; font-weight: bold; }
</style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Total Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-appid">App. ID</th>
            <th>Applicant Name</th>
            <th class="col-marks">Written</th>
            <th class="col-marks">Viva</th>
            <th class="col-total">Total</th>
            <th class="col-choice">1st Choice</th>
            <th class="col-choice">2nd Choice</th>
            <th class="col-choice">3rd Choice</th>
            <th class="col-choice">4th Choice</th>
            <th class="col-choice">5th Choice</th>
            <th class="col-choice">6th Choice</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            @php
                $pref = data_get($application->additional_info, 'course_preferences', []);
                $written = (float) ($application->written_exam_marks ?? 0);
                $viva    = (float) ($application->viva_exam_marks ?? 0);
                $total   = ($application->written_exam_marks !== null || $application->viva_exam_marks !== null)
                           ? number_format($written + $viva, 2)
                           : '—';
                $shorten = fn(string $name): string => strlen($name) > 18 ? substr($name, 0, 17) . '…' : $name;
            @endphp
            <tr>
                <td class="col-sl">{{ $index + 1 }}</td>
                <td>{{ $application->application_id ?? $application->ulid }}</td>
                <td>{{ $application->applicant_name }}</td>
                <td class="col-marks">{{ $application->written_exam_marks ?? '—' }}</td>
                <td class="col-marks">{{ $application->viva_exam_marks ?? '—' }}</td>
                <td class="col-total">{{ $total }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'first_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'second_choice', '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'third_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'fourth_choice', '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'fifth_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'sixth_choice',  '—')) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="muted" style="text-align:center;">No paid applicants found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

