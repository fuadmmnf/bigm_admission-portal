@extends('reports.layouts.report')

@section('title', 'Employer Wise Applicant Report')
@section('report-subtitle', 'Employer Wise Report' . (isset($employerFilter) && $employerFilter ? ' – ' . $employerFilter : ' – All Employers'))
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

        .report-table .col-category {
            width: 80pt !important;
        }

        .report-table .col-organization {
            width: 100pt !important;
        }

        .report-table .col-designation {
            width: 90pt !important;
        }
    </style>
@endsection

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Employer Filter:</span> {{ $employerFilter ?? 'All' }}</span>
{{--    <span><span class="label">Total Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>--}}
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-photo">Photo / App. ID</th>
            <th class="col-name">Applicant Name</th>
            <th class="col-category">Current Job Category</th>
            <th class="col-organization">Current Organization</th>
            <th class="col-designation">Current Designation</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($applications as $index => $application)
            @php
                $jobExp = data_get($application->additional_info, 'job_experience', []);
                $currentCat = data_get($jobExp, 'current.job_category', 'N/A');
                $currentOrg = data_get($jobExp, 'current.organization_name', '—');
                $currentDesig = data_get($jobExp, 'current.designation', '—');
            @endphp
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
                <td class="col-category">{{ $currentCat }}</td>
                <td class="col-organization">{{ $currentOrg }}</td>
                <td class="col-designation">{{ $currentDesig }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row">No paid applicants found for the selected employer category.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
