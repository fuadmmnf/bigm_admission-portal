@extends('reports.layouts.report')

@section('title', 'Choice List Wise Report')
@section('report-subtitle', 'Choice List Report – All Paid Applicants')
@section('paper-orientation', 'landscape')

@section('extra-styles')
<style>

    .report-table .col-app-id {
        width: 55pt !important;
        text-align: center;
        font-size: 7.5pt;
    }

    .report-table .col-photo {
        width: 95pt !important;
    }

    .report-table .col-name {
        width: auto !important;
    }

    .col-choice { width: 36pt !important; text-align: center; font-size: 7.5pt; }
    .col-marks-stack { width: 62pt !important; }
    .marks-stack { font-size: 7.8pt; line-height: 1.25; }
    .marks-stack .row-label { font-weight: bold; }
</style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
{{--    <span><span class="label">Total Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>--}}
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-app-id">Application ID</th>
            <th class="col-photo">Photo</th>
            <th class="col-name">Applicant Name</th>
            <th class="col-marks-stack">Marks</th>
            <th class="col-choice">1st Choice</th>
            <th class="col-choice">2nd Choice</th>
            <th class="col-choice">3rd Choice</th>
            <th class="col-choice">4th Choice</th>
            <th class="col-choice">5th Choice</th>
            <th class="col-choice">6th Choice</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $application)
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
                <td class="col-app-id">{{ $application->application_id ?? $application->ulid }}</td>
                <td class="col-photo">
                    @if($application->photo_data_uri)
                        <img src="{{ $application->photo_data_uri }}" alt="Photo" class="report-photo">
                    @else
                        <div class="report-photo-placeholder">N/A</div>
                    @endif
                </td>
                <td class="col-name">{{ $application->applicant_name }}</td>
                <td class="col-marks-stack">
                    <div class="marks-stack">
                        <div><span class="row-label">Written:</span> {{ $application->written_exam_marks ?? '—' }}</div>
                        <div><span class="row-label">Viva:</span> {{ $application->viva_exam_marks ?? '—' }}</div>
                        <div><span class="row-label">Total:</span> {{ $total }}</div>
                    </div>
                </td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'first_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'second_choice', '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'third_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'fourth_choice', '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'fifth_choice',  '—')) }}</td>
                <td class="col-choice">{{ $shorten(data_get($pref, 'sixth_choice',  '—')) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="empty-row">No paid applicants found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

