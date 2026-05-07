@extends('reports.layouts.report')

@section('title', 'Employer Wise Applicant Report')
@section('report-subtitle', 'Employer Wise Report' . (isset($employerFilter) && $employerFilter ? ' – ' . $employerFilter : ' – All Employers'))

@section('content')
<div class="report-meta">
    <span><span class="label">Exam:</span> {{ $exam->name }}</span>
    <span><span class="label">Employer Filter:</span> {{ $employerFilter ?? 'All' }}</span>
    <span><span class="label">Total Applicants:</span><span class="summary-badge">{{ $applications->count() }}</span></span>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-sl">SL</th>
            <th class="col-photo">Photo / App. ID</th>
            <th>Applicant Name</th>
            <th>Current Job Category</th>
            <th>Current Organization</th>
            <th>Current Designation</th>
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
                <td>{{ $application->applicant_name }}</td>
                <td>{{ $currentCat }}</td>
                <td>{{ $currentOrg }}</td>
                <td>{{ $currentDesig }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row">No paid applicants found for the selected employer category.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
