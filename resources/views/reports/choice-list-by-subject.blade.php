@extends('reports.layouts.report')

@section('title', 'Choice List By Subject – ' . $subject)
@section('report-subtitle', 'Choice List By Subject – ' . $subject)

@section('extra-styles')
    <style>

        .section-table .col-sl {
            width: 30pt !important;
            text-align: center;
        }

        .section-table .col-photo {
            width: 95pt !important;
        }

        .section-table .col-name {
            width: auto !important;
        }

        .section-table .col-marks {
            width: 50pt !important;
            text-align: center;
        }

        .section-table .col-choices {
            width: 150pt !important;
        }
    </style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Subject:</span> <strong>{{ $subject }}</strong></span>
    <span><span class="label">Total Across All Choices:</span><span class="summary-badge">{{ $totalCount }}</span></span>
</div>

@php
$ordinals = [
    'first_choice'  => '1st Choice',
    'second_choice' => '2nd Choice',
    'third_choice'  => '3rd Choice',
    'fourth_choice' => '4th Choice',
    'fifth_choice'  => '5th Choice',
    'sixth_choice'  => '6th Choice',
];
@endphp

@foreach ($ordinals as $field => $label)
    @php $group = $byChoice[$field] ?? collect(); @endphp
    <div class="section-heading">
        {{ $label }} for <strong>{{ $subject }}</strong>
        <span class="summary-badge">{{ $group->count() }}</span>
    </div>

    @if ($group->isEmpty())
        <p class="report-note">No applicants with {{ $subject }} as {{ $label }}.</p>
    @else
        <table class="report-table section-table">
            <thead>
                <tr>
                    <th class="col-sl">SL</th>
                    <th class="col-photo">Photo / App. ID</th>
                    <th class="col-name">Applicant Name</th>
                    <th class="col-marks">Written</th>
                    <th class="col-marks">Viva</th>
                    <th class="col-choices">All 6 Choices</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group as $i => $application)
                    @php
                        $pref = data_get($application->additional_info, 'course_preferences', []);
                        $allChoices = implode(' › ', array_filter([
                            data_get($pref, 'first_choice'),
                            data_get($pref, 'second_choice'),
                            data_get($pref, 'third_choice'),
                            data_get($pref, 'fourth_choice'),
                            data_get($pref, 'fifth_choice'),
                            data_get($pref, 'sixth_choice'),
                        ]));
                    @endphp
                    <tr>
                        <td class="col-sl">{{ $i + 1 }}</td>
                        <td class="col-photo photo-with-id">
                            @if($application->photo_data_uri)
                                <img src="{{ $application->photo_data_uri }}" alt="Photo" class="report-photo">
                            @else
                                <div class="report-photo-placeholder">N/A</div>
                            @endif
                            <div class="photo-app-id">{{ $application->application_id ?? $application->ulid }}</div>
                        </td>
                        <td class="col-name">{{ $application->applicant_name }}</td>
                        <td class="col-marks">{{ $application->written_exam_marks ?? '—' }}</td>
                        <td class="col-marks">{{ $application->viva_exam_marks ?? '—' }}</td>
                        <td class="col-choices">{{ $allChoices ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
@endsection

