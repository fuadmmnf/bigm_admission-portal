@extends('reports.layouts.report')

@section('title', 'Program Selected Applicants')
@section('report-subtitle', 'Program Selected Applicants by Program Code')
@section('paper-orientation', 'landscape')

@section('extra-styles')
    <style>

        .report-table .col-sl {
            width: 30pt !important;
            text-align: center;
        }

        .report-table .col-photo {
            width: 95pt !important;
        }

        .report-table .col-name {
            width: auto !important;
        }

        .report-table .col-contact {
            width: 90pt !important;
        }

        .report-table .col-marks {
            width: 50pt !important;
            text-align: center;
        }

        .report-table .col-stage {
            width: 70pt !important;
            text-align: center;
        }
    </style>
@endsection

@section('content')
@php
    $programCode = data_get($programCategory->additional_info, 'code', $programCategory->name);
@endphp

<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Program Code:</span> <span class="summary-badge">{{ $programCode }}</span></span>
{{--    <span><span class="label">Total Selected:</span> <span class="summary-badge">{{ $applications->count() }}</span></span>--}}
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-photo">Photo / App. ID</th>
            <th class="col-name">Applicant Name</th>
            <th class="col-contact">Phone</th>
            <th class="col-contact">Email</th>
            <th class="col-marks">Written</th>
            <th class="col-marks">Viva</th>
            <th class="col-stage">Stage</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            <tr>
                <td class="col-sl">{{ $index + 1 }}</td>
                <td class="col-photo photo-with-id">
                    @if($application->photo_data_uri)
                        <img src="{{ $application->photo_data_uri }}" alt="Photo" class="report-photo">
                    @else
                        <div class="report-photo-placeholder">N/A</div>
                    @endif
                    <div class="photo-app-id">{{ $application->application_id ?? $application->ulid }}</div>
                </td>
                <td class="col-name">{{ $application->applicant_name }}</td>
                <td class="col-contact">{{ $application->applicant_phone }}</td>
                <td class="col-contact">{{ $application->applicant_email }}</td>
                <td class="col-marks">{{ $application->written_exam_marks !== null ? number_format((float) $application->written_exam_marks, 2) : 'N/A' }}</td>
                <td class="col-marks">{{ $application->viva_exam_marks !== null ? number_format((float) $application->viva_exam_marks, 2) : 'N/A' }}</td>
                <td class="col-stage">{{ str($application->selection_stage ?? '')->replace('_', ' ')->title() ?: 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="empty-row">No selected applicants found for this program code.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

